<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\TelegramPhone;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/phone")
 */
final class TelegramPhoneController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramPhone::class;
}
