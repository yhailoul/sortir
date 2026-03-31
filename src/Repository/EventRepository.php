<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\Status;
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

    public function filterBySelection(User $user, FilterSearch $filter, ?Campus $campus)
    {

        $qb = $this->createQueryBuilder('e');

        dump('SQL REçU: ' . $qb->getDQL());
        dump('paramètres : ' . $qb->getParameters());

        if ($campus !== null) {
            if ($campus->getId()) {
                $qb->andWhere('e.campus = :campus')
                    ->setParameter('campus', $campus);
            }
        }
        if ($filter->getSearchTerm()) {
            $qb->andWhere('e.name LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $filter->getSearchTerm() . '%');
        }
        if ($filter->getStartDate()) {
            $start = clone $filter->getStartDate();
            $start->setTime(0, 0, 0); // Début de journée
            $end = clone $filter->getStartDate();
            $end->setTime(23, 59, 59); // Fin de journée

            $qb->andWhere('e.dateStartHour BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $start)
                ->setParameter('endDate', $end);
        }
        if ($filter->getEndDate()) {
            $end = clone $filter->getEndDate();
            $end->setTime(23, 59, 59); // Fin de journée : 23:59:59

            $qb->andWhere('e.dateEndHour <= :endDate')
                ->setParameter('endDate', $end);
        }

        if ($filter->getOrganized()) {
            $qb->andWhere('e.organizer = :organizer')
                ->setParameter('organizer', $user);

        }
        if ($filter->getSignedUp()) {
            $qb->andWhere(':user MEMBER OF e.participantList')
                ->setParameter('user', $user);

        }
        if ($filter->getPassed()) {
            $qb->andWhere('e.dateEndHour < :now')
                ->setParameter('now', new DateTime('now'));

        }

        $qb->orderBy('e.dateStartHour', 'ASC');
        $query = $qb->getQuery();
        (dump($query->getSQL()));
        dump($query->getParameters());
        return $qb->getQuery()->getResult();

   }

    public function AllEvents()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.eventStatus', 's')
            ->leftJoin('e.participantList', 'p')
            ->leftJoin('e.campus', 'c')
            ->addSelect('o', 's', 'p', 'c')  // Seulement les vraies relations !
            ->orderBy('e.dateStartHour', 'ASC')
            ->getQuery()
            ->getResult();


    }

    public function findEventsToUpdate()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.eventStatus', 'eStatus')
            ->andWhere('eStatus.label != :status')
            ->setParameter('status', Status::HISTORIZED)
            ->getQuery()
            ->getResult();


    }

}
