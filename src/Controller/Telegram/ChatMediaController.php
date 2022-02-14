<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\ChatMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat-media")
 */
class ChatMediaController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = ChatMedia::class;
}
