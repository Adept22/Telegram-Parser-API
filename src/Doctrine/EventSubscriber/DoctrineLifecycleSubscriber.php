<?php

namespace App\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

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
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postFlush,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Валидируем сущность
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

        // Валидируем сущность
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
            $classPath = explode('\\', get_class($updatedEntity));

            $client->publish("com.app.entity", [['_' => array_pop($classPath), 'entity' => $updatedEntity]]);
        }
    }
}
