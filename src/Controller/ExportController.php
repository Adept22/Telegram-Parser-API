<?php

namespace App\Controller;

use App\Controller\AbstractEntityController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Export;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/export")
 */
class ExportController extends AbstractEntityController
{
    public function __construct()
    {
        parent::__construct(Export::class);
    }
}