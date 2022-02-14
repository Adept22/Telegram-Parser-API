<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\MemberMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/member-media")
 */
class MemberMediaController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = MemberMedia::class;
}
