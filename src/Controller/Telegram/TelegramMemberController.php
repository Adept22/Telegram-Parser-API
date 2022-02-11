<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\TelegramMember;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/member")
 */
class TelegramMemberController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramMember::class;
}
