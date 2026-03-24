<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher){}

        public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $user= new User();
            $user
                ->setEmail($faker->email)
                ->setPassword($this->userPasswordHasher->hashPassword($user, '123456'))
                ->setRoles(['ROLE_USER'])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setActive($faker->boolean())
                ->setAdmin(false);

        }
        $manager->flush();
    }
}
