<?php

namespace App\Core\Infrastructure\Exchange;

use App\Core\Infrastructure\Exchange\Client\ExchangeClientInterface;
use App\Core\Infrastructure\Exchange\Request\GetExchangeLatestClientRequest;
use App\Core\Infrastructure\Exchange\Response\GetExchangeLatestDto;
use App\Core\Infrastructure\Exchange\Response\Rates\RateDto;
use JsonException;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class ExchangeFacade
{
    public function __construct(
        private readonly ExchangeClientInterface $exchangeClient,
    ) {}

    public function getExchangeLatest(
        #[\SensitiveParameter] string $authorizationToken,
    ): GetExchangeLatestDto {
        try {
            $response = $this->exchangeClient->call(
                request: new GetExchangeLatestClientRequest(
                    authorizationToken: $authorizationToken
                ),
            );

            $response = $this->makeJsonDataByResponse($response);
        } catch (Throwable $e) {
            throw new RuntimeException(
                message: $e->getMessage(),
                code: $e->getCode(),
            );
        }

        return new GetExchangeLatestDto(
            timestamp: $response['timestamp'],
            base: $response['base'],
            rates: array_map(
                static function($currencyCode, $rate) {
                return new RateDto($currencyCode, $rate);
            }, array_keys($response['rates']), $response['rates']),
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function makeJsonDataByResponse(ResponseInterface $response): array
    {
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}