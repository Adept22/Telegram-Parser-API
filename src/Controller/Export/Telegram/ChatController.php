<?php

namespace App\Controller\Export\Telegram;

use App\Entity\Telegram;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat")
 */
class ChatController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
    }
    
    /**
     * Если все хорошо, ответит соответствующе.
     *
     * @Rest\Post("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"})
     */
    public function chatExport(string $id, Request $request): View
    {
        $entity = $this->em->find(Telegram\Chat::class, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Сущность c UUID ($id) не существует.");
        }

        $classMetadata = $this->em->getClassMetadata(Telegram\Chat::class);

        $content = json_decode($request->getContent(), true) ?? [];

        $fields = [];

        foreach ($classMetadata->fieldMappings as $fieldMapping) {
            if (isset($content['fields']) && is_array($content['fields'])) {
                if (!in_array($fieldMapping['fieldName'], $content['fields'])) {
                    continue;
                }
            }

            $fields[] = $fieldMapping;
        }

        if (isset($content['members']) && $content['members'] === true) {

        }

        if (isset($content['messages']) && $content['messages'] === true) {
            
        }

        $date = new DateTime();

        $basePath = $this->getParameter('kernel.project_dir') . "/var/export/telegram/chat/" . $id;

        $exportName = $date->format("d.m.Y_H:i:s");
        
        $membersPath = "/members";

        if (!file_exists($basePath . "/" . $exportName . "/" . $membersPath)) {
            mkdir(dirname($basePath . "/" . $exportName . "/" . $membersPath), 0777, true);
        }

        $file = fopen($basePath . "/" . $exportName . "/" . $membersPath . "/members.csv", "a+");

        $titles = [];

        /* MEMBER */

        $memberClassMetadata = $this->em->getClassMetadata(Telegram\Member::class);

        foreach ($memberClassMetadata->fieldMappings as $fieldMapping) {
            if (in_array($fieldMapping['fieldName'], [ 'internalId' ]))
                continue;
            
            $titles[] = "member_" . $fieldMapping['fieldName'];
        }

        /* ! MEMBER */

        /* CHAT MEMBER ROLE */
        
        $chatMemberRoleClassMetadata = $this->em->getClassMetadata(Telegram\ChatMemberRole::class);

        foreach ($chatMemberRoleClassMetadata->fieldMappings as $fieldMapping) {
            if (in_array($fieldMapping['fieldName'], [ 'id', 'internalId' ]))
                continue;
            
            $titles[] = "role_" . $fieldMapping['fieldName'];
        }

        /* ! CHAT MEMBER ROLE */

        fputcsv($file, $titles, ';');

        $csv = [];

        $chatMemberRepository = $this->em->getRepository(Telegram\ChatMember::class);
        $chatMembers = $chatMemberRepository->findBy([ 'chat' => $id ]);

        /** @var Telegram\ChatMember */
        foreach ($chatMembers as $chatMember) {
            $row = [];
            
            /* MEMBER */

            $member = $chatMember->getMember();

            foreach ($memberClassMetadata->fieldMappings as $fieldMapping) {
                if (in_array($fieldMapping['fieldName'], [ 'internalId' ]))
                    continue;
                
                $getter = 'get' . ucfirst($fieldMapping['fieldName']);
                
                if (method_exists($member, $getter)) {
                    $value = $member->$getter();

                    if ($value instanceof DateTime) {
                        $value = $value->format("d.m.Y H:i:s");
                    }

                    $row[] = $value;
                } else {
                    $row[] = null;
                }
            }

            /* ! MEMBER */
            
            /* ROLES */

            $roles = $chatMember->getRoles();
            $role = $roles[0];

            foreach ($chatMemberRoleClassMetadata->fieldMappings as $fieldMapping) {
                if (in_array($fieldMapping['fieldName'], [ 'id', 'internalId' ]))
                    continue;

                $getter = 'get' . ucfirst($fieldMapping['fieldName']);
                
                if (method_exists($role, $getter)) {
                    $value = $role->$getter();

                    if ($value instanceof DateTime) {
                        $value = $value->format("d.m.Y H:i:s");
                    }

                    $row[] = $value;
                } else {
                    $row[] = null;
                }
            }

            /* ! ROLES */

            $csv[] = $row;
        }

        foreach ($csv as $row) {
            fputcsv($file, $row, ';');
        }
        
        fclose($file);

        $zip = new ZipArchive();

        if ($zip->open($basePath . "/" . $exportName . ".zip", ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Couldn't create ZIP archive.");
        }

        return View::create(null, Response::HTTP_OK);
    }
}