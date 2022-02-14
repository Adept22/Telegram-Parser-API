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
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = Message::class;
}
