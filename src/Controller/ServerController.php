<?php

namespace App\Controller;

use App\Controller\AbstractEntityController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Server;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/server")
 */
class ServerController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = Server::class;
}