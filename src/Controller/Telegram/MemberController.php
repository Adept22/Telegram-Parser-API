<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\Member;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/member")
 */
class MemberController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(Member::class);
    }
}
