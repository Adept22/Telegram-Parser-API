<?php

namespace App\Thruway\Command;

use App\Thruway\Process\Command as ThruwayCommand;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thruway\ClientSession;
use Thruway\Connection;
use Thruway\Logging\Logger;
use Thruway\Transport\PawlTransportProvider;
use App\Thruway\Process\ProcessManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ThruwayProcessCommand
 *
 * @package Voryx\ThruwayTestBundle\Command
 */
class ThruwayProcessCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected static $defaultName = 'app:thruway:process';
    protected static $defaultDescription = 'Thruway Process Manager';

    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * @var
     */
    private $config;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $consoleCommand;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
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
            ->setAliases(['tp'])
            ->setHelp('The <info>%command.name%</info> manages thruway sub processes (workers).')
            ->addOption('no-log', null, InputOption::VALUE_NONE, 'Don\'t logging command process')
            ->addOption('no-exec', null, InputOption::VALUE_NONE, 'Don\'t use "exec" command when starting processes')
            ->addArgument('url', InputArgument::OPTIONAL, 'Server URL (default ws://127.0.0.1:7015/)', 'ws://127.0.0.1:7015/')
            ->addArgument('action', InputArgument::REQUIRED, 'Actions: start, status')
            ->addArgument('worker', InputArgument::OPTIONAL, 'Actions for individual workers: start, stop, restart');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-log')) {
            Logger::set($this->logger);
        } else {
            Logger::set(new NullLogger());
        }

        $this->input  = $input;
        $this->output = $output;
        $this->config = $this->container->getParameter('voryx_thruway');

        switch ($input->getArgument('action')) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'status':
                $this->status();
                break;
            case 'add':
                $this->add();
                break;
            default:
                $output->writeln('Expected an action: start, stop, status');
        }
        
        return Command::SUCCESS;
    }

    /**
     * Configure and start the workers
     *
     */
    protected function start()
    {
        $appCmd = \dirname(__DIR__)."/console";
        $binCmd = \dirname(__DIR__)."/../bin/console";

        $this->consoleCommand = file_exists($binCmd) ? $binCmd : $appCmd;

        if ($this->input->getArgument('worker')) {
            $this->startWorker($this->input->getArgument('worker'));
        } else {
            $this->startManager();
        }
    }


    /**
     *
     */
    private function startManager()
    {
        try {
            $env  = $this->container->get('kernel')->getEnvironment();
            $loop = $this->container->get('voryx.thruway.loop');

            $this->processManager = new ProcessManager("process_manager", $loop, $this->container);
            $this->processManager->addTransportProvider(new PawlTransportProvider($this->input->getArgument('url')));

            $this->output->writeln('Starting Thruway Workers...');
            $this->output->writeln("The environment is: {$env}");

            //Add processes for Symfony Command Workers
            $this->addSymfonyCmdWorkers($env);

            //Add external guest Workers
            $this->addShellCmdWorkers();

            //Add processes for regular Workers defined by annotations
            $this->addWorkers($env);

            $this->output->writeln('Done');

            $this->processManager->start();

        } catch (\Exception $e) {
            $this->logger->critical('EXCEPTION:' . $e->getMessage());
            $this->output->writeln('EXCEPTION:' . $e->getMessage());
        }
    }


    /**
     * Make WAMP call
     *
     * @param $uri
     * @param array $args
     * @return null
     */
    private function call($uri, $args = [])
    {
        $result = null;
        $realm  = 'process_manager';

        $connection = new Connection(['realm' => $realm, 'url' => $this->input->getArgument('url'), "max_retries" => 0]);
        $connection->on('open', function (ClientSession $session) use ($uri, $args, $connection, &$result) {
            $session->call($uri, $args)->then(
                function ($res) use ($connection, &$result) {
                    $result = $res[0];
                    $connection->close();
                },
                function ($error) use ($connection, &$result) {
                    $result = $error;
                    $connection->close();
                }
            );
        });

        $connection->open();

        return $result;
    }

    /**
     * @param $worker
     */
    private function startWorker($worker)
    {
        $this->call('start_process', [$worker]);
    }

    /**
     * Stop Worker
     */
    protected function stop()
    {
        if (!$this->input->getArgument('worker')) {
            return;
        }

        $worker = $this->input->getArgument('worker');
        $this->call('stop_process', [$worker]);
    }

    /**
     *
     */
    protected function restart()
    {
        if (!$this->input->getArgument('worker')) {
            return;
        }

        $worker = $this->input->getArgument('worker');
        $this->call('restart_process', [$worker]);
    }

    /**
     * Get the process status
     *
     */
    protected function status()
    {
        $statuses = $this->call('status');

        if (!$statuses) {
            return;
        }

        foreach ($statuses as $status) {

            $uptime = 'Not Started';
            if (isset($status->started_at) && $status->status === 'RUNNING') {
                $uptime = 'up since ' . date("l F jS \@ g:i:s a", $status->started_at);
            }

            $pid = null;
            if (isset($status->pid) && $status->status === 'RUNNING') {
                $pid = "pid {$status->pid}";
            }

            $this->output->writeln(sprintf('%-25s %-3s %-10s %s, %s ', $status->name, $status->process_number, $status->status,
                $pid, $uptime));
        }
    }

    /**
     * Add a new worker instance to the process
     */
    protected function add()
    {
        if (!$this->input->getArgument('worker')) {
            return;
        }
        $worker = $this->input->getArgument('worker');
        $this->call('add_instance', [$worker]);
    }

    /**
     * Add symfony command workers.  These are workers that will only ever have one instance running
     * @param $env
     * @throws \Exception
     */
    protected function addSymfonyCmdWorkers($env)
    {
        $phpBinary = escapeshellarg(PHP_BINARY);
        if (!$this->input->getOption('no-exec')) {
            $phpBinary = 'exec ' . $phpBinary;
        }

        //Default Symfony Command Workers
        $defaultWorkers = [
            'router' => 'thruway:router:start'
        ];

        $onetimeWorkers = array_merge($defaultWorkers, $this->config['workers']['symfony_commands']);

        foreach ($onetimeWorkers as $workerName => $command) {

            if (!$command) {
                continue;
            }

            $this->output->writeln("Adding onetime Symfony worker: {$workerName}");

            $cmd     = "{$phpBinary} {$this->consoleCommand} --env={$env} {$command}";
            $command = new ThruwayCommand($workerName, $cmd);

            $this->processManager->addCommand($command);
        }
    }

    /**
     * Add regular shell command workers.
     * @throws \Exception
     */
    protected function addShellCmdWorkers()
    {
        $shellWorkers = $this->config['workers']['shell_commands'];

        foreach ($shellWorkers as $workerName => $command) {

            if (!$command) {
                continue;
            }

            $this->output->writeln("Adding onetime shell worker: {$workerName}");
            $command = new ThruwayCommand($workerName, $command);

            $this->processManager->addCommand($command);
        }
    }


    /**
     * Add regular workers.  Theses are workers that can have multiple instances running
     *
     * @param $env
     * @throws \Exception
     */
    protected function addWorkers($env)
    {
        $phpBinary = escapeshellarg(PHP_BINARY);
        if (!$this->input->getOption('no-exec')) {
            $phpBinary = 'exec ' . $phpBinary;
        }
        $resourceMapper = $this->container->get('voryx.thruway.resource.mapper');
        $mappings       = $resourceMapper->getAllMappings();

        foreach ($mappings as $workerName => $mapping) {
            $this->output->writeln("Adding workers: {$workerName}");

            $workerAnnotation = $resourceMapper->getWorkerAnnotation($workerName);
            $numprocs         = $workerAnnotation ? $workerAnnotation->getMaxProcesses() : 1;
            $cmd              = "{$phpBinary} {$this->consoleCommand} --env={$env} thruway:worker:start {$workerName} 0";
            $command          = new ThruwayCommand($workerName, $cmd);

            $command->setMaxInstances($numprocs);
            $this->processManager->addCommand($command);
        }
    }
}
