<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\Member;
use App\Entity\Telegram\MemberMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MemberMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberMedia|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberMedia[]    findAll()
 * @method MemberMedia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberMediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberMedia::class);
    }

    // /**
    //  * @return MemberMedia[] Returns an array of MemberMedia objects
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
    public function findOneBySomeField($value): ?MemberMedia
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
