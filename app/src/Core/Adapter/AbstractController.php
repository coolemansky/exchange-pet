<?php

declare(strict_types=1);

namespace App\Core\Adapter;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController extends BaseAbstractController
{
    public const AUTH_HEADER = 'authorization';
    public const INTEGRATION_NAME_HEADER = 'exchange-auth';

    protected function getUserByRequest(Request $request): ?string
    {
        $token = $request->headers->get(self::AUTH_HEADER) ?: '';

        return str_replace('Bearer ', '', $token);
    }

    protected function getExchangeTokenByRequest(Request $request): ?string
    {
        return $request->headers->get(self::INTEGRATION_NAME_HEADER);
    }
}
