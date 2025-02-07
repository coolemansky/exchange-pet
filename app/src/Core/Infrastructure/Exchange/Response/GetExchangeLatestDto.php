<?php

namespace App\Core\Infrastructure\Exchange\Response;

use App\Core\Infrastructure\Exchange\Response\Rates\RateDto;

class GetExchangeLatestDto
{
    /**
     * @param RateDto[] $rates
     */
    public function __construct(
        public int $timestamp,
        public string $base,
        public array $rates,
    ) {}
}