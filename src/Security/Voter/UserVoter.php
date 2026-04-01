<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    public const CREATE = 'USER_CREATE';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';
    public const DISABLE = 'USER_DISABLE';
    public const ACTIVATE = 'USER_ACTIVATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::CREATE, self::EDIT, self::DELETE, self::DISABLE, self::ACTIVATE])
            && ($subject instanceof User || null === $subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::CREATE:
                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    return true;
                }
                break;

            case self::EDIT:
                if ($user === $subject) {
                    return true;
                }
                break;

            case self::DELETE:
            case self::DISABLE:
            case self::ACTIVATE:
                if ($user !== $subject && in_array('ROLE_ADMIN', $user->getRoles())) {
                    return true;
                }
                break;

        }

        return false;
    }
}
