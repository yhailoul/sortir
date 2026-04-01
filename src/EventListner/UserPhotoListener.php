<?php

namespace App\EventListner;

use App\Entity\User;
use App\Service\AvatarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener]
class UserPhotoListener
{
    public function __construct(
        private readonly AvatarService $avatarService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getAuthenticatedToken()->getUser();

        if ($user instanceof User && !$user->getPhoto()) {
            $this->avatarService->correctionPhotoProfile($user);
            $this->entityManager->flush();
        }
    }
}
