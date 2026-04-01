<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{

    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private FileUploader                $fileUploader,
        private AvatarService               $defaultAvatar,
        private Security                    $security

    )
    {
    }

    public function createUser(User $user, string $plainPassword, ?UploadedFile $photo): void
    {
        $user->setRoles(['ROLE_USER']);
        $user->setActive(true);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
        if ($photo) {
            $user->setPhoto($this->fileUploader->upload($photo));
        }
        $this->defaultAvatar->correctionPhotoProfile($user);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

    }

    public function updateUser(User $user, ?string $checkPassword, ?UploadedFile $photo): void
    {

        if (!$user->getPhoto()) {
            $this->defaultAvatar->correctionPhotoProfile($user);
        }

        if ($photo) {
            $user->setPhoto($this->fileUploader->upload($photo));
        }

        if (!empty($checkPassword)) {
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $checkPassword);
            $user->setPassword($hashedPassword);
            $this->security->login($user);
        }

        $this->entityManager->flush();

    }
}
