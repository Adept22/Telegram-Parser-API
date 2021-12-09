<?php

namespace App\Controller;

use App\Entity\TelegramMemberMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/member-media")
 */
class TelegramMemberMediaController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramMemberMedia::class;
}
