<?php

namespace App\Controller;

use App\Entity\TelegramChatsMembers;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/chat-member")
 */
class TelegramChatsMembersController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChatsMembers::class;
}
