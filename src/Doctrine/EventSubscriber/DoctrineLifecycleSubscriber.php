<?php

namespace App\Doctrine\EventSubscriber;

use App\Entity\Telegram;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Util\ClassUtils;
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

    protected $updatedEntities = [];

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
            Events::postUpdate,
            Events::postFlush,
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

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->updatedEntities[] = $entity;
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

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

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        $this->updatedEntities[] = $entity;
    }

    public function postFlush()
    {
        $client = $this->container->get('thruway.client');

        foreach ($this->updatedEntities as $updatedEntity) {
            $class = str_replace("App\Entity\\", '', ClassUtils::getClass($updatedEntity));

            $client->publish("com.app.entity", [['_' => $class, 'entity' => $updatedEntity]]);
        }
    }
}
