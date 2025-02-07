<?php

namespace App\ProjectModule\Application\Service\Exchange;

use App\Core\Infrastructure\Exchange\ExchangeFacade;
use App\Core\Infrastructure\Exchange\Response\GetExchangeLatestDto;
use Exception;
use RuntimeException;

class GetExchangeLatestService
{
    public function __construct(
        private readonly ExchangeFacade $exchangeFacade
    ) {}

    /**
     * @throws Exception
     */
    public function execute(#[\SensitiveParameter] string $exchangeToken): GetExchangeLatestDto
    {
        try {
            $dto = $this->exchangeFacade->getExchangeLatest($exchangeToken);
        } catch (RuntimeException) {
            throw new RuntimeException('Failed to retrieve exchange latest data');
        }

        return $dto;
    }
}