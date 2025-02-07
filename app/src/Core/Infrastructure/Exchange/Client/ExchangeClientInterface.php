<?php

namespace App\Core\Infrastructure\Exchange\Client;

use App\Core\Infrastructure\Http\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface ExchangeClientInterface
{
    public function call(RequestInterface $request): ResponseInterface;
}