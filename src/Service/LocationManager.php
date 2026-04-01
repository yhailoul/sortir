<?php

namespace App\Service;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class LocationManager
{
    public function __construct(
        private LocationRepository     $locationRepository,
        private EntityManagerInterface $em
    )
    {
    }

    public function handleLocation(Location $location): bool
    {

        if ($this->locationRepository->findOneBy(['name' => $location->getName()])) {
            return false;
        }

        $location->setLatitude(null);
        $location->setLongitude(null);
        $this->em->persist($location);
        $this->em->flush();

        return true;

    }

}
