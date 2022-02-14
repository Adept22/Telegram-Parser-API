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
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = ChatMember::class;
}
