<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Владислав Теренчук <v.terenchuk@soccard.ru>
 */
interface EntityControllerInterface {
    /**
     * Получает сущность
     * 
     * @param string UUID сущности
     * 
     * @return Response Ответ
     */
    public function _get(string $id): Response;

    /**
     * Получает массив сущностей фильтруя по свойствам
     * 
     * @param Request $request Объект запроса
     * 
     * @return Response Ответ
     */
    public function _postFind(Request $request): Response;

    /**
     * Создает сущность
     * 
     * @param Request $request Объект запроса
     * 
     * @return Response Ответ
     */
    public function _post(Request $request): Response;

    /**
     * Изменяет сущность
     * 
     * @param string UUID сущности
     * @param Request $request Объект запроса
     * 
     * @return Response Ответ
     */
    public function _put(string $id, Request $request): Response;

    /**
     * Удаяет сущность
     * 
     * @param string UUID сущности
     * 
     * @return Response Ответ
     */
    public function _delete(string $id): Response;
}