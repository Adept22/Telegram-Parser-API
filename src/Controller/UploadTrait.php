<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

trait UploadTrait
{
    protected $path;

    public function setMediaPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Загружает цельный файл
     * 
     * @Route("/{id}/upload", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"POST"})
     */
    public function _postUpload(string $id, Request $request): Response
    {
        $entity = $this->get('doctrine.orm.entity_manager')
            ->find($this->entityClass, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity not found.");
        }

        /** @var UploadedFile */
        $file = $request->files->get('file');

        if (!isset($file)) {
            throw new BadRequestHttpException("File expected.");
        }

        if (!$file->isValid()) {
            throw new BadRequestHttpException($file->getErrorMessage());
        }

        $basePath = $this->getParameter('kernel.project_dir') . "/public";
        
        $file = $file->move(
            $basePath . "/uploads/" . $this->path, 
            (string) $entity->getId() . '.' . $file->getClientOriginalExtension()
        );

        $entity->setPath(str_replace($basePath . '/', '', $file->getPathname()));

        $this->get('doctrine.orm.entity_manager')->persist($entity);
        $this->get('doctrine.orm.entity_manager')->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Загружает кусок файла
     * 
     * @Route("/{id}/chunk", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"POST"})
     */
    public function _postChunkUpload(string $id, Request $request): Response
    {
        $entity = $this->get('doctrine.orm.entity_manager')
            ->find($this->entityClass, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity not found.");
        }

        /** @var UploadedFile */
        $chunk = $request->files->get('chunk');

        if (!isset($chunk)) {
            throw new BadRequestHttpException("File expected.");
        }

        if (!$chunk->isValid()) {
            throw new BadRequestHttpException($chunk->getErrorMessage());
        }

        $queryConstraint = new Assert\Collection([
            "fields" => [
                "filename" => new Assert\Sequentially([
                    new Assert\Type("string"), 
                    new Assert\NotBlank()
                ]),
                "chunkNumber" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _limit value invalid."
                ]),
                "totalChunks" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _limit value invalid."
                ]),
                "totalSize" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _limit value invalid."
                ]),
            ]
        ]);

        $errors = $this->get('validator')
            ->validate($request->query->all(), $queryConstraint);
        
        if ($errors->count() > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $filename = $request->get('filename');
        $chunkNumber = (int) $request->get('chunkNumber');
        $totalChunks = (int) $request->get('totalChunks');
        $totalSize = (int) $request->get('totalSize');

        $tmpBase = sys_get_temp_dir();

        $chunk = $chunk->move($tmpBase, $filename . ".part$chunkNumber");

        if ($chunkNumber >= $totalChunks - 1) {
            $totalChunksSize = 0;
            
            $chunks = glob($tmpBase . "/$filename.part[0-9]*");

            foreach ($chunks as $pathname) $totalChunksSize += (int) filesize($pathname);

            if ($totalChunksSize >= $totalSize) {
                natsort($chunks);

                try {
                    if (($tmp = tempnam(sys_get_temp_dir(), 'php')) === false) {
                        throw new \Exception("Couldn't create temp file.");
                    }
            
                    $this->completeFile($tmp, $filename, $chunks, $entity);
                } catch (\Exception $ex) {
                    foreach ($chunks as $chunk) @unlink($chunk);
                    
                    @unlink($tmp);

                    throw $ex;
                }
            } else {
                throw new \Exception("Not all chunks was uploaded.");
            }
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function completeFile($tmp, $filename, $chunks, $entity)
    {
        foreach ($chunks as $part) {
            if (exec("cat $part >> $tmp", $output, $code) === false) {
                throw new \Exception(implode("\n", $output));
            }

            unlink($part);
        }

        $file = new File($tmp);

        $basePath = $this->getParameter('kernel.project_dir') . "/public";
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $file = $file->move(
            $basePath . "/uploads/" . $this->path, 
            (string) $entity->getId() . '.' . $extension
        );

        $entity->setPath(str_replace($basePath . '/', '', $file->getPathname()));

        $this->get('doctrine.orm.entity_manager')->persist($entity);
        $this->get('doctrine.orm.entity_manager')->flush();
    }

    /**
     * Проверяет загрузился ли кусок
     * 
     * @Route("/{id}/chunk", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"GET"})
     */
    public function _getChunk(string $id, Request $request): Response
    {
        $entity = $this->get('doctrine.orm.entity_manager')
            ->find($this->entityClass, $id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity not found.");
        }

        $queryConstraint = new Assert\Collection([
            "fields" => [
                "filename" => new Assert\Sequentially([
                    new Assert\Type("string"), 
                    new Assert\NotBlank()
                ]),
                "chunkNumber" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _limit value invalid."
                ]),
                "chunkSize" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _limit value invalid."
                ])
            ]
        ]);

        $errors = $this->get('validator')
            ->validate($request->query->all(), $queryConstraint);
        
        if ($errors->count() > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }

        $filename = $request->get('filename');
        $chunkNumber = $request->get('chunkNumber');
        $chunkSize = $request->get('chunkSize');

        $tmpBase = sys_get_temp_dir();

        $chunkName = $tmpBase . "/" . $filename . ".part$chunkNumber";

        if (file_exists($chunkName) && filesize($chunkName) >= $chunkSize) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }
        
        throw new NotFoundHttpException("Chunk not uploaded");
    }
}
