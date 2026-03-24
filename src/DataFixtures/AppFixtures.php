<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher){}

        public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        //Déclaration des variables nécessaires
        $users = [];
        $events=[];
        $campusList =[];
        $statusList =[];
        $categories = [];
        $cities =[];
        $locations=[];
        $organizersEvents=[];



        /////////////Création des données qui sont référencées par les autres tables//////////////

        //création de fixtures pour Campus


        $campusEni = [
            'Rennes',
            'Quimper',
            'Niort',
            'Nantes',
            'En-ligne'
        ];
        foreach ($campusEni as $campusName) {
            $campus = new Campus();
            $campus
                ->setName($campusName);
            $manager->persist($campus);
            $campusList[] = $campus;
        }


        //creation de fixtures pour Status


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
            $status = new Status();
            $status->setLabel($statusName);
            $statusList[] = $status;
            $manager->persist($status);
        }
        //creation de fixtures pour Category
        $categoryNames = [
            'Gastronomy',
            'Sport & Adventure ',
            'Culture & Discoveries',
            'Games & Entertainment',
            'Gaming',
            'Well-being & relaxation',
            'Nature & Outdoors',
            'Creativity et Workshops',
            'Nightlife & Festivities',
            'Unusual & Original'
        ];


        foreach ($categoryNames as $categoryName) {
            $category = new Category();
            $category->setLabel($categoryName);
            $manager->persist($category);
            $categories[] = $category;

            $manager->persist($category);
        }


        //fixtures pour Location

        $randomCity=($faker->randomElement($cities));
        $randomEvent=($faker->randomElement($events));

        for ($i = 0; $i < 50; $i++) {
            $location=new Location();
            $location
                ->addEvent($randomEvent)
                ->addCity($randomCity)
                ->setName($faker->sentence())
                ->setStreet($faker->streetAddress())
                ->setLatitude($faker->latitude())
                ->setLongitude($faker->longitude());
            $locations[]=$location;
            $manager->persist($location);
        }

        //fixtures pour City
        $randomLocation=($faker->randomElement($locations));
        for ($i = 0; $i < 50; $i++) {
            $city=new City();
            $city
                ->setLocationsList($randomLocation)
                ->setName($faker->city())
                ->setZipCode($faker->postcode());
            $cities[]=$city;

            $manager->persist($city);
        }


        /////////////////Création des données dépendantes des autres tables/////////////////


        //fixtures pour User
        $randomCampus = $faker->randomElement($campusList);
        $randomEvent=($faker->randomElement($events));



        for ($i = 0; $i < 50; $i++) {
            $user=new User();
            $user
                ->setEmail($faker->unique()->email())
                ->addEventInscription($randomEvent)
                ->addOrganizerEvent($randomEvent)
                ->setPassword($this->userPasswordHasher->hashPassword($user, '123456'))
                ->setRoles(['ROLE_USER'])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setActive(true)
                ->setCampus($randomCampus);

            $users[] = $user;

            $manager->persist($user);


        }

        //fixtures pour Event

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



        //éléments représentant les relation entre tables Event et : User, Status, Category, Location
        $randomOrganizer=($faker->randomElement($users));
        $randomStatus=($faker->randomElement($statuses));
        $randomLocation=($faker->randomElement($locations));
        $randomCategory=($faker->randomElement($categories));





        //éléments permettant de représenter la relation ManyToMany Event - User
        for ($i = 0; $i < 50; $i++) {
            $event=new Event();

            //ajout de participants aléatoires à l'événement

            //séléction d'un nombre random de participants à l'événement
            $maxParticipants = min($event->getNbInscriptionMax(), count($users));
            $nbParticipants = $faker->numberBetween(0,$maxParticipants);

            //mélange des utilisateurs et prendre les premiers participants numéro défini dans la variable $participants
            //je récupère tous les utilisateurs
            $shuffledParticipants = $users;
            //random mix des utilisateurs pour ne pas prendre forcément les premiers de la liste
            shuffle($shuffledParticipants);
            //séléction des participants à l'événement (à l'éxception de l'organisateur
            $selectedParticipants = array_slice($shuffledParticipants, 0, $nbParticipants);
            foreach ($selectedParticipants as $participant) {
                if($participant !== $randomOrganizer){
                    $event->addParticipantList($participant);
                }else{
                    $organizersEvents[]=$event;
                }
            }
            $event

                ->setOrganizer($randomOrganizer)
                ->setEventStatus($randomStatus)
                ->setEventLocation($randomLocation)
                ->setEventCategory($randomCategory)
                ->setName($faker->sentence())
                ->setinfosEvent($faker->paragraph())
                ->setnbInscriptionMax($faker->numberBetween(2,15))
                ->setDuration($faker->randomElement($durations))
                ->setDateStartHour($faker->dateTimeBetween('now', '+30 day'))
                ->setDateEndHour($faker->dateTimeBetween('now', '+30 day'))
                ->setDateLimiteInscription($faker->dateTimeBetween('now',$event->getDateStartHour()));


            $manager->persist($event);
        }






        //$manager->flush();
    }
}
