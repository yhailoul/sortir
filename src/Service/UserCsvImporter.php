<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CampusRepository;
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
        private CampusRepository $campusRepository,
        private UserRepository $userRepository,
    ){}

    public function import(string $filePath): array{
        $errors = [];
        $created = 0;
        $seenEmails = [];
        $handle = fopen($filePath, 'r');

        fgetcsv($handle);

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            [$email, $roles, $password, $firstName, $lastName, $phone, $active, $photo, $username, $campus_id, ] = $row;

            // Test doublons de mail dans le CSV
            $email = trim($email);
            if (in_array($email, $seenEmails)) {
                $errors[] = "$email - Duplicates emails in CSV file";
                continue;
            }

            // Test d'un mail déjà existant dans la BD
            $seenEmails[] = $email;
            if ($this->userRepository->findOneBy(['email' => $email])){
                $errors[] = "$email - Email already exists in database.";
                continue;
            }

            // Test de validité du campus
            $campus = $this->campusRepository->find((int) $campus_id);
            if (!$campus) {
                $errors[] = "$email - campus_id $campus_id not found";
                continue;
            }

            $user = new User();
            $user->setEmail(trim($email));
            $user->setRoles([trim($roles) ?: 'ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, trim($password)));
            $user->setFirstName(trim($firstName));
            $user->setLastName(trim($lastName));
            $user->setPhone(trim($phone));
            $user->setActive((bool)trim($active));
            $user->setPhoto(trim($photo));
            $user->setUsername(trim($username));
            $user->setCampus($campus);

            $violations = $this->validator->validate($user);
            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors[] = "$email — {$violation->getPropertyPath()} : {$violation->getMessage()}";
                }
                continue;
            }
            $this->entityManager->persist($user);
            $created++;
        }
        fclose($handle);

        $this->entityManager->flush();

        return ['created' => $created, 'errors' => $errors];
    }
}
