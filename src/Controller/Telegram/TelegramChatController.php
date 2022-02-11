<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\TelegramChat;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat")
 */
final class TelegramChatController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChat::class;
}
