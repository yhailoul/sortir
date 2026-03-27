<?php

namespace App\Security\Voter;

use App\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class EventVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';

//    La méthode supports est une vérification seulement et si les vérifs return true ça appelle la méthode
//     voteOnAttribute pour la logique
    protected function supports(string $attribute, mixed $subject): bool
    {
        //Vérifie si c'est l'action est bien EVENT_EDIT d'un objet Event

        return $attribute === 'EVENT_EDIT' && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }


        return in_array('ROLE_ADMIN', $user->getRoles()) || $subject->getOrganizer() === $user;

    }
}
