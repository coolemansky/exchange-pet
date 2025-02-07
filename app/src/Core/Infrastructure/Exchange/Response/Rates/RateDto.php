<?php

namespace App\Core\Infrastructure\Exchange\Response\Rates;

class RateDto
{
    public function __construct(
        public string $name,
        public float $rate,
    ) {}
}