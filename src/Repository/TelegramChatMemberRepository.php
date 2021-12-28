<?php

namespace App\Repository;

use App\Entity\TelegramChatMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TelegramChatMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramChatMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramChatMember[]    findAll()
 * @method TelegramChatMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramChatMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramChatMember::class);
    }

    // /**
    //  * @return TelegramChatMember[] Returns an array of TelegramChatMember objects
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
    public function findOneBySomeField($value): ?TelegramChatMember
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
