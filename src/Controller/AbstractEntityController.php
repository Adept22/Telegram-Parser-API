<?php

namespace App\Controller;

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

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
abstract class AbstractEntityController extends AbstractController implements EntityControllerInterface
{
    /**
     * @var string $entityClassName Класс сущности с которой работает контроллер
     */
    protected static $entityClassName;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"GET"})
     */
    public function _get(string $id): Response
    {
        $entity = $this->em->find(static::$entityClassName, $id);

        if ($entity == null) {
            throw new NotFoundHttpException("Entity $id not found.");
        }

        $response = $this->serializer->serialize($entity, 'json');

        return new Response($response, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/find", methods={"POST"})
     */
    public function _postFind(Request $request): Response
    {
        $content = json_decode($request->getContent(), true) ?? [];

        $classMetadata = $this->em->getClassMetadata(static::$entityClassName);

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

        $errors = $this->validator->validate($request->query->all(), $queryConstraint);
        
        if ($errors->count() > 0) {
            throw new BadRequestHttpException($errors->get(count($errors) - 1)->getMessage());
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

        $entities = $this->em->getRepository(static::$entityClassName)
            ->findBy($content, [$sort => $order], $limit, $start);

        $response = $this->serializer->serialize($entities, 'json');

        return new Response($response, Response::HTTP_OK, ['content-type' => 'application/json']);
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

        $entity = $this->serializer->deserialize($request->getContent(), static::$entityClassName, 'json');

        try {
            $this->em->persist($entity);
        } catch (ValidationFailedException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }

        $this->em->flush();

        $response = $this->serializer->serialize($entity, 'json');

        return new Response($response, Response::HTTP_CREATED, ['content-type' => 'application/json']);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"PUT"})
     */
    public function _put(string $id, Request $request): Response
    {
        $content = json_decode($request->getContent(), true) ?? [];

        $entity = $this->em->find(static::$entityClassName, $id);

        if ($entity == null) {
            throw new NotFoundHttpException("Entity $id not found.");
        }

        $content['id'] = $id;

        $entity = $this->serializer->deserialize(json_encode($content), static::$entityClassName, 'json');

        try {
            $this->em->persist($entity);
        } catch (ValidationFailedException $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }

        $this->em->flush();

        $response = $this->serializer->serialize($entity, 'json');

        return new Response($response, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"DELETE"})
     */
    public function _delete(string $id): Response
    {
        $entity = $this->em->find(static::$entityClassName, $id);

        if ($entity == null) {
            throw new NotFoundHttpException("Entity $id not found.");
        }

        $this->em->remove($entity);
        $this->em->flush();

        return new Response('', Response::HTTP_NO_CONTENT, ['content-type' => 'application/json']);
    }
}
