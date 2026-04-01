<?php

namespace App\DataFixtures;

use App\Entity\Campus;
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
    )
    {
        $this->faker = Factory::create('fr_FR');


    }

    public function load(ObjectManager $manager): void
    {
        // On récupère directement les objets créés par les méthodes et renvoyer en return au lieu de passer par le repo
        $campusList = $this->campus($manager);
        $statusList = $this->status($manager);
        $cityList = $this->city($manager);

        $locationList = $this->location($manager, $cityList);

        $users = $this->user($manager, $campusList);

        $this->event($manager, $users, $statusList, $locationList, $campusList );

        $manager->flush();
    }

    public function campus(ObjectManager $manager): array
    {
        //$faker = Factory::create('fr_FR');
        $campusList = [];
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

        return $campusList;
    }

    public function status(ObjectManager $manager): array
    {
        //$faker = Factory::create('fr_FR');
        $statusList = [];
        $statuses = [
            'In creation',
            'Open',
            'Closed',
            'In progress',
            'Ended',
            'Canceled',
            'Historized'
        ];
        foreach ($statuses as $statusName) {
            $status = new Status();
            $status->setLabel($statusName);
            $statusList[] = $status;
            $manager->persist($status);
        }

        return $statusList;
    }

    public function location(ObjectManager $manager, array $cityList): array
    {
        //fixtures pour Location
        $faker = Factory::create('fr_FR');
        $locations = [];
        for ($i = 0; $i < 50; $i++) {
            $location = new Location();
            //->addEvent($randomEvent)
            $location->setCity($this->faker->randomElement($cityList));
            $location->setName($faker->sentence());
            $location->setStreet($faker->streetAddress());
            $location->setLatitude($faker->latitude());
            $location->setLongitude($faker->longitude());
            $locations[] = $location;
            $manager->persist($location);
        }

        return $locations;
    }

    public function city(ObjectManager $manager): array
    {
        //fixtures pour City

        $cities = [];
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 50; $i++) {
            $city = new City();
            $city
                ->setName($faker->city())
                ->setZipCode($faker->postcode());
            $cities[] = $city;

            $manager->persist($city);
        }

        return $cities;
    }

    public function user(ObjectManager $manager, array $campusList): array
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
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
            $manager->persist($user);
        }
        return $users;
    }

    public function event(ObjectManager $manager, array $users, array $statusList, array $locationList, array $campusList): array
    {
        $faker = Factory::create('fr_FR');
        $events = [];
        $durations = [
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
            $organizer = $faker->randomElement($users);
            $randomLocation = $faker->randomElement($locationList);
            $randomCampus = $faker->randomElement($campusList);
            $event
                ->setName($faker->sentence())
                ->setinfosEvent($faker->paragraph())
                ->setnbMaxRegistrations($faker->numberBetween(2, 15))
                ->setDuration($faker->randomElement($durations))
                ->setDateStartHour($faker->dateTimeBetween('now', '+30 day'))
                ->setDateEndHour($faker->dateTimeBetween('now', '+30 day'))
                ->setregistrationDeadline($faker->dateTimeBetween('now', $event->getDateStartHour()))
                ->setOrganizer($organizer)
                ->setEventStatus($faker->randomElement($statusList))
                ->setEventLocation($randomLocation)
                ->setCampus($randomCampus);
            $maxParticipants = min($event->getnbMaxRegistrations(), count($users) - 1);
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
        return $events;
    }

}
