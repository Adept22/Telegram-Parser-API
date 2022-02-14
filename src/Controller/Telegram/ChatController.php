<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\Chat;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat")
 */
final class ChatController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = Chat::class;
}
