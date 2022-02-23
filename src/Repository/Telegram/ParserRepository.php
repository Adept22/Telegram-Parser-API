<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\Parser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parser|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parser|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parser[]    findAll()
 * @method Parser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parser::class);
    }

    public function findAllOrderByChatsCount($orderByDirection = "ASC")
    {
        return $this->createQueryBuilder("p")
            ->select("p, COUNT(c.id) as HIDDEN chats_count")
            ->leftJoin("p.chats", "c")
            ->orderBy("chats_count", $orderByDirection)
            ->groupBy("p.id")
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderByPhonesCount($orderByDirection = "ASC")
    {
        return $this->createQueryBuilder("p")
            ->select("p, COUNT(ph.id) as HIDDEN phones_count")
            ->leftJoin("p.phones", "ph")
            ->orderBy("phones_count", $orderByDirection)
            ->groupBy("p.id")
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Parser[] Returns an array of Parser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Parser
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
