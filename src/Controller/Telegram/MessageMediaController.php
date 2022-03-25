<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Controller\UploadTrait;
use App\Entity\Telegram\MessageMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/message-media")
 */
class MessageMediaController extends AbstractEntityController
{
    use UploadTrait;

    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = MessageMedia::class;
    
    /**
     * {@inheritdoc}
     */
    protected static $alias = 'message';
}
