<?php

namespace App\Controller;

use App\Ftp\Ftp;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
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
     * @Route("/{id}/download", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"GET"})
     */
    public function _getDownload(string $id, Ftp $ftp): Response
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

        $filename = basename($entity->getPath());

        $response = new Response($file);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Disposition', $disposition);
        
        // To generate a file download, you need the mimetype of the file
        $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
        
        // Set the mimetype with the guesser or manually
        if($mimeTypeGuesser->isSupported()){
            // Guess the mimetype of the file according to the extension of the file
            $response->headers->set('Content-Type', $mimeTypeGuesser->guess($filename));
        }else{
            // Set the mimetype of the file manually, in this case for a text file is text/plain
            $response->headers->set('Content-Type', 'text/plain');
        }

        return $response;
    }
}
