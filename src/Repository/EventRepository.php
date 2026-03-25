<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Form\Model\FilterSearch;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\Clock\now;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function filterBySelection(User $user, FilterSearch $filter){

        $qb = $this->createQueryBuilder('e');

        if($filter->getOrganized()){
            $qb->orWhere('e.organizer = :organizer')
                ->setParameter('organizer', $user);

        }
        if($filter->getSignedUp()){
            $qb->orWhere(':user MEMBER OF e.participantList')
                ->setParameter('user', $user);

        }
        if($filter->getPassed()){
            $qb->orWhere('e.dateEndHour < :now')
                ->setParameter('now', new DateTime('now'));

        }
        $qb->orderBy('e.dateStartHour', 'ASC');
        $query = $qb->getQuery();
        (dump($query->getSQL()));
        dump($query->getParameters());
        return $qb->getQuery()->getResult();



    }































//        public function findByOrganizer(User $user): array
//        {
//        $dql= "SELECT e
//                    FROM App\Entity\Event e
//                    JOIN e.organizer o
//                    WHERE o.id = :organizerId";
//        $query = $this->getEntityManager()->createQuery($dql);
//        $query->setParameter("organizerId", $user->getId());
//        return $query->getResult();
//        }
//
//    public function findBySignedUp(User $user): array
//    {
//        $dql='SELECT e
//                    FROM App\Entity\Event e
//                    JOIN e.participantList p
//                    WHERE p.id = :userId
//                    ORDER BY e.dateStartHour ASC';
//        $query = $this->getEntityManager()->createQuery($dql);
//        $query->setParameter("userId", $user->getId());
//        return $query->getResult();
//    }
//
//    public function findByPassedEvents(string $selected) : array
//    {
//        return $this->createQueryBuilder('e')
//            ->select('e')
//            ->where('e.dateEndHour <:now')
//            ->setParameter('now', new \DateTime('now'))
//            ->getQuery()
//            ->getResult();
//    }

}
