<?php

namespace App\Command;

use App\Entity\Telegram;
use App\Entity\Export;
use App\Repository\ExportRepository;
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

class ExportCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var string $basePath
     */
    private $basePath;

    protected static $defaultName = 'app:export';
    protected static $defaultDescription = 'Start the export zip archive generator';

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->em = $em;
        $this->logger = $logger;

        $this->basePath = $this->container->getParameter('kernel.project_dir') . "/public";

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

        try {
            $output->writeln('Starting generation ZIP archive of export');

            /** @var Export */
            $export = $this->em->find(Export::class, $exportId);

            if (!isset($export)) {
                throw new \Exception("Export not found");
            }

            if ($export->getStatus() != "created") {
                throw new \Exception("Export already running");
            }

            $export->setStatus("running");

            $this->em->persist($export);
            $this->em->flush();

            try {
                $exportPath = $this->basePath . "/uploads/export/" . (string) $export->getId();

                $entities = $export->getEntities();
                $chat = $export->getChat();
                
                if (in_array("members", $entities)) {
                    $file = $this->openCSV($exportPath . "/members/members.csv");

                    if ($file) {
                        $this->makeMembersCSV($file, $chat);

                        fclose($file);
                    } else {
                        $this->logger->warning('WARNING: Couldn\'t create members CSV');
                        $output->writeln('WARNING: Couldn\'t create members CSV');
                    }
                }
                
                if (in_array("messages", $entities)) {
                    $file = $this->openCSV($exportPath . "/messages/messages.csv");

                    if ($file) {
                        $this->makeMessagesCSV($file, $chat);

                        fclose($file);
                    } else {
                        $this->logger->warning('WARNING: Couldn\'t create messages CSV');
                        $output->writeln('WARNING: Couldn\'t create messages CSV');
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

                    if (in_array(basename($file), ['.', '..']))
                        continue;

                    $file = realpath($file);

                    $zipPath = str_replace($this->basePath . "/uploads/export/", '', $file);

                    if (is_dir($file) === true) {
                        $this->logger->debug('Placing zip directory ' . $zipPath . '/ from ' . $file);
                        $output->writeln('DEBUG: Placing zip directory ' . $zipPath . '/ from ' . $file);

                        $zip->addEmptyDir($zipPath . '/');
                    } else if (is_file($file) === true) {
                        $this->logger->debug('Placing zip file ' . $zipPath . ' from ' . $file);
                        $output->writeln('DEBUG: Placing zip file ' . $zipPath . ' from ' . $file);

                        $zip->addFile($file, $zipPath);
                    }
                }

                $this->addMedias(
                    $zip,
                    (string) $export->getId() . '/media',
                    $chat
                );

                if (in_array("members", $entities)) {
                    foreach ($chat->getMembers() as $chatMember) {
                        $this->addMedias(
                            $zip, 
                            (string) $export->getId() . '/members/media',
                            $chatMember->getMember()
                        );
                    }
                }

                if (in_array("messages", $entities)) {
                    foreach ($chat->getMessages() as $message) {
                        $this->addMedias(
                            $zip, 
                            (string) $export->getId() . '/messages/media', 
                            $message
                        );
                    }
                }

                $zip->close();

                $this->deleteDirectory($exportPath);

                $export->setPath("uploads/export/" . (string) $export->getId() . ".zip");
                $export->setStatus("finished");

                $this->em->persist($export);
                $this->em->flush();
            } catch (\Exception $e) {
                $export->setStatus("error");

                $this->em->persist($export);
                $this->em->flush();

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->logger->critical('EXCEPTION:' . $e->getMessage());
            $output->writeln('EXCEPTION:' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function addMedias($zip, $zipPath, $entity)
    {
        $index = 0;

        foreach ($entity->getMedia() as $media) {
            if ($media->getPath() == null) continue;

            $file = realpath($this->basePath . "/" . $media->getPath());

            if (!$file) continue;

            $zip->addFile($file, $zipPath . '/' . (string) $entity->getInternalId() . "_" . $index . '.' . pathinfo($file, PATHINFO_EXTENSION));

            $index++;
        }
    }

    private function openCSV($path)
    {
        if (!file_exists($path)) {
            mkdir(dirname($path), 0777, true);
        }

        $file = fopen($path, "a+");
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        return $file;
    }

    private function makeMembersCSV($file, Telegram\Chat $chat)
    {
        $memberTitles = $this->getTitles(Telegram\Member::class);
        $chatMemberRoleTitles = $this->getTitles(Telegram\ChatMemberRole::class, "role_", ['id']);

        fputcsv($file, array_merge($memberTitles, $chatMemberRoleTitles), ';');

        foreach ($chat->getMembers() as $chatMember) {
            $memberRow = $this->getRow($chatMember->getMember(), $memberTitles);
            $role = $chatMember->getRoles()->last();
            $roleRow = $this->getRow($role !== false ? $role : null, $chatMemberRoleTitles, "role_");

            fputcsv($file, array_merge($memberRow, $roleRow), ';');
        }
    }

    private function makeMessagesCSV($file, Telegram\Chat $chat)
    {
        $messageTitles = $this->getTitles(Telegram\Message::class);
        $memberTitles = $this->getTitles(Telegram\Member::class, "sender_");
        $replyToTitles = $this->getTitles(Telegram\Message::class, "reply_to_", ['id']);

        fputcsv($file, array_merge($messageTitles, $memberTitles, $replyToTitles), ';');

        foreach ($chat->getMessages() as $message) {
            $messageRow = $this->getRow($message, $messageTitles);
            $memberRow = $this->getRow($message->getMember(), $memberTitles, "sender_");
            $replyToRow = $this->getRow($message->getReplyTo(), $replyToTitles, "reply_to_");

            fputcsv($file, array_merge($messageRow, $memberRow, $replyToRow), ';');
        }
    }

    private function getTitles($class, string $prefix = "", array $exclude = []): array
    {
        $titles = [];

        $entityClassMetadata = $this->em->getClassMetadata($class);

        foreach ($entityClassMetadata->fieldMappings as $fieldMapping) {
            if (in_array($fieldMapping['fieldName'], array_merge([], $exclude)))
                continue;

            $titles[] = $prefix . $fieldMapping['fieldName'];
        }

        return $titles;
    } 

    private function getRow($entity, array $titles, string $titlePrefix = ""): array
    {
        $row = [];
        
        if ($entity) {
            foreach ($titles as $title) {
                $title = str_replace($titlePrefix, '', $title);

                $getter = 'get' . ucfirst($title);

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

    private function deleteDirectory($directory)
    { 
        $files = array_diff(scandir($directory), ['.', '..']); 

        foreach ($files as $file) { 
            (is_dir("$directory/$file")) 
                ? $this->deleteDirectory("$directory/$file") 
                : unlink("$directory/$file"); 
        }

        return rmdir($directory); 
    } 
}
