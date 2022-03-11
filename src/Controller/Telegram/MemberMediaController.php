<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Controller\MediaTrait;
use App\Entity\Telegram\MemberMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/member-media")
 */
class MemberMediaController extends AbstractEntityController
{
    use MediaTrait;

    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = MemberMedia::class;
    
    /**
     * {@inheritdoc}
     */
    protected static $alias = 'member';
}
