<?php

namespace App\ProjectModule\Domain\User\Exception;

use Exception;
use Throwable;

class ForbiddenUserException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 404,
        ?Throwable $previous = null,
    ) {
        if (!$message) {
            $message = 'Пользователь не найден';
        }

        parent::__construct($message, $code, $previous);
    }
}