<?php

namespace App\Kernel\EventListener;

use App\Controller\ExportController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Process\Process;

class EventListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onTerminateEvent(TerminateEvent $event)
    {
        list($controllerClass, $function) = explode("::", $event->getRequest()->get("_controller"));

        if ($controllerClass === ExportController::class && $function == "_post") {
            $response = $event->getResponse();
            $data = json_decode($response->getContent(), true);

            $process = new Process(
                ["bin/console", "app:export", $data['id']], 
                $this->container->getParameter('kernel.project_dir')
            );
            $process->run();
        }
    }
}
