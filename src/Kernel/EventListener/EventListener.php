<?php

namespace App\Kernel\EventListener;

use App\Entity\Export;
use App\Controller\ExportController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class EventListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function onTerminateEvent(TerminateEvent $event)
    {
        list($controllerClass, $function) = explode("::", $event->getRequest()->get("_controller"));

        if ($controllerClass === ExportController::class && $function == "_post") {
            $response = $event->getResponse();
            $data = json_decode($response->getContent(), true);

            $export = $this->em->find(Export::class, $data['id']);

            $process = new Process(
                ["bin/console", "app:export", (string) $export->getId()], 
                $this->container->getParameter('kernel.project_dir')
            );

            $process->setTimeout(null);
            $process->setIdleTimeout(60);

            try {
                $process->run();
            } catch (ProcessTimedOutException $ex) {
                $export->setStatus('failed');

                $this->em->persist($export);
                $this->em->flush();
            }
        }
    }
}
