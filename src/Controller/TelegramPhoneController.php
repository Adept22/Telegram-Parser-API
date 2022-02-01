<?php

namespace App\Controller;

use App\Entity\TelegramPhone;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/telegram/phone")
 */
final class TelegramPhoneController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = TelegramPhone::class;
}
