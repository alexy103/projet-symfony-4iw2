<?php

namespace App\Tests\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;

final class NotificationServiceTest extends TestCase
{
    public function testNotifyPersistsAndSendsEmailByDefault(): void
    {
        $persistedNotification = null;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(static function (object $notification) use (&$persistedNotification): bool {
                self::assertInstanceOf(Notification::class, $notification);
                $persistedNotification = $notification;

                return true;
            }));
        $entityManager->expects(self::once())->method('flush');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $service = new NotificationService($entityManager, $mailer, 'noreply@excusatron.test');
        $user = (new User())->setEmail('user@test.local')->setPasswordHash('hash')->setRoles(['ROLE_USER']);

        $notification = $service->notify($user, 'Titre test', 'Message test');

        self::assertInstanceOf(Notification::class, $notification);
        self::assertSame($user, $notification->getUser());
        self::assertSame('Titre test', $notification->getTitle());
        self::assertSame('Message test', $notification->getMessage());
        self::assertFalse((bool) $notification->isRead());
        self::assertInstanceOf(\DateTimeImmutable::class, $notification->getCreatedAt());
        self::assertSame($persistedNotification, $notification);
    }

    public function testNotifyCanSkipEmailSending(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $service = new NotificationService($entityManager, $mailer, 'noreply@excusatron.test');
        $user = (new User())->setEmail('user@test.local')->setPasswordHash('hash')->setRoles(['ROLE_USER']);

        $notification = $service->notify($user, 'Sans email', 'Notification locale', false);

        self::assertSame('Sans email', $notification->getTitle());
        self::assertSame('Notification locale', $notification->getMessage());
    }

    public function testNotifyDoesNotSendEmailWhenUserHasNoEmail(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $service = new NotificationService($entityManager, $mailer, 'noreply@excusatron.test');
        $user = (new User())->setPasswordHash('hash')->setRoles(['ROLE_USER']);

        $notification = $service->notify($user, 'Sans adresse', 'Pas d\'envoi possible');

        self::assertSame($user, $notification->getUser());
        self::assertSame('Sans adresse', $notification->getTitle());
    }
}

