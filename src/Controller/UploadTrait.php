<?php

namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait UploadTrait
{
    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}/upload", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"POST"})
     */
    public function _postUpload(string $id, Request $request): Response
    {
        $entity = $this->em->find(static::$entityClassName, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity ($id) not found.");
        }
        
        $file = $request->files->get('file');
        
        if (!isset($file)) {
            throw new BadRequestHttpException("Unexpected file given.");
        }

        $basePath = $this->getParameter('kernel.project_dir') . "/public";
        
        $file = $file->move($basePath . '/uploads/' . static::$alias, (string) $entity->getId() . '.' . $file->getClientOriginalExtension());

        $entity->setPath(str_replace($basePath . '/', '', $file->getPathname()));

        $this->em->persist($entity);
        $this->em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT, ['content-type' => 'application/json']);
    }
}