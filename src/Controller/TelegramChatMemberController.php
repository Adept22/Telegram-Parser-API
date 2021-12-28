<?php

namespace App\Controller;

use App\Entity\TelegramChatMember;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/chat-member")
 */
class TelegramChatMemberController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramChatMember::class;
}
