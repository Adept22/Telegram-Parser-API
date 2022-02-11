<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram")
 */
class TelegramController extends AbstractFOSRestController
{
    /**
     * Если все хорошо, ответит соответствующе.
     *
     * @Rest\Post("/member/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"})
     */
    public function memberExport(string $id, Request $request): View
    {
        $entity = $this->find($id);

        $content = json_decode($request->getContent(), true) ?? [];

        if (isset($content['props'])) {

        }

        return View::create(['ping' => 'pong'], Response::HTTP_OK);
    }
}
