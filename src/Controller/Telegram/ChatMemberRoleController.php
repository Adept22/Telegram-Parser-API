<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\ChatMemberRole;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat-member-role")
 */
class ChatMemberRoleController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(ChatMemberRole::class);
    }
}
