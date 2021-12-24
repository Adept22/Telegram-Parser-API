<?php

namespace App\Thruway\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\InputArgument;
use Thruway\Peer\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thruway\Logging\Logger;
use Thruway\Transport\PawlTransportProvider;

class ThruwayWorkerCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    protected static $defaultName = 'app:thruway:worker:start';
    protected static $defaultDescription = 'Start Thruway WAMP worker';

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelp('The <info>%command.name%</info> starts the Thruway WAMP client.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the worker you\'re starting')
            ->addArgument('instance', InputArgument::OPTIONAL, 'Worker instance number', 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->container->getParameter('voryx_thruway')['enable_logging'])
        {
            Logger::set($this->logger);
        }
        else
        {
            Logger::set(new NullLogger());
        }

        try {
            $output->write("Making a go at starting a Thruway worker.");

            $name             = $input->getArgument('name');
            $config           = $this->container->getParameter('voryx_thruway');
            $loop             = $this->container->get('voryx.thruway.loop');
            $kernel           = $this->container->get('wamp_kernel');
            $workerAnnotation = $kernel->getResourceMapper()->getWorkerAnnotation($name);

            if ($workerAnnotation) {
                $realm = $workerAnnotation->getRealm() ?: $config['realm'];
                $url   = $workerAnnotation->getUrl() ?: $config['url'];
            } else {
                $realm = $config['realm'];
                $url   = $config['url'];
            }

            $transport = new PawlTransportProvider($url);
            $client    = new Client($realm, $loop);

            $client->addTransportProvider($transport);

            $kernel->setProcessName($name);
            $kernel->setClient($client, $this->container->get('voryx.thruway.client.react_connector'));
            $kernel->setProcessInstance($input->getArgument('instance'));

            $client->start();

        } catch (\Exception $e) {
            $this->logger->critical('EXCEPTION:' . $e->getMessage());
            $output->writeln('EXCEPTION:' . $e->getMessage());
        }
    }
}
