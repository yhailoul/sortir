<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\User;
use App\Form\CampusFilterType;
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

    public function filterBySelection(User $user, FilterSearch $filter, ?Campus $campus){

        $qb = $this->createQueryBuilder('e');

        dump('SQL REçU: '.$qb->getDQL());
        dump('paramètres : '.$qb->getParameters());

        if($campus !== null){
            if($campus->getId()){
                $qb->andWhere('e.campus = :campus')
                    ->setParameter('campus', $campus);
            }
        }
        if($filter->getSearchTerm()){
            $qb->andWhere('e.name LIKE :searchTerm')
                ->setParameter('searchTerm', '%'.$filter->getSearchTerm().'%');
        }
        if($filter->getStartDate()){
            $start = clone $filter->getStartDate();
            $start->setTime(0, 0, 0); // Début de journée
            $end = clone $filter->getStartDate();
            $end->setTime(23, 59, 59); // Fin de journée

            $qb->andWhere('e.dateStartHour BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $start)
                ->setParameter('endDate', $end);
        }
        if($filter->getEndDate()){
            $end = clone $filter->getEndDate();
            $end->setTime(23, 59, 59); // Fin de journée : 23:59:59

            $qb->andWhere('e.dateEndHour <= :endDate')
                ->setParameter('endDate', $end);
        }
        $orConditions =[];

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
        if(!empty($orConditions)){
            $qb->andWhere($qb->expr()->orX()->addMultiple($orConditions));
        }

        $qb->orderBy('e.dateStartHour', 'ASC');
        $query = $qb->getQuery();
        (dump($query->getSQL()));
        dump($query->getParameters());
        return $qb->getQuery()->getResult();



    }

//recherche des événements par campus uniquement (sans prendre en considération si l'utilisateur fais parti de l'event ou pas
//    public function findByCampus(Campus $campus){
//
//        return $this->createQueryBuilder('e')
//            ->where('e.campus = :campus')
//            ->setParameter('campus', $campus)
//            ->orderBy('e.dateStartHour', 'ASC')
//            ->getQuery()
//            ->getResult();
//
//        }

































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
