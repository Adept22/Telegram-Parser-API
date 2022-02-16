<?php

namespace App\Thruway\Command;

use App\Entity\Telegram;
use App\Entity\Export;
use App\Repository\ExportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Thruway\Logging\Logger;
use Thruway\Transport\RatchetTransportProvider;

class ExportCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var string $basePath
     */
    private $basePath;

    protected static $defaultName = 'app:export';
    protected static $defaultDescription = 'Start the export archive generator';

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;

        $this->basePath = $this->container->getParameter('kernel.project_dir') . "/var/export";

        parent::__construct(static::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelp('The <info>%command.name%</info> starts generation of export archive.')
            ->addArgument('exportId', InputArgument::REQUIRED, 'Export uuid')
            ->addOption('no-log', null, InputOption::VALUE_NONE, 'Don\'t logging command process');
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

        $exportId = $input->getArgument('exportId');

        /** @var EntityManagerInterface */
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $output->writeln('Starting generation ZIP archive of export');

            /** @var Export */
            $export = $em->find(Export::class, $exportId);

            if (!isset($export)) {
                throw new \Exception("Export not found");
            }

            if ($export->getStatus() != "created") {
                throw new \Exception("Export already running");
            }

            $export->setStatus("running");

            try {
                $exportPath = $this-> basePath . "/" . (string) $export->getId();
                
                if (in_array("members", $export->getEntities())) {
                    $file = $this->openCSV($exportPath . "/members/members.csv");

                    if ($file) {
                        $this->makeMembersCSV($file, $export->getChat());

                        fclose($file);
                    } else {
                        $this->logger->warning('WARNING: Couldn\'t create members CSV');
                        $output->writeln('WARNING: Couldn\'t create members CSV');
                    }
                }
                
                if (in_array("messages", $export->getEntities())) {
                    $file = $this->openCSV($exportPath . "/messages/messages.csv");

                    if ($file) {
                        $this->makeMessagesCSV($file, $export->getChat());

                        fclose($file);
                    } else {
                        $this->logger->warning('WARNING: Couldn\'t messages members CSV');
                        $output->writeln('WARNING: Couldn\'t messages members CSV');
                    }
                }

                $zip = new \ZipArchive();

                if ($zip->open($exportPath . ".zip", \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new \Exception("Couldn't create ZIP archive.");
                }

                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($exportPath), 
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($files as $file) {
                    $file = str_replace('\\', '/', $file);

                    if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..']))
                        continue;

                    $file = realpath($file);

                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($exportPath . '/', '', $file . '/'));
                    } else if (is_file($file) === true) {
                        $zip->addFile($file, str_replace($exportPath . '/', '', $file));
                    }
                }

                $export->setStatus("finished");
            } catch (\Exception $e) {
                $export->setStatus("error");

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->logger->critical('EXCEPTION:' . $e->getMessage());
            $output->writeln('EXCEPTION:' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function openCSV($path)
    {
        if (!file_exists($path)) {
            mkdir(dirname($path), 0777, true);
        }

        return fopen($path, "a+");
    }

    private function makeMembersCSV($file, Telegram\Chat $chat)
    {
        $memberTitles = $this->getTitles(Telegram\Member::class);
        $chatMemberRoleTitles = $this->getTitles(Telegram\ChatMemberRole::class, "role_", ['id']);

        fputcsv($file, array_merge($memberTitles, $chatMemberRoleTitles), ';');

        $csv = [];

        foreach ($chat->getMembers() as $chatMember) {
            $memberRow = $this->getRow($chatMember->getMember());
            $roleRow = $this->getRow($chatMember->getRoles()->last(), ['id']);

            $csv[] = array_merge($memberRow, $roleRow);
        }

        foreach ($csv as $row) {
            fputcsv($file, $row, ';');
        }
    }

    private function makeMessagesCSV($file, Telegram\Chat $chat)
    {
        $messageTitles = $this->getTitles(Telegram\Message::class);
        $memberTitles = $this->getTitles(Telegram\Member::class);
        $replyToTitles = $this->getTitles(Telegram\Message::class, "reply_to_", ['id']);

        fputcsv($file, array_merge($messageTitles, $memberTitles, $replyToTitles), ';');

        $csv = [];

        foreach ($chat->getMessages() as $message) {
            $messageRow = $this->getRow($message);
            $memberRow = $this->getRow($message->getMember());
            $replyToRow = $this->getRow($message->getReplyTo(), ['id']);

            $csv[] = array_merge($messageRow, $memberRow, $replyToRow);
        }

        foreach ($csv as $row) {
            fputcsv($file, $row, ';');
        }
    }

    private function getTitles($class, string $prefix = "", array $exclude = []): array
    {
        $titles = [];

        $entityClassMetadata = $this->em->getClassMetadata($class);

        foreach ($entityClassMetadata->fieldMappings as $fieldMapping) {
            if (in_array($fieldMapping['fieldName'], array_merge(['internalId'], $exclude)))
                continue;

            $titles[] = $prefix . $fieldMapping['fieldName'];
        }

        return $titles;
    } 

    private function getRow($entity, array $exclude = []): array
    {
        $row = [];
        
        if (!isset($entity)) {
            $entityClassMetadata = $this->em->getClassMetadata(get_class($entity));

            foreach ($entityClassMetadata->fieldMappings as $fieldMapping) {
                if (in_array($fieldMapping['fieldName'], array_merge(['internalId'], $exclude)))
                    continue;

                $getter = 'get' . ucfirst($fieldMapping['fieldName']);

                if (method_exists($entity, $getter)) {
                    $value = $entity->$getter();

                    if ($value instanceof \DateTime) {
                        $value = $value->format("d.m.Y H:i:s");
                    }

                    $row[] = $value;
                } else {
                    $row[] = null;
                }
            }
        }

        return $row;
    }
}
