<?php

namespace App\Command;

use App\Entity\Telegram;
use App\Entity\Export;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $baseDir;

    protected static $defaultName = 'app:export';
    protected static $defaultDescription = 'Start the export zip archive generator';

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->logger = $logger;

        $this->baseDir = $this->container->getParameter('kernel.project_dir') . '/public';

        parent::__construct(static::$defaultName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setHelp('The <info>%command.name%</info> starts generation of export archive.')
            ->addArgument('exportId', InputArgument::REQUIRED, 'Export uuid');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exportId = $input->getArgument('exportId');

        try {
            $this->logger->info("Starting generation ZIP archive of export $exportId...");

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
                if (($tempZip = tempnam(sys_get_temp_dir(), 'ZIP')) === false) {
                    throw new \Exception("Couldn't create temp ZIP file.");
                }

                $zip = new \ZipArchive();

                if ($zip->open($tempZip, \ZipArchive::OVERWRITE) !== TRUE) {
                    throw new \Exception("Couldn't open temp ZIP archive.");
                }

                $tempFiles = [];

                $zipBase = (string) $export->getId();

                $entities = $export->getEntities();

                $chat = $export->getChat();
                $chatId = (string) $chat->getId();

                $chatMedias = $chat->getMedia();
                $chatMediasCount = $chatMedias->count();
        
                foreach ($chatMedias as $chatMediaIndex => $chatMedia) {
                    $this->logger->debug("Push media $chatMediaIndex/$chatMediasCount of chat $chatId");
        
                    $path = $chatMedia->getPath();
        
                    if ($path === null || !is_file($this->baseDir . '/' . $path)) {
                        $this->logger->warning("Media $chatMediaIndex/$chatMediasCount of chat $chatId path is not valid. Continue.");

                        continue;
                    }
        
                    $internalId = (string) $chat->getInternalId();
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
        
                    $zip->addFile(
                        $this->baseDir . '/' . $path, 
                        "{$zipBase}/media/{$internalId}_{$chatMediaIndex}.{$extension}"
                    );
        
                    $this->logger->debug("Media $chatMediaIndex/$chatMediasCount of chat $chatId pushed");
                }

                if (in_array("members", $entities)) {
                    $this->logger->info("Pushing members data");

                    $tempFiles[] = $temp = tmpfile();

                    fprintf($temp, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    $memberTitles = $this->getTitles(Telegram\Member::class);
                    $chatMemberRoleTitles = $this->getTitles(Telegram\ChatMemberRole::class, "role_", ['id']);

                    fputcsv($temp, array_merge($memberTitles, $chatMemberRoleTitles), ';');

                    $chatMembers = $chat->getMembers();
                    $chatMembersCount = $chatMembers->count();

                    foreach ($chatMembers as $chatMemberIndex => $chatMember) {
                        $this->logger->debug("Try to push member $chatMemberIndex/$chatMembersCount to CSV");

                        $member = $chatMember->getMember();

                        $memberRow = $this->getRow($member, $memberTitles);
                        $role = $chatMember->getRoles()->last();
                        $roleRow = $this->getRow($role !== false ? $role : null, $chatMemberRoleTitles, "role_");

                        fputcsv($temp, array_merge($memberRow, $roleRow), ';');

                        $this->logger->debug("Member $chatMemberIndex/$chatMembersCount data pushed to CSV");
                        
                        $this->logger->debug("Pushing member $chatMemberIndex/$chatMembersCount medias");
                
                        $memberMedias = $member->getMedia();
                        $memberMediasCount = $memberMedias->count();
                
                        foreach ($memberMedias as $memberMediaIndex => $memberMedia) {
                            $this->logger->debug("Push media $memberMediaIndex/$memberMediasCount of member $chatMemberIndex/$chatMembersCount");
                
                            $path = $memberMedia->getPath();
                
                            if ($path === null || !is_file($this->baseDir . '/' . $path)) {
                                $this->logger->warning("Media $memberMediaIndex/$memberMediasCount of member $chatMemberIndex/$chatMembersCount path is not valid. Continue.");

                                continue;
                            }
                
                            $internalId = (string) $member->getInternalId();
                            $extension = pathinfo($path, PATHINFO_EXTENSION);
                
                            $zip->addFile(
                                $this->baseDir . '/' . $path, 
                                "{$zipBase}/members/media/{$internalId}_{$memberMediaIndex}.{$extension}"
                            );
                
                            $this->logger->debug("Media $memberMediaIndex/$memberMediasCount of member $chatMemberIndex/$chatMembersCount pushed");
                        }
                    }

                    $zip->addFile(stream_get_meta_data($temp)['uri'], "{$zipBase}/members/members.csv");

                    $this->logger->info("Members CSV pushed to ZIP");
                }

                if (in_array("messages", $entities)) {
                    $this->logger->info("Pushing messages data");

                    $tempFiles[] = $temp = tmpfile();

                    fprintf($temp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
                    $messageTitles = $this->getTitles(Telegram\Message::class);
                    $memberTitles = $this->getTitles(Telegram\Member::class, "sender_");
                    $replyToTitles = $this->getTitles(Telegram\Message::class, "reply_to_", ['id']);
            
                    fputcsv($temp, array_merge($messageTitles, $memberTitles, $replyToTitles), ';');

                    $messages = $chat->getMessages();
                    $messagesCount = $messages->count();
            
                    foreach ($messages as $messageIndex => $message) {
                        $this->logger->debug("Try to push message $messageIndex/$messagesCount to CSV");
            
                        $messageRow = $this->getRow($message, $messageTitles);
                        $memberRow = $this->getRow($message->getMember(), $memberTitles, "sender_");
                        $replyToRow = $this->getRow($message->getReplyTo(), $replyToTitles, "reply_to_");
            
                        fputcsv($temp, array_merge($messageRow, $memberRow, $replyToRow), ';');
            
                        $this->logger->debug("Message $messageIndex/$messagesCount data pushed to CSV");

                        $this->logger->debug("Pushing message $messageIndex/$messagesCount medias");
                
                        $messageMedias = $member->getMedia();
                        $messageMediasCount = $messageMedias->count();
                
                        foreach ($messageMedias as $messageMediaIndex => $messageMedia) {
                            $this->logger->debug("Push media $messageMediaIndex/$messageMediasCount of message $messageIndex/$messagesCount");
                
                            $path = $messageMedia->getPath();
                
                            if ($path === null || !is_file($this->baseDir . '/' . $path)) {
                                $this->logger->warning("Media $messageMediaIndex/$messageMediasCount of message $messageIndex/$messagesCount path is not valid. Continue.");

                                continue;
                            }
                
                            $internalId = (string) $member->getInternalId();
                            $extension = pathinfo($path, PATHINFO_EXTENSION);
                
                            $zip->addFile(
                                $this->baseDir . '/' . $path, 
                                "{$zipBase}/messages/media/{$internalId}_{$messageMediaIndex}.{$extension}"
                            );
                
                            $this->logger->debug("Media $messageMediaIndex/$messageMediasCount of message $messageIndex/$messagesCount pushed");
                        }
                    }

                    $zip->addFile(stream_get_meta_data($temp)['uri'], "{$zipBase}/messages/messages.csv");

                    $this->logger->info("Messages CSV pushed to ZIP");
                }

                $zip->close();

                foreach ($tempFiles as $temp) fclose($temp);

                if (!copy($tempZip, $this->baseDir . '/uploads/export/' . (string) $export->getId() . '.zip')) {
                    throw new \Exception("Cannot create ZIP file from temp.");
                }

                unlink($tempZip);
                
                $this->logger->info("ZIP created. Updating export entity.");

                $export->setPath('uploads/export/' . (string) $export->getId() . '.zip');
                $export->setStatus("finished");

                $this->em->persist($export);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                
                $export->setStatus("failed");

                $this->em->persist($export);
                $this->em->flush();

                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return Command::FAILURE;
        }

        $this->logger->info("Command success.");

        return Command::SUCCESS;
    }

    private function getTitles($class, string $prefix = "", array $exclude = []): array
    {
        $this->logger->debug("Creating title row for $class");

        $titles = [];

        $entityClassMetadata = $this->em->getClassMetadata($class);

        foreach ($entityClassMetadata->fieldMappings as $fieldMapping) {
            if (in_array($fieldMapping['fieldName'], array_merge([], $exclude)))
                continue;

            $titles[] = $prefix . $fieldMapping['fieldName'];
        }

        $this->logger->debug("Title row for $class created");

        return $titles;
    }

    private function getRow($entity, array $titles, string $titlePrefix = ""): array
    {
        $this->logger->debug("Creating CSV row");

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

        $this->logger->debug("CSV row created");

        return $row;
    }
}
