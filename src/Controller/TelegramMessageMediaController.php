<?php

namespace App\Controller;

use App\Entity\TelegramMessageMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/message-media")
 */
class TelegramMessageMediaController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramMessageMedia::class;
}
