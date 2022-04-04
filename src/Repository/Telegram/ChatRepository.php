<?php

namespace App\Repository\Telegram;

use App\Entity\Telegram\Chat;
use App\Entity\Telegram\ChatMedia;
use App\Entity\Telegram\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Chat::class);

        $this->logger = $logger;
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
        $select = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('cm.id')
            ->from(ChatMedia::class, 'cm')
            ->where('cm.chat = :chat_id')
            ->orderBy('cm.date', 'DESC')
            ->getDQL();

        $this->logger->critical($select);

        $this->createQueryBuilder('c')
            ->update()
            ->set('c.lastMedia', "($select limit 1)")
            ->where('c.id = :chat_id')
            ->setParameter('chat_id', $entity->getId())
            ->getQuery()
            ->execute();
    }

    public function updateLastMessageDate(Chat $entity): void
    {
        $select = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('m.date')
            ->from(Message::class, 'm')
            ->where('m.chat = :chat_id')
            ->orderBy('m.date', 'DESC')
            ->getDQL();

        $this->logger->critical($select);

        $this->createQueryBuilder('c')
            ->update()
            ->set('c.lastMessageDate', "($select limit 1)")
            ->where('c.id = :chat_id')
            ->setParameter('chat_id', $entity->getId())
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
