<?php

namespace App\Tests\Security\Voter;

use App\Entity\ClassicExcuse;
use App\Entity\User;
use App\Security\Voter\ExcuseVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ExcuseVoterTest extends TestCase
{
    public function testAuthorCanEditRejectedExcuse(): void
    {
        $author = $this->createUser(1, ['ROLE_USER']);
        $excuse = (new ClassicExcuse())->setStatus('rejected')->setAuthor($author);

        $token = new UsernamePasswordToken($author, 'main', $author->getRoles());
        $voter = new ExcuseVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $excuse, [ExcuseVoter::EXCUSE_EDIT])
        );
    }

    public function testAuthorCannotEditPendingExcuse(): void
    {
        $author = $this->createUser(1, ['ROLE_USER']);
        $excuse = (new ClassicExcuse())->setStatus('pending')->setAuthor($author);

        $token = new UsernamePasswordToken($author, 'main', $author->getRoles());
        $voter = new ExcuseVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $excuse, [ExcuseVoter::EXCUSE_EDIT])
        );
    }

    public function testValidatorCanValidatePendingExcuse(): void
    {
        $author = $this->createUser(1, ['ROLE_USER']);
        $validator = $this->createUser(2, ['ROLE_VALIDATOR']);
        $excuse = (new ClassicExcuse())->setStatus('pending')->setAuthor($author);

        $token = new UsernamePasswordToken($validator, 'main', $validator->getRoles());
        $voter = new ExcuseVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $excuse, [ExcuseVoter::EXCUSE_VALIDATE])
        );
    }

    public function testValidatorCannotValidateWhenNotPending(): void
    {
        $author = $this->createUser(1, ['ROLE_USER']);
        $validator = $this->createUser(2, ['ROLE_VALIDATOR']);
        $excuse = (new ClassicExcuse())->setStatus('validated')->setAuthor($author);

        $token = new UsernamePasswordToken($validator, 'main', $validator->getRoles());
        $voter = new ExcuseVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $excuse, [ExcuseVoter::EXCUSE_VALIDATE])
        );
    }

    public function testAdminCanAlwaysDelete(): void
    {
        $author = $this->createUser(1, ['ROLE_USER']);
        $admin = $this->createUser(3, ['ROLE_ADMIN']);
        $excuse = (new ClassicExcuse())->setStatus('validated')->setAuthor($author);

        $token = new UsernamePasswordToken($admin, 'main', $admin->getRoles());
        $voter = new ExcuseVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $excuse, [ExcuseVoter::EXCUSE_DELETE])
        );
    }

    public function testNonAuthorCannotDelete(): void
    {
        $author = $this->createUser(1, ['ROLE_USER']);
        $otherUser = $this->createUser(2, ['ROLE_USER']);
        $excuse = (new ClassicExcuse())->setStatus('rejected')->setAuthor($author);

        $token = new UsernamePasswordToken($otherUser, 'main', $otherUser->getRoles());
        $voter = new ExcuseVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $excuse, [ExcuseVoter::EXCUSE_DELETE])
        );
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(int $id, array $roles): User
    {
        $user = (new User())
            ->setEmail(sprintf('user%d@test.local', $id))
            ->setPasswordHash('hash')
            ->setRoles($roles);

        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($user, $id);

        return $user;
    }
}

