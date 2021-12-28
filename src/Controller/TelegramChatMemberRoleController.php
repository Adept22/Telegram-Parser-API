<?php

namespace App\Controller;

use App\Entity\TelegramChatMemberRole;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/chat-member-role")
 */
class TelegramChatMemberRoleController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChatMemberRole::class;
}
