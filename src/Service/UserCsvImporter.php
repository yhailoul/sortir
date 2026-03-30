<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserCsvImporter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ){}

    public function import(string $filePath): array{
        $errors = [];
        $created = 0;
        $handle = fopen($filePath, 'r');

        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            [$email, $roles, $password, $firstName, $lastName, $phone, $active, $photo, $username, $campus_id, ] = $row;

            $user = new User();
            $user->setEmail(trim($email));
            $user->setUsername(trim($username));
            $user->setRoles(trim($roles) ?: 'ROLE_USER');
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setFirstName(trim($firstName));
            $user->setLastName(trim($lastName));
            $user->setPhone(trim($phone));
            $user->setActive(trim($active));
            $user->setPhoto(trim($photo));
            $user->setUsername(trim($username));
            $user->setCampus(trim($campus_id));
        }

        $violations = $this->validator->validate($user);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
                continue;
            }
        }

        $this->entityManager->persist($user);
        $created++;

        fclose($handle);

        $this->entityManager->flush();

        return ['created' => $created, 'errors' => $errors];
    }
}
