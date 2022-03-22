<?php

namespace App\Doctrine\EventSubscriber;

use App\Entity\Telegram;
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
            Events::preUpdate
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $entity = $args->getObject();

        if ($entity instanceof Telegram\Phone) {
            if ($entity->getParser() == null) {
                /** @var \App\Repository\Telegram\ParserRepository */
                $parsersRepository = $objectManager->getRepository(Telegram\Parser::class);
                $parsers = $parsersRepository->findAllOrderByPhonesCount();

                if (count($parsers) > 0) {
                    $entity->setParser($parsers[0]);
                }
            }
        }

        if ($entity instanceof Telegram\Chat) {
            if ($entity->getParser() == null) {
                /** @var \App\Repository\Telegram\ParserRepository */
                $parsersRepository = $objectManager->getRepository(Telegram\Parser::class);
                $parsers = $parsersRepository->findAllOrderByChatsCount();

                if (count($parsers) > 0) {
                    $entity->setParser($parsers[0]);
                }
            }

            if ($entity->getParser() != null) {
                foreach ($entity->getParser()->getPhones() as $phone) {
                    $entity->addAvailablePhone($phone);
                }
            }
        }

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $om = $args->getObjectManager();

        if ($entity instanceof Telegram\Chat) {
            $parser = $entity->getParser();

            if ($parser != null) {
                $phones = $parser->getPhones();

                $entity->getAvailablePhones()->map(function ($availablePhone) use ($entity, $phones) {
                    $exist = $phones->exists(function ($key, $phone) use ($availablePhone) {
                        return (string) $phone->getId() === (string) $availablePhone->getId();
                    });

                    if (!$exist) $entity->removeAvailablePhone($availablePhone);
                });

                $availablePhones = $entity->getAvailablePhones();

                $entity->getPhones()->map(function ($phone) use ($entity, $availablePhones) {
                    $exist = $availablePhones->exists(function ($key, $availablePhone) use ($phone) {
                        return (string) $availablePhone->getId() === (string) $phone->getId();
                    });

                    if (!$exist) $entity->removePhone($phone);
                });
            } else {
                $entity->getAvailablePhones()->map(function ($availablePhone) use ($entity) {
                    $entity->removeAvailablePhone($availablePhone);
                });

                $entity->getPhones()->map(function ($phone) use ($entity) {
                    $entity->removePhone($phone);
                });
            }
        }

        if ($entity instanceof Telegram\ChatMedia) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatMediaRepository */
            $chatMediaRepository = $om->getRepository(Telegram\ChatMedia::class);
            $chat->setLastMedia($chatMediaRepository->findLastByChat($chat));

            $om->persist($chat);
        }

        if ($entity instanceof Telegram\ChatMember) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatMemberRepository */
            $chatMemberRepository = $om->getRepository(Telegram\ChatMember::class);
            $chat->setMembersCount($chatMemberRepository->count(['chat' => $chat]));

            $om->persist($chat);
        }

        if ($entity instanceof Telegram\Message) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\MessageRepository */
            $messageRepository = $om->getRepository(Telegram\Message::class);
            $chat->setLastMessage($messageRepository->findLastByChat($chat));

            $chat->setMessagesCount($messageRepository->count(['chat' => $chat]));

            $om->persist($chat);
        }

        if ($entity instanceof Telegram\MemberMedia) {
            $member = $entity->getMember();

            /** @var \App\Repository\Telegram\MemberMediaRepository */
            $memberMediaRepository = $om->getRepository(Telegram\MemberMedia::class);
            $member->setLastMedia($memberMediaRepository->findLastByMember($member));

            $om->persist($member);
        }

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }
}
