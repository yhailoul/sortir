<?php

namespace App\Repository;

use App\Entity\Campus;
use App\Form\Model\FilterSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Campus>
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    public function SearchByName(FilterSearch $filter)
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $filter->getSearchTerm() . '%')
            ->getQuery()
            ->getResult();


    }
}
