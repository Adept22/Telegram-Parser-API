<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\TelegramMessageMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/message-media")
 */
class TelegramMessageMediaController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramMessageMedia::class;
}
