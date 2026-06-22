<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function notify(User $user, string $title, string $message, bool $sendEmail = true): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        if ($sendEmail && null !== $user->getEmail()) {
            $this->sendEmail($user, $title, $message);
        }

        return $notification;
    }

    private function sendEmail(User $user, string $title, string $message): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@excusatron.test', 'Excusatron 3000'))
            ->to($user->getEmail())
            ->subject($title)
            ->htmlTemplate('email/notification.html.twig')
            ->context([
                'title' => $title,
                'message' => $message,
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }
}
