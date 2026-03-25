<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
        public function findByOrganizer(string $user): array
        {
            return $this->createQueryBuilder('e')
                ->leftJoin('e.organizer', 'o')
                ->addSelect('o')
                ->where('e.organizer = :c_organizer')
                ->setParameter('c_organizer', $user)
                ->orderBy('e.dateStartHour', 'ASC')
                ->getQuery()
                ->getResult();
        }

    public function findBySignedUp(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.participantList', 'p')
            ->addSelect('p')
            ->where('p.id = :userId')
            ->setParameter('c_organizer', $user->getId())
            ->orderBy('e.dateStartHour', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByNotSignedUp(User $user) {


    }

}
