<?php

namespace App\Controller;

use App\Entity\TelegramChatMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/chat-media")
 */
class TelegramChatMediaController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChatMedia::class;
}
