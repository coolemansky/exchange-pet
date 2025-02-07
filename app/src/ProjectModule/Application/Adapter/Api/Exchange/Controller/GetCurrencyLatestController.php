<?php

namespace App\ProjectModule\Application\Adapter\Api\Exchange\Controller;

use App\Core\Adapter\AbstractController;
use App\ProjectModule\Application\Service\Exchange\GetExchangeLatestService;
use App\ProjectModule\Domain\User\Exception\UserNotFoundException;
use App\ProjectModule\Domain\User\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetCurrencyLatestController extends AbstractController
{
    public function __construct(
        private readonly GetExchangeLatestService $getCurrencyLatestService,
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(
    '/api/v1/exchange/get/latest',
    name: 'api_v1_get_exchanges_latest',
    methods: ['GET'],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** todo Здесь должна быть нормальная статусная модель и работа с грантами.
         *   не стал заморачиваться и тянуть в проект symfony-security еще.
         *   Делаю вручную валидацию и проваливаюсь в сервис.
         **/
        $userToken = $this->getUserByRequest($request);
        $exchangeToken = $this->getExchangeTokenByRequest($request);

        if ($userToken === null || $exchangeToken === null) {
            return new JsonResponse(
                data: 'You are not authenticated',
                status: Response::HTTP_UNAUTHORIZED
            );
        }

        try {
            $user = $this->userRepository->findOrThrowByLogin($userToken);
        } catch (UserNotFoundException) {
            return new JsonResponse(
                data: 'User not found',
                status: Response::HTTP_NOT_FOUND
            );
        }

        if ($user->isManager() === false) {
            return new JsonResponse(
                data: 'User is not a manager',
                status: Response::HTTP_FORBIDDEN
            );
        }

        try {
            $response = $this->getCurrencyLatestService->execute($exchangeToken);
        }catch (Exception) {
            return new JsonResponse(
                data: 'Something wrong with exchange service',
                status: Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }


        return new JsonResponse($response);
    }
}