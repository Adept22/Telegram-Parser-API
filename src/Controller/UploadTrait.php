<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $entity = $this->repository->find($id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity ($id) not found.");
        }

        $file = $request->files->get('file');

        if (!isset($file)) {
            throw new BadRequestException("Unexpected file given.");
        }

        $basePath = $this->container->getParameter('kernel.project_dir') . "/var/upload";
        
        $file = $file->move($basePath . '/' . parent::$entityAlias, $file->getClientOriginalName());

        $entity->setPath(str_replace($basePath . '/', '', $file->getPathname()));

        return new Response(null, Response::HTTP_NO_CONTENT, ['content-type' => 'application/json']);
    }
}