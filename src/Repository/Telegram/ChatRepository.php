<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function incrementMembersCount(Chat $entity): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.membersCount', 'c.membersCount + 1')
            ->where('c.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    public function incrementMessagesCount(Chat $entity): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.messagesCount', 'c.messagesCount + 1')
            ->where('c.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    public function decrementMembersCount(Chat $entity): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.membersCount', 'c.membersCount - 1')
            ->where('c.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    public function decrementMessagesCount(Chat $entity): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.messagesCount', 'c.messagesCount - 1')
            ->where('c.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    public function updateLastMedia(Chat $entity): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set(
                'c.lastMedia', 
                '(' . 
                    $this->createQueryBuilder('cma')
                        ->select('cma.id')
                        ->where('cma.chat = :chat')
                        ->setParameter('chat', $entity)
                        ->orderBy('cma.date', 'DESC')
                        ->setMaxResults(1)
                        ->getDQL()
                . ')'
            )
            ->where('c.id = :id')
            ->setParameter('id', $entity)
            ->getQuery()
            ->execute();
    }

    public function updateLastMessageDate(Chat $entity): void
    {
        $this->createQueryBuilder('c')
            ->update()
            ->set(
                'c.lastMessageDate', 
                '(' .
                    $this->createQueryBuilder('cms')
                        ->select('cms.date')
                        ->where('cms.chat = :chat')
                        ->setParameter('chat', $entity)
                        ->orderBy('cms.date', 'DESC')
                        ->setMaxResults(1)
                        ->getDQL()
                . ')'
            )
            ->where('c.id = :id')
            ->setParameter('id', $entity)
            ->getQuery()
            ->execute();
    }

    // /**
    //  * @return Chat[] Returns an array of Chat objects
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
    public function findOneBySomeField($value): ?Chat
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
