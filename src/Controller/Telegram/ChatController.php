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
    public function __construct()
    {
        parent::__construct(Chat::class);
    }
}
