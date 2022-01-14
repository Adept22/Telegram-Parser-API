<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
class DefaultController extends AbstractFOSRestController
{
    /**
     * Если все хорошо, ответит соответствующе.
     *
     * @Rest\Get("/ping")
     */
    public function pingAction(): View
    {
        return View::create(['ping' => 'pong'], Response::HTTP_OK);
    }
}
