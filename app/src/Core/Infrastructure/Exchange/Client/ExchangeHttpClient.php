<?php

namespace App\Core\Infrastructure\Exchange\Client;

use App\Core\Infrastructure\Http\RequestInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Throwable;

class ExchangeHttpClient implements HttpClientInterface, ExchangeClientInterface
{
    protected HttpClientInterface $client;

    public function __construct(
        HttpClientInterface $client,
        string $baseUri,
    ) {
        $this->client = $client->withOptions([
            'ciphers' => 'DEFAULT@SECLEVEL=1',
            'base_uri' => $baseUri,
        ]);
    }

    public function call(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->request(
                method: $request->getMethod(),
                url: $request->getUri(),
            );

        } catch (Throwable $e) {
            throw new RuntimeException(
                message: $e->getMessage(),
                code: $e->getCode(),
            );
        }

        return $response;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $response = $this->client->request($method, $url, $options);

        $this->checkResponse($response);

        return $response;
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        return $this->client->withOptions($options);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function checkResponse(ResponseInterface $response): void
    {
        $code = $response->getStatusCode();

        if ($code < 300) {
            return;
        }

        $error = $response->getInfo();

        $errorMessage = $error['response_headers'][0] ?? '';

        if ($code === 404) {
            $errorCode = 'Route not found';
        }

        throw new RuntimeException(
            message: $errorMessage,
            code: $errorCode ?? null,
        );
    }
}