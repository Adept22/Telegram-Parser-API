<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\ChatMember;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat-member")
 */
class ChatMemberController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(ChatMember::class);
    }
}
