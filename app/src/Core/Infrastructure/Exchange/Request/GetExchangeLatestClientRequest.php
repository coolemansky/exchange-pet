<?php

namespace App\Core\Infrastructure\Exchange\Request;

use App\Core\Infrastructure\Http\RequestInterface;

class GetExchangeLatestClientRequest implements RequestInterface
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $authorizationToken,
    ) {}

    public function getMethod(): string
    {
        return 'GET';
    }

    public function getUri(): string
    {
        return sprintf(
            'latest.json?app_id=%s',
            $this->authorizationToken,
        );
    }

    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }
}