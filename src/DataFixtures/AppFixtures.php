<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Entity\Location;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private CampusRepository $campusRepository,
        private CityRepository $cityRepository
    ){
        $this->faker = Factory::create('fr_FR');


    }

    public function load(ObjectManager $manager): void
    {

        $this->campus($manager);
        $this->status($manager);
        $this->category($manager);
        $this->city($manager);
        $this->location($manager);
        // Récupérer les objets créés pour les réutiliser
        $campusList = $manager->getRepository(Campus::class)->findAll();
        $statusList = $manager->getRepository(Status::class)->findAll();
        $categoryList = $manager->getRepository(Category::class)->findAll();
        $cityList = $manager->getRepository(City::class)->findAll();
        $locationList = $manager->getRepository(Location::class)->findAll();


        // Passer ces listes aux méthodes qui en ont besoin
        $this->user($manager, $campusList);
        $users = $manager->getRepository(User::class)->findAll();
        $this->event($manager, $users, $statusList, $locationList, $categoryList);

    }
    public function campus(ObjectManager $manager){
        //$faker = Factory::create('fr_FR');
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
            $campus
                ->setName($campusName);
            $manager->persist($campus);
            $campusList[] = $campus;
        }
        $manager->flush();
    }

    public function status(ObjectManager $manager)
    {
        //$faker = Factory::create('fr_FR');
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
            $status = new Status();
            $status->setLabel($statusName);
            $statusList[] = $status;
            $manager->persist($status);
        }
        $manager->flush();
    }
    public function category(ObjectManager $manager){
        //$faker = Factory::create('fr_FR');
        $categories = [];
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

            // $manager->persist($category);
        }
        $manager->flush();
    }

    public function location(ObjectManager $manager){
        //fixtures pour Location
        $faker = Factory::create('fr_FR');
        $cities = $this->cityRepository->findAll();
        $locations=[];
        for ($i = 0; $i < 50; $i++) {
            $location=new Location();
            $location
                //->addEvent($randomEvent)
                ->addCity($this->faker->randomElement($cities))
                ->setName($faker->sentence())
                ->setStreet($faker->streetAddress())
                ->setLatitude($faker->latitude())
                ->setLongitude($faker->longitude());
            $locations[]=$location;
            $manager->persist($location);
        }
        $manager->flush();
    }

    public function city(ObjectManager $manager){
        //fixtures pour City

        $cities =[];
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 50; $i++) {
            $city=new City();
            $city
                ->setName($faker->city())
                ->setZipCode($faker->postcode());
            $cities[]=$city;

            $manager->persist($city);
        }
        $manager->flush();
    }

    public function user(ObjectManager $manager, array $campusList){
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $user=new User();
            $user
                ->setEmail($faker->unique()->email())
                ->setPassword($this->userPasswordHasher->hashPassword($user, '123456'))
                ->setRoles(['ROLE_USER'])
                ->setUsername($faker->userName())
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setActive(true)
                ->setCampus($faker->randomElement($campusList));

            $users[] = $user;

//

            $manager->persist($user);
        }
        $manager->flush();
    }

    public function event(ObjectManager $manager, array $users, array $statusList, array $locationList, array $categoryList){
        $faker = Factory::create('fr_FR');
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

            $event=new Event();
            $organizer = $faker->randomElement($users);
            $randomLocation = $faker->randomElement($locationList);
            $event
                ->setName($faker->sentence())
                ->setinfosEvent($faker->paragraph())
                ->setnbInscriptionMax($faker->numberBetween(2,15))
                ->setDuration($faker->randomElement($durations))
                ->setDateStartHour($faker->dateTimeBetween('now', '+30 day'))
                ->setDateEndHour($faker->dateTimeBetween('now', '+30 day'))
                ->setDateLimiteInscription($faker->dateTimeBetween('now',$event->getDateStartHour()))
                ->setOrganizer($organizer)
                ->setEventStatus($faker->randomElement($statusList))
                ->setEventLocation($randomLocation)
                ->setEventCategory($faker->randomElement($categoryList));
            $maxParticipants = min($event->getNbInscriptionMax(), count($users) - 1);
            $nbParticipants = $faker->numberBetween(0, $maxParticipants);
            $shuffledUsers = $users;
            shuffle($shuffledUsers);

            for ($j = 0; $j < $nbParticipants; $j++) {
                if ($shuffledUsers[$j] !== $organizer) {
                    $event->addParticipantList($shuffledUsers[$j]);
                }
            }
            $manager->persist($event);
        }
        $manager->flush();
    }


}
