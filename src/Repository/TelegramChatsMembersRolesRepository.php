<?php

namespace App\Repository;

use App\Entity\TelegramChatsMembersRoles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TelegramChatsMembersRoles|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramChatsMembersRoles|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramChatsMembersRoles[]    findAll()
 * @method TelegramChatsMembersRoles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramChatsMembersRolesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramChatsMembersRoles::class);
    }

    // /**
    //  * @return TelegramChatsMembersRoles[] Returns an array of TelegramChatsMembersRoles objects
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
    public function findOneBySomeField($value): ?TelegramChatsMembersRoles
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
