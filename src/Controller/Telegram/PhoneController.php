<?php

namespace App\Controller\Telegram;

use App\Controller\AbstractEntityController;
use App\Entity\Telegram\Phone;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/phone")
 */
final class PhoneController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(Phone::class);
    }
}
