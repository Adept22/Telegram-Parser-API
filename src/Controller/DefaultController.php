<?php

namespace App\Controller;

use App\Entity\TelegramChat;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
class DefaultController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * @var EntityManagerInterface $em
     */
    protected $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer) {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * Если все хорошо, ответит соответствующе.
     *
     * @Rest\Get("/ping")
     */
    public function pingAction(): View
    {
        return View::create(['ping' => 'pong'], Response::HTTP_OK);
    }

    /**
     * Если все хорошо, ответит соответствующе.
     *
     * @Rest\Post("/chat/find")
     */
    public function chatsFindAction(Request $request): View
    {
        $content = json_decode($request->getContent(), true) ?? [];

        if (!isset($content['link'])) throw new BadRequestException('Parameter \'link\' not presented');

        $response = (new \GuzzleHttp\Client())->post('http://tg_checker:7010/check', [ 'json' => $content ]);

        $statusCode = $response->getStatusCode();
        $json = $response->getBody()->getContents();
        $json = json_decode($json, true);

        if ($statusCode === 200) {
            $json['internalId'] = $json['id'];
            unset($json['id']);

            $entity = $this->serializer->deserialize(json_encode($json), TelegramChat::class, 'json');

            try {
                $this->em->persist($entity);
            } catch (ValidationFailedException $ex) {
                return View::create($ex, Response::HTTP_BAD_REQUEST);
            }
    
            $this->em->flush();

            return View::create($json, Response::HTTP_OK);
        }

        return View::create($json, Response::HTTP_BAD_REQUEST);
    }
}
