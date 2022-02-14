<?php

namespace App\Repository;

use App\Entity\Export;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Export|null find($id, $lockMode = null, $lockVersion = null)
 * @method Export|null findOneBy(array $criteria, array $orderBy = null)
 * @method Export[]    findAll()
 * @method Export[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Export::class);
    }

    // /**
    //  * @return Export[] Returns an array of Export objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Export
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
