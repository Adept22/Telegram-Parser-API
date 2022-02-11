<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\TelegramMessage;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/message")
 */
class TelegramMessageController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramMessage::class;
}
