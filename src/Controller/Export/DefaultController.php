<?php

namespace App\Controller\Export;

use App\Entity\Export;
use App\Controller\AbstractEntityController;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
class DefaultController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = Export::class;
}