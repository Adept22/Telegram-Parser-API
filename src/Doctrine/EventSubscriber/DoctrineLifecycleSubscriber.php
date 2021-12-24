<?php

namespace App\Doctrine\EventSubscriber;

use App\Entity\TelegramChat;
use App\Entity\TelegramPhone;
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

        if ($entity instanceof TelegramChat) {
            /** @var ArrayCollection|TelegramPhone[] */
            $telegramPhones = $args->getObjectManager()
                ->getRepository(TelegramPhone::class)
                ->findAllOrderByTelegramChatsCount(3);

            foreach ($telegramPhones as $telegramPhone) {
                $entity->addPhone($telegramPhone);
            }
        }

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
