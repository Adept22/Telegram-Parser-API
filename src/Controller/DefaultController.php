<?php

namespace App\Controller;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
class DefaultController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
    
    /**
     * Если все хорошо, ответит соответствующе.
     *
     * @Route("/ping", methods={"GET"})
     */
    public function pingAction(): Response
    {
        $response = $this->serializer->serialize(['ping' => 'pong'], 'json');

        return new Response($response, Response::HTTP_OK, ['content-type' => 'application/json']);
    }
}
