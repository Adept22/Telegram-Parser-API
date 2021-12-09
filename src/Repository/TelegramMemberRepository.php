<?php

namespace App\Repository;

use App\Entity\TelegramMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TelegramMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramMember[]    findAll()
 * @method TelegramMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramMember::class);
    }

    // /**
    //  * @return TelegramMember[] Returns an array of TelegramMember objects
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
    public function findOneBySomeField($value): ?TelegramMember
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
