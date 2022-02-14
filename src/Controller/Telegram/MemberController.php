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
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = Member::class;
}
