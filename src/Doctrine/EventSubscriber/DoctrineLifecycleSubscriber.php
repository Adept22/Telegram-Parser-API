<?php

namespace App\Doctrine\EventSubscriber;

use App\Entity\Telegram;
use App\Entity\Telegram\ChatPhone;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DoctrineLifecycleSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(ContainerInterface $container, ValidatorInterface $validator)
    {
        $this->container = $container;
        $this->validator = $validator;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postRemove
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $om = $args->getObjectManager();
        $entity = $args->getObject();

        if ($entity instanceof Telegram\Phone) {
            if ($entity->getParser() == null) {
                /** @var \App\Repository\Telegram\ParserRepository */
                $parsersRepository = $om->getRepository(Telegram\Parser::class);
                $parsers = $parsersRepository->findAllOrderByPhonesCount();

                if (count($parsers) > 0) {
                    $entity->setParser($parsers[0]);
                }
            }
        }

        if ($entity instanceof Telegram\Chat) {
            if ($entity->getParser() == null) {
                /** @var \App\Repository\Telegram\ParserRepository */
                $parsersRepository = $om->getRepository(Telegram\Parser::class);
                $parsers = $parsersRepository->findAllOrderByChatsCount();

                if (count($parsers) > 0) {
                    $entity->setParser($parsers[0]);
                }
            }

            if ($entity->getParser() != null) {
                foreach ($entity->getParser()->getPhones() as $phone) {
                    $chatPhone = new ChatPhone();
                    $chatPhone->setChat($entity);
                    $chatPhone->setPhone($phone);
                    
                    $om->persist($chatPhone);
                }
            }
        }

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $om = $args->getObjectManager();
        $entity = $args->getObject();

        if ($entity instanceof Telegram\ChatMedia) {
            if (($chat = $entity->getChat()) !== null) {
                /** @var \App\Repository\Telegram\ChatRepository */
                $chatRepository = $om->getRepository(Telegram\Chat::class);
    
                $chatRepository->updateLastMedia($chat);
            }
        }

        if ($entity instanceof Telegram\ChatMember) {
            if (($chat = $entity->getChat()) !== null) {
                /** @var \App\Repository\Telegram\ChatRepository */
                $chatRepository = $om->getRepository(Telegram\Chat::class);

                $chatRepository->incrementMembersCount($chat);
            }
        }

        if ($entity instanceof Telegram\Message) {
            if (($chat = $entity->getChat()) !== null) {
                /** @var \App\Repository\Telegram\ChatRepository */
                $chatRepository = $om->getRepository(Telegram\Chat::class);

                $chatRepository->incrementMessagesCount($chat);
                $chatRepository->updateLastMessageDate($chat);
            }
        }

        if ($entity instanceof Telegram\MemberMedia) {
            if (($member = $entity->getMember()) !== null) {
                /** @var \App\Repository\Telegram\MemberRepository */
                $memberRepository = $om->getRepository(Telegram\Member::class);

                $memberRepository->updateLastMedia($member);
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $om = $args->getObjectManager();

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $om = $args->getObjectManager();

        if ($entity instanceof Telegram\ChatMedia) {
            if (($chat = $entity->getChat()) !== null) {
                /** @var \App\Repository\Telegram\ChatRepository */
                $chatRepository = $om->getRepository(Telegram\Chat::class);

                $chatRepository->updateLastMedia($chat);
            }
        }

        if ($entity instanceof Telegram\ChatMember) {
            if (($chat = $entity->getChat()) !== null) {
                /** @var \App\Repository\Telegram\ChatRepository */
                $chatRepository = $om->getRepository(Telegram\Chat::class);

                $chatRepository->decrementMembersCount($chat);
            }
        }

        if ($entity instanceof Telegram\Message) {
            if (($chat = $entity->getChat()) !== null) {
                /** @var \App\Repository\Telegram\ChatRepository */
                $chatRepository = $om->getRepository(Telegram\Chat::class);

                $chatRepository->decrementMessagesCount($chat);
                $chatRepository->updateLastMessageDate($chat);
            }
        }

        if ($entity instanceof Telegram\MemberMedia) {
            if (($member = $entity->getMember()) !== null) {
                /** @var \App\Repository\Telegram\MemberRepository */
                $memberRepository = $om->getRepository(Telegram\Member::class);

                $memberRepository->updateLastMedia($member);
            }
        }
    }
}
