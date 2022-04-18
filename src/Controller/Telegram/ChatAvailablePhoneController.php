<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\ChatAvailablePhone;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat-available-phone")
 */
class ChatAvailablePhoneController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(ChatAvailablePhone::class);
    }
}
