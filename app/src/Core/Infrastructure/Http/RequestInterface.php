<?php

namespace App\Core\Infrastructure\Http;

interface RequestInterface
{
    public function getMethod(): string;

    public function getUri(): string;

    public function getAuthorizationToken(): string;

}