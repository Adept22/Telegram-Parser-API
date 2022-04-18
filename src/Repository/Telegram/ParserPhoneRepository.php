<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\ParserPhone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParserPhone|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParserPhone|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParserPhone[]    findAll()
 * @method ParserPhone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParserPhoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParserPhone::class);
    }

    // /**
    //  * @return ParserPhone[] Returns an array of ParserPhone objects
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
    public function findOneBySomeField($value): ?ParserPhone
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
