<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
abstract class AbstractEntityController extends AbstractController implements EntityControllerInterface
{
    /**
     * @var string $entityClass Класс сущности с которой работает контроллер
     */
    protected $entityClass;

    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public static function getSubscribedServices()
    {
        $services = parent::getSubscribedServices();

        $services['doctrine.orm.entity_manager'] = '?'.EntityManagerInterface::class;
        $services['jms_serializer'] = '?'.SerializerInterface::class;
        $services['validator'] = '?'.ValidatorInterface::class;

        return $services;
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"GET"})
     */
    public function _get(string $id): Response
    {
        if ($this->has('doctrine.orm.entity_manager')) {
            $em = $this->get('doctrine.orm.entity_manager');
        } else {
            throw new ServiceUnavailableHttpException();
        }

        $entity = $em->find($this->entityClass, $id);

        if ($entity == null) {
            throw new NotFoundHttpException("Entity $id not found.");
        }

        return $this->json($entity, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/find", methods={"POST"})
     */
    public function _postFind(Request $request): Response
    {
        $content = json_decode($request->getContent(), true) ?? [];

        $classMetadata = $this->get('doctrine.orm.entity_manager')->getClassMetadata($this->entityClass);

        $queryConstraint = new Assert\Collection([
            "fields" => [
                "_sort" => new Assert\Choice([
                    "choices" => $classMetadata->getFieldNames(), 
                    "message" => "The value you selected is not a valid choice. Allow one of {{ choices }}."
                ]),
                "_order" => new Assert\Choice([
                    "choices" => ["ASC", "DESC"], 
                    "message" => "The value you selected is not a valid choice. Allow one of {{ choices }}."
                ]),
                "_limit" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _limit value invalid."
                ]),
                "_start" => new Assert\Regex([
                    "pattern" => "/^\d+$/", 
                    "message" => "Property _start value invalid."
                ])
            ],
            "allowMissingFields" => true,
            "missingFieldsMessage" => "Ожидается параметр {{ field }}"
        ]);

        $errors = $this->get('validator')
            ->validate($request->query->all(), $queryConstraint);
        
        if ($errors->count() > 0) {
            throw new BadRequestHttpException($errors->get(0)->getMessage());
        }
        
        $sort = $request->query->get('_sort') ?? "id";
        $order = $request->query->get('_order') ?? "ASC";
        $limit = $request->query->get('_limit') ?? 50;
        $start = $request->query->get('_start') ?? 0;

        if (isset($content["id"])) {
            unset($content["id"]);
        }

        foreach ($content as $key => $value) {
            if (is_array($value) && isset($value['id'])) {
                $content[$key] = $value['id'];
            }
        }

        $entities = $this->get('doctrine.orm.entity_manager')
            ->getRepository($this->entityClass)
            ->findBy($content, [$sort => $order], $limit, $start);

        return $this->json($entities, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("", methods={"POST"})
     */
    public function _post(Request $request): Response
    {
        $content = json_decode($request->getContent(), true) ?? [];

        if (isset($content['id'])) {
            throw new BadRequestHttpException("Entity id was given. Maybe you mean PUT request?");
        }

        $entity = $this->entity($request->getContent());

        try {
            $this->get('doctrine.orm.entity_manager')->persist($entity);
        } catch (ValidationFailedException $ex) {
            throw new BadRequestHttpException($ex->getViolations()->get(0)->getMessage());
        }

        try {
            $this->get('doctrine.orm.entity_manager')->flush();
        } catch (UniqueConstraintViolationException $ex) {
            throw new ConflictHttpException("Unique constraint violation error.", $ex);
        }

        return $this->json($entity, Response::HTTP_CREATED);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"PUT"})
     */
    public function _put(string $id, Request $request): Response
    {
        $entity = $this->get('doctrine.orm.entity_manager')
            ->find($this->entityClass, $id);
        
        if ($entity == null) {
            throw new NotFoundHttpException("Entity $id not found.");
        }

        $content = json_decode($request->getContent(), true) ?? [];

        $entity = $this->entity(json_encode(array_merge($content, [ 'id' => $id ])));

        try {
            $this->get('doctrine.orm.entity_manager')->persist($entity);
        } catch (ValidationFailedException $ex) {
            throw new BadRequestHttpException($ex->getViolations()->get(0)->getMessage());
        }

        try {
            $this->get('doctrine.orm.entity_manager')->flush();
        } catch (UniqueConstraintViolationException $ex) {
            throw new ConflictHttpException("Unique constraint violation error.", $ex);
        }

        return $this->json($entity, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"DELETE"})
     */
    public function _delete(string $id): Response
    {
        $entity = $this->get('doctrine.orm.entity_manager')->find($this->entityClass, $id);

        if ($entity == null) {
            throw new NotFoundHttpException("Entity $id not found.");
        }

        $this->get('doctrine.orm.entity_manager')->remove($entity);
        $this->get('doctrine.orm.entity_manager')->flush();

        return new Response('', Response::HTTP_NO_CONTENT, ['content-type' => 'application/json']);
    }

    protected function entity(string $data)
    {
        return $this->get('jms_serializer')->deserialize($data, $this->entityClass, 'json');
    }

    /**
     * {@inheritdoc}
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        $json = $this->get('jms_serializer')->serialize($data, 'json');

        return new JsonResponse($json, $status, $headers, true);
    }
}
