<?php

namespace App\Controller;

use App\Controller\AbstractEntityController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Export;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 * 
 * @Route("/export")
 */
class ExportController extends AbstractEntityController
{
    /**
     * {@inheritdoc}
     */
    protected static $entityClassName = Export::class;

    /**
     * {@inheritdoc}
     * 
     * @Rest\Post("")
     */
    public function _post(Request $request, ParamFetcherInterface $paramFetcher): View
    {
        $view = parent::_post($request, $paramFetcher);

        $data = $view->getData();

        $process = new Process(['bin/console', 'app:export', $data['id']]);
        $process->start();

        return $view;
    }
}