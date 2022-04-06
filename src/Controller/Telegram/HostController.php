<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Telegram\Host;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/host")
 */
class HostController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(Host::class);
    }
}