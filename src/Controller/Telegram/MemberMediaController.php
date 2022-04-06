<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Controller\UploadTrait;
use App\Entity\Telegram\MemberMedia;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/member-media")
 */
class MemberMediaController extends AbstractEntityController
{
    use UploadTrait;
    
    public function __construct()
    {
        parent::__construct(MemberMedia::class);

        $this->setMediaPath('member');
    }
}
