<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\ParserPhone;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/parser-phone")
 */
class ParserPhoneController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(ParserPhone::class);
    }
}
