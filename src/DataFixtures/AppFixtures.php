<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use phpDocumentor\Reflection\Location;
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
                ->setActive($faker->boolean());
            //mettre les attributs représentants liens entre les tables
            //$manager->persist($user);
            //$users[] = $user;

        }

        $events=[];
        $durations=[
            new \DateInterval('PT30M'),
            new \DateInterval('PT1H'),
            new \DateInterval('PT1H30M'),
            new \DateInterval('PT2H'),
            new \DateInterval('PT2H30M'),
            new \DateInterval('PT3H'),
            new \DateInterval('PT3H30M'),
            new \DateInterval('PT4H'),
            new \DateInterval('P1D'),
            new \DateInterval('P2D'),
            new \DateInterval('P3D'),
        ];
        for ($i = 0; $i < 50; $i++) {
            $event = new Event();
            $event
                ->setName($faker->sentence())
                ->setinfosEvent($faker->paragraph())
                ->setnbInscriptionMax($faker->numberBetween(2,15))
                ->setDuration($faker->randomElement($durations))
                ->setDateStartHour($faker->dateTimeBetween('now', '+30 day'))
                ->setDateEndHour($faker->dateTimeBetween('+30 minutes', '+30 day'))
                ->setDateLimitInscription($faker->dateTimeBetween('+2 day','+30 day'));
            //mettre les attributs représentants liens entre les tables
            //$manager->persist($event);
        }
        $campusList =[];
        $campusEni = [
            'Rennes',
            'Quimper',
            'Niort',
            'Nantes',
            'En-ligne'
        ];
        foreach ($campusEni as $campusName) {
            $campus = new Campus();
            $campusName
                ->setName($campusName);
            //ajouter la relation avec USER
            //$manager->persist($campus);
            $campusList[] = $campus;
        }
        $statusList =[];
        $statuses=[
            'En création',
            'Ouverte',
            'Clôturée',
            'En cours',
            'Terminée',
            'Annulée',
            'Historisée'
        ];
        foreach ($statuses as $statusName) {
            $status = new Status;
            $status->setName($statusName);
            $statusList[] = $status;
            //ajouter la relation avec la table EVENT
            //$manager->persist($status);
        }

        $categoryNames = [
            'Gastronomie et Détente ',
            'Sport et Aventure ',
            'Culture et découverte',
            'Jeux et divertissement',
            'Gaming',
            'Bien-être et relaxation',
            'Nature et Plein Air',
            'Créatif et Atéliers',
            'Nocturnes et Festivités',
            'Insolite et Original'
        ];

        $categories = []; // Pour stocker les objets Category créés

        foreach ($categoryNames as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
            $categories[] = $category;

            //ajouter la relation avec la table EVENT
            //$manager->persist($category);
        }

        for ($i = 0; $i < 50; $i++) {
            $location = new Location();
            $location
                ->setName($faker->sentence())
                ->setStreet($faker->streetAddress())
                ->setLatitude($faker->latitude())
                ->setLongitude($faker->longitude());
            //mettre les attributs représentants liens entre les tables
            //$manager->persist($location);
        }


        for ($i = 0; $i < 50; $i++) {
            $city = new City();
            $city
                ->setName($faker->city())
                ->setCodePostal($faker->postcode());
            //mettre les attributs représentants liens entre les tables
            //$manager->persist($location);
        }






        //$manager->flush();
    }
}
