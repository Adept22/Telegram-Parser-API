<?php

namespace App\Repository;

use App\Entity\TelegramChatMemberRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TelegramChatMemberRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method TelegramChatMemberRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method TelegramChatMemberRole[]    findAll()
 * @method TelegramChatMemberRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TelegramChatMemberRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramChatMemberRole::class);
    }

    // /**
    //  * @return TelegramChatMemberRole[] Returns an array of TelegramChatMemberRole objects
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
    public function findOneBySomeField($value): ?TelegramChatMemberRole
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
