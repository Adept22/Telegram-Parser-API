<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\ChatPhone;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat-phone")
 */
class ChatPhoneController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(ChatPhone::class);
    }
}
