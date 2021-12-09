<?php

namespace App\Controller;

use App\Entity\TelegramMessage;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/message")
 */
class TelegramMessageController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramMessage::class;
}
