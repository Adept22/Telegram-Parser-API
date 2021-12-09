<?php

namespace App\Controller;

use App\Entity\TelegramChatsMembersRoles;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/chat-member-role")
 */
class TelegramChatsMembersRolesController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChatsMembersRoles::class;
}
