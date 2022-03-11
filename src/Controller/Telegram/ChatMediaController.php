<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Controller\MediaTrait;
use App\Entity\Telegram\ChatMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/chat-media")
 */
class ChatMediaController extends AbstractEntityController
{
    use MediaTrait;
    
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = ChatMedia::class;
    
    /**
     * {@inheritdoc}
     */
    protected static $alias = 'chat';
}
