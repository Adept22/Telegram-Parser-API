<?php

namespace App\Doctrine\EventSubscriber;

use App\Entity\Telegram;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
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
        $entity = $args->getObject();

        if ($entity instanceof Telegram\Chat) {
            foreach ($entity->getParser()->getPhones() as $phone) {
                $entity->addAvailablePhone($phone);
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
            $phones = $entity->getParser()->getPhones();

            $entity->getAvailablePhones()->map(function ($availablePhone) use ($entity, $phones) {
                $exist = $phones->exists(function ($key, $phone) use ($availablePhone) {
                    return (string) $phone->getId() === (string) $availablePhone->getId();
                });

                if (!$exist) $entity->removePhone($availablePhone);
            });

            $availablePhones = $entity->getAvailablePhones();

            $entity->getPhones()->map(function ($phone) use ($entity, $availablePhones) {
                $exist = $availablePhones->exists(function ($key, $availablePhone) use ($phone) {
                    return (string) $availablePhone->getId() === (string) $phone->getId();
                });

                if (!$exist) $entity->removePhone($phone);
            });
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
            $class = str_replace("App\Entity\\", '', get_class($updatedEntity));

            $client->publish("com.app.entity", [['_' => $class, 'entity' => $updatedEntity]]);
        }
    }
}
