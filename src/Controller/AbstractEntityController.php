<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use JMS\Serializer\SerializerInterface;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
abstract class AbstractEntityController extends AbstractController implements ControllerInterface
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

    /**
     * @var Security
     */
    // protected $security;

    /**
     * @var ServiceEntityRepository
     */
    protected $repository;

    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    public function __construct(ContainerInterface $container) {
        /** @var EntityManagerInterface */
        $this->em = $container->get('doctrine.orm.entity_manager');
        /** @var SerializerInterface */
        $this->serializer = $container->get('jms_serializer');
        /** @var ValidatorInterface */
        $this->validator = $container->get('validator');
        // $this->security = $container->get('security.authorization_checker');

        // Разрешаем админам удалять сущности безвозвратно
        // if ($this->security->isGranted('ROLE_ADMIN')) {
        //     $this->em->getFilters()->disable('softdeleteable');
        // }
        
        /** @var ServiceEntityRepository */
        $this->repository = $this->em->getRepository(static::$entityClassName);
        /** @var ClassMetadata */
        $this->classMetadata = $this->em->getClassMetadata(static::$entityClassName);
    }

    /**
     * {@inheritdoc}
     * 
     * @Route("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"}, methods={"GET"})
     */
    public function _get(string $id): Response
    {
        $entity = $this->repository->find($id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Entity ($id) not found.");
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

        $queryConstraint = new Assert\Collection([
            "fields" => [
                "_sort" => new Assert\Choice($this->classMetadata->getFieldNames()),
                "_order" => new Assert\Choice(["ASC", "DESC"]),
                "_limit" => new Assert\Type("integer"),
                "_start" => new Assert\Type("integer")
            ],
            "allowMissingFields" => true,
            "missingFieldsMessage" => "Ожидается параметр {{ field }}"
        ]);

        $errors = $this->validator->validate($request->query->all(), $queryConstraint);
        
        if ($errors->count() > 0) {
            throw new BadRequestException($errors->get(count($errors) - 1)->getMessage());
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

        $entities = $this->repository->findBy($content, [$sort => $order], $limit, $start);

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
            unset($content['id']);
        }

        $entity = $this->serializer->deserialize(json_encode($content), static::$entityClassName, 'json');

        // try {
            $this->em->persist($entity);
        // } catch (ValidationFailedException $ex) {
        //     return View::create($ex, Response::HTTP_BAD_REQUEST);
        // }

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

        $content['id'] = $id;

        $entity = $this->serializer->deserialize(json_encode($content), static::$entityClassName, 'json');

        try {
            $this->em->persist($entity);
        } catch (ValidationFailedException $ex) {
            throw new BadRequestException($ex->getMessage());
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
        $entity = $this->repository->find($id);

        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return new Response('', Response::HTTP_NO_CONTENT, ['content-type' => 'application/json']);
    }
}
