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
            Events::preUpdate,
            Events::preRemove
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
                    $entity->addAvailablePhone($phone);
                }
            }
        }

        if ($entity instanceof Telegram\ChatMedia) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatRepository */
            $chatRepository = $om->getRepository(Telegram\Chat::class);

            $chatRepository->updateLastMedia($chat);
        }

        if ($entity instanceof Telegram\ChatMember) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatRepository */
            $chatRepository = $om->getRepository(Telegram\Chat::class);

            $chatRepository->incrementMembersCount($chat);
        }

        if ($entity instanceof Telegram\Message) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatRepository */
            $chatRepository = $om->getRepository(Telegram\Chat::class);

            $chatRepository->incrementMessagesCount($chat);
            $chatRepository->updateLastMessageDate($chat);
        }

        if ($entity instanceof Telegram\MemberMedia) {
            $member = $entity->getMember();

            /** @var \App\Repository\Telegram\MemberRepository */
            $memberRepository = $om->getRepository(Telegram\Member::class);

            $memberRepository->updateLastMedia($member);
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

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        $om = $args->getObjectManager();

        if ($entity instanceof Telegram\ChatMedia) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatRepository */
            $chatRepository = $om->getRepository(Telegram\Chat::class);

            $chatRepository->updateLastMedia($chat);
        }

        if ($entity instanceof Telegram\ChatMember) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatRepository */
            $chatRepository = $om->getRepository(Telegram\Chat::class);

            $chatRepository->decrementMembersCount($chat);
        }

        if ($entity instanceof Telegram\Message) {
            $chat = $entity->getChat();

            /** @var \App\Repository\Telegram\ChatRepository */
            $chatRepository = $om->getRepository(Telegram\Chat::class);

            $chatRepository->decrementMessagesCount($chat);
            $chatRepository->updateLastMessageDate($chat);
        }

        if ($entity instanceof Telegram\MemberMedia) {
            $member = $entity->getMember();

            /** @var \App\Repository\Telegram\MemberRepository */
            $memberRepository = $om->getRepository(Telegram\Member::class);

            $memberRepository->updateLastMedia($member);
        }
    }
}
