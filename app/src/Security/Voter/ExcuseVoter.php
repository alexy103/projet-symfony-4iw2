<?php

namespace App\Security\Voter;

use App\Entity\Excuse;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExcuseVoter extends Voter
{
    public const EXCUSE_VIEW = 'EXCUSE_VIEW';
    public const EXCUSE_EDIT = 'EXCUSE_EDIT';
    public const EXCUSE_DELETE = 'EXCUSE_DELETE';
    public const EXCUSE_VALIDATE = 'EXCUSE_VALIDATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Excuse) {
            return false;
        }

        return in_array($attribute, [
            self::EXCUSE_VIEW,
            self::EXCUSE_EDIT,
            self::EXCUSE_DELETE,
            self::EXCUSE_VALIDATE,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Excuse $excuse */
        $excuse = $subject;

        if ($this->hasRole($user, 'ROLE_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::EXCUSE_VIEW => $this->canView($excuse, $user),
            self::EXCUSE_EDIT => $this->canEdit($excuse, $user),
            self::EXCUSE_DELETE => $this->canDelete($excuse, $user),
            self::EXCUSE_VALIDATE => $this->canValidate($excuse, $user),
            default => false,
        };
    }

    private function canView(Excuse $excuse, User $user): bool
    {
        if ($this->hasRole($user, 'ROLE_VALIDATOR')) {
            return true;
        }

        return $this->isAuthor($excuse, $user);
    }

    private function canEdit(Excuse $excuse, User $user): bool
    {
        if (!$this->isAuthor($excuse, $user)) {
            return false;
        }

        return in_array($excuse->getStatus(), ['draft', 'rejected'], true);
    }

    private function canDelete(Excuse $excuse, User $user): bool
    {
        if (!$this->isAuthor($excuse, $user)) {
            return false;
        }

        return $excuse->getStatus() !== 'validated';
    }

    private function canValidate(Excuse $excuse, User $user): bool
    {
        if (!$this->hasRole($user, 'ROLE_VALIDATOR')) {
            return false;
        }

        return $excuse->getStatus() === 'pending';
    }

    private function isAuthor(Excuse $excuse, User $user): bool
    {
        return $excuse->getAuthor()?->getId() === $user->getId();
    }

    private function hasRole(User $user, string $role): bool
    {
        return in_array($role, $user->getRoles(), true);
    }
}


