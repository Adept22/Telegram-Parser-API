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
            Events::preUpdate
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

            foreach ($entity->getParser()->getChats() as $chat) {
                $chatPhone = new ChatPhone();
                $chatPhone->setPhone($entity);
                $chatPhone->setChat($chat);
                
                $om->persist($chatPhone);
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

            foreach ($entity->getParser()->getPhones() as $phone) {
                $chatPhone = new ChatPhone();
                $chatPhone->setChat($entity);
                $chatPhone->setPhone($phone);
                
                $om->persist($chatPhone);
            }
        }

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (count($violations = $this->validator->validate($entity)) > 0) {
            throw new ValidationFailedException($entity, $violations);
        }
    }
}
