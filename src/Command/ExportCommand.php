<?php

namespace App\Command;

use App\Entity\Telegram;
use App\Entity\Export;
use App\Ftp\Ftp;
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
     * @var Ftp $ftp
     */
    private $ftp;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    protected static $defaultName = 'app:export';
    protected static $defaultDescription = 'Start the export zip archive generator';

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        Ftp $ftp,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->ftp = $ftp;
        $this->logger = $logger;

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
                $tempZip  = tempnam(sys_get_temp_dir(), 'ZIP');

                $zip = new \ZipArchive();

                if ($zip->open($tempZip, \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new \Exception("Couldn't create ZIP archive.");
                }

                $zipBase = (string) $export->getId();

                $entities = $export->getEntities();
                $chat = $export->getChat();

                $this->addMedias($zip, $zipBase . '/media', $chat);

                if (in_array("members", $entities)) {
                    $temp = tmpfile();

                    fprintf($temp, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    $this->makeMembersCSV($temp, $chat);

                    if (!!$content = stream_get_contents($temp)) {
                        $zip->addFromString($zipBase . '/members/members.csv', $content);
                    }

                    fclose($temp);

                    foreach ($chat->getMembers() as $chatMember) {
                        $this->addMedias($zip, $zipBase . '/members/media', $chatMember->getMember());
                    }
                }

                if (in_array("messages", $entities)) {
                    $temp = tmpfile();

                    fprintf($temp, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    $this->makeMessagesCSV($temp, $chat);

                    if (!!$content = stream_get_contents($temp)) {
                        $zip->addFromString($zipBase . '/messages/messages.csv', $content);
                    }

                    fclose($temp);
                    
                    foreach ($chat->getMessages() as $message) {
                        $this->addMedias($zip, '/messages/media', $message);
                    }
                }

                $zip->close();

                $remotePath = 'uploads/export/' . (string) $export->getId() . ".zip";

                $this->ftp->mkdir(pathinfo($remotePath, PATHINFO_DIRNAME), true);

                if (!$this->ftp->put($remotePath, $tempZip)) {
                    throw new \Exception("Can't transfer file via FTP.");
                }

                unlink($tempZip);

                $export->setPath($remotePath);
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

    private function makeMembersCSV($file, $chat)
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

    private function makeMessagesCSV($file, $chat)
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

    private function addMedias($zip, $zipPath, $entity)
    {
        $index = 0;

        foreach ($entity->getMedia() as $media) {
            $path = $media->getPath();

            if ($path === null) continue;

            $file = $this->ftp->getContent($path);

            if ($file === null) continue;

            $internalId = (string) $entity->getInternalId();
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $zip->addFromString("{$zipPath}/{$internalId}_{$index}.{$extension}", $file);

            $index++;
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
