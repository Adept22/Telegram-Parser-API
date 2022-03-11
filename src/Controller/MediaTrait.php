<?php

namespace App\Controller;

use App\Ftp\Ftp;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait MediaTrait
{
    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}/upload", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"POST"})
     */
    public function _postUpload(string $id, Request $request, Ftp $ftp): Response
    {
        $entity = $this->em->find(static::$entityClassName, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity not found.");
        }

        $file = $request->files->get('file');

        if (!isset($file)) {
            throw new BadRequestHttpException("Unexpected file given.");
        }

        $remotePath = 'uploads/' . static::$alias;
        $filename = (string) $entity->getId() . '.' . $file->getClientOriginalExtension();

        $ftp->mkdir($remotePath, true);

        if (!$ftp->put($remotePath . '/' . $filename, $file->getPathname())) {
            throw new HttpException("Can't save file.");
        }

        $entity->setPath($remotePath . '/' . $filename);

        $this->em->persist($entity);
        $this->em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT, ['content-type' => 'application/json']);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}/download", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"POST"})
     */
    public function _postDownload(string $id, Ftp $ftp): Response
    {
        $entity = $this->em->find(static::$entityClassName, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity not found.");
        }
        
        if ($entity->getPath() === null) {
            throw new BadRequestHttpException("Entity doesn't have file.");
        }

        $file = $ftp->getContent($entity->getPath());
        
        if ($file === null) {
            throw new NotFoundHttpException("File not found.");
        }

        $response = new Response($file);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            basename($entity->getPath())
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
