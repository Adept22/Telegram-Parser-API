<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\MessageMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/message-media")
 */
class MessageMediaController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = MessageMedia::class;
}
