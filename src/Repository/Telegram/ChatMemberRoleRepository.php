<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\ChatMemberRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMemberRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMemberRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMemberRole[]    findAll()
 * @method ChatMemberRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMemberRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMemberRole::class);
    }

    // /**
    //  * @return ChatMemberRole[] Returns an array of ChatMemberRole objects
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
    public function findOneBySomeField($value): ?ChatMemberRole
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
