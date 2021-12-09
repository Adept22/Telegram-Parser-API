<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Validator\Exception\ValidationException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
abstract class AbstractController extends AbstractFOSRestController implements ControllerInterface
{
    /**
     * @var string $entityClassName Класс сущности с которой работает контроллер
     */
    protected static $entityClassName;

    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @var SerializerInterface $serializer
     */
    protected $serializer;

    // /**
    //  * @var Security $security
    //  */
    // protected $security;

    /**
     * @var mixed $repository
     */
    protected $repository;

    public function __construct(ContainerInterface $container) {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->serializer = $container->get('jms_serializer');
        // $this->security = $container->get('security.authorization_checker');

        // Разрешаем админам удалять сущности безвозвратно
        // if ($this->security->isGranted('ROLE_ADMIN')) {
        //     $this->em->getFilters()->disable('softdeleteable');
        // }

        $this->repository = $this->em->getRepository(static::$entityClassName);
    }

    /**
     * {@inheritdoc}
     * 
     * @Rest\Get("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"})
     */
    public function _get(string $id): View
    {
        $entity = $this->find($id);

        return View::create($entity, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * 
     * @Rest\Post("/find")
     * 
     * @Rest\QueryParam(name="_start", default=null, requirements="\d+", description="Сдвиг с начала")
     * @Rest\QueryParam(name="_limit", default=null, requirements="\d+", description="Количество")
     * @Rest\QueryParam(name="_order", default="ASC", requirements="ASC|DESC", description="Порядок сортировки")
     * @Rest\QueryParam(name="_sort", default="id", requirements=".+", description="Свойство сортировки")
     */
    public function _postFind(Request $request, ParamFetcherInterface $paramFetcher): View
    {
        $reserved = ['id', '_sort', '_order', '_limit', '_start'];

        $content = json_decode($request->getContent(), true) ?? [];

        $sort = $paramFetcher->get('_sort');
        $order = $paramFetcher->get('_order');
        $limit = $paramFetcher->get('_limit');
        $start = $paramFetcher->get('_start');

        foreach ($content as $key => $value) {
            if (in_array($key, $reserved, true)) {
                unset($content[$key]);
                continue;
            }

            if (is_array($value)) {
                unset($content[$key]);

                if (isset($value['id'])) {
                    $content[$key] = $value['id'];
                } else {
                    foreach ($value as $item) {
                        if (isset($item['id'])) {
                            $content[$key][] = $item['id'];
                        }
                    }
                }
            }
        }

        $entities = $this->repository->findBy(
            $content,
            [$sort => $order],
            $limit !== '' ? $limit : null,
            $start !== '' ? $start : null
        );

        return View::create($entities, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * 
     * @Rest\Post("")
     */
    public function _post(Request $request, ParamFetcherInterface $paramFetcher): View
    {
        $content = json_decode($request->getContent());

        if (isset($content->id)) {
            unset($content->id);
        }

        $entity = $this->serializer->deserialize(json_encode($content), static::$entityClassName, 'json');

        try {
            $this->em->persist($entity);
        } catch (ValidationFailedException $ex) {
            return View::create($ex, Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return View::create($entity, Response::HTTP_CREATED);
    }

    /**
     * {@inheritdoc}
     * 
     * @Rest\Put("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"})
     */
    public function _put(?string $id, Request $request): View
    {
        $content = json_decode($request->getContent());

        $content->id = $id;

        $entity = $this->serializer->deserialize(json_encode($content), static::$entityClassName, 'json');

        try {
            $this->em->persist($entity);
        } catch (ValidationFailedException $ex) {
            return View::create($ex, Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return View::create($entity, Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     * 
     * @Rest\Delete("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"})
     */
    public function _delete(string $id): View
    {
        $entity = $this->repository->find($id);

        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }

        return View::create([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Получает сущность по UUID
     * 
     * @param string $id UUID сущности
     * 
     * @return AbstractEntity Сущность
     * 
     * @throws NotFoundHttpException Если сущность не найдена
     */
    public function find(string $id): AbstractEntity
    {
        $entity = $this->repository->find($id);

        if (!isset($entity)) {
            throw new NotFoundHttpException("Сущность c UUID ($id) не существует.");
        }

        return $entity;
    }
}
