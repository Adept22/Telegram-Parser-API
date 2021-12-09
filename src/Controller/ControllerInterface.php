<?php

namespace App\Controller;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
interface ControllerInterface {
    /**
     * Получает сущность
     * 
     * @param string|null UUID сущности
     * 
     * @return View Представление
     */
    public function _get(string $id): View;

    /**
     * Получает сущности
     * 
     * @param Request $request Объект запроса
     * @param ParamFetchInterface Сервис валидирующий параметры
     * 
     * @return View Представление
     */
    public function _post(Request $request, ParamFetcherInterface $paramFetcher): View;

    /**
     * Создает или изменяет сущность
     * 
     * @param string|null UUID сущности
     * @param Request $request Объект запроса
     * 
     * @return View Представление
     */
    public function _put(?string $id, Request $request): View;

    /**
     * Получает сущности по параметрам
     * 
     * @param string UUID сущности
     * 
     * @return View Представление
     */
    public function _delete(string $id): View;
}