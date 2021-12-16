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
    
    /**
     * {@inheritdoc}
     * 
     * @Rest\Post("/{id}", requirements={"id"="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"})
     */
    public function verify(string $id, Request $request): View
    {
        /** @var TelegramPhone $phone */
        $phone = $this->find($id);

        if ($phone->getIsBanned()) {
            return View::create("Номер телефона забанен", Response::HTTP_BAD_REQUEST);
        }

        if ($phone->getIsVerified()) {
            return View::create("Номер телефона уже активирован", Response::HTTP_BAD_REQUEST);
        }

        $content = json_decode($request->getContent(), true) ?? [];

        if (!isset($content['code'])) {
            return View::create("Отсутствует код активации", Response::HTTP_BAD_REQUEST);
        }

        $json = [
            'phone' => $phone,
            'code' => $content['code']
        ];

        $response = (new \GuzzleHttp\Client())->post('http://tg_checker:7010/phone/verify', [ 'json' => $json ]);

        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            $content = json_decode($content, true);

            $phone->setIsVerified(true);

            // try {
                $this->em->persist($phone);
            // } catch (ValidationFailedException $ex) {
            //     return View::create($ex, Response::HTTP_BAD_REQUEST);
            // }
            $this->em->flush();

            return View::create(null, Response::HTTP_NO_CONTENT);
        }

        return View::create("Внутренняя ошибка", Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
