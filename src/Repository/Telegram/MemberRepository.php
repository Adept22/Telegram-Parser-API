<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\Member;
use App\Entity\Telegram\MemberMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function updateLastMedia(Member $entity): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.lastMedia', "FIRST(" . 
                $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('mm.id')
                    ->from(MemberMedia::class, 'mm')
                    ->where('mm.member = :member_id')
                    ->orderBy('mm.date', 'DESC')
                    ->getDQL()
            . ")")
            ->where('m.id = :member_id')
            ->setParameter('member_id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    // /**
    //  * @return Member[] Returns an array of Member objects
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
    public function findOneBySomeField($value): ?Member
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
