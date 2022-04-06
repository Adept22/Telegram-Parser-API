<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\Message;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/message")
 */
class MessageController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(Message::class);
    }
}
