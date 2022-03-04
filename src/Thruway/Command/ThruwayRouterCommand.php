<?php

namespace App\Thruway\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thruway\Transport\RatchetTransportProvider;

class ThruwayRouterCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    private $logger;

    protected static $defaultName = 'app:thruway:router:start';
    protected static $defaultDescription = 'Start the default Thruway WAMP router';

    public function __construct(ContainerInterface $container, \Psr\Log\LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;

        parent::__construct(static::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelp('The <info>%command.name%</info> starts the Thruway WAMP router.')
            ->addOption('no-log', null, InputOption::VALUE_NONE, 'Don\'t logging command process')
            ->addOption('ip', 'i', InputOption::VALUE_OPTIONAL, 'Listening IP address (default 0.0.0.0)', '0.0.0.0')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Listening port (default 8080)', 8080);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-log')) {
            \Thruway\Logging\Logger::set($this->logger);
        } else {
            \Thruway\Logging\Logger::set(new \Psr\Log\NullLogger());
        }

        try {
            $output->writeln('Making a go at starting the Thruway Router');

            //Get the Router Service
            $server = $this->container->get('voryx.thruway.server');

            if ($input->getOption('ip') !== '0.0.0.0' || $input->getOption('port') !== 8080) {
                //Trusted provider (bound to loopback and requires no authentication)
                $trustedProvider = new RatchetTransportProvider(
                    $input->getOption('ip'), 
                    (int) $input->getOption('port')
                );
                $trustedProvider->setTrusted(true);
                $server->addTransportProvider($trustedProvider);
            }

            $server->start();
        } catch (\Exception $e) {
            $this->logger->critical('EXCEPTION:' . $e->getMessage());
            $output->writeln('EXCEPTION:' . $e->getMessage());
        }


        return Command::SUCCESS;
    }
}
