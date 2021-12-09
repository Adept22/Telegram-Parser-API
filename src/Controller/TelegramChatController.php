<?php

namespace App\Controller;

use App\Entity\TelegramChat;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/chat")
 */
final class TelegramChatController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChat::class;
}
