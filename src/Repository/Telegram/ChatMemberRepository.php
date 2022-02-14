<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\ChatMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMember[]    findAll()
 * @method ChatMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMember::class);
    }

    // /**
    //  * @return ChatMember[] Returns an array of ChatMember objects
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
    public function findOneBySomeField($value): ?ChatMember
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
