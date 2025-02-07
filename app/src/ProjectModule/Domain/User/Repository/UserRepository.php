<?php

namespace App\ProjectModule\Domain\User\Repository;

use App\ProjectModule\Domain\User\Entity\User;
use App\ProjectModule\Domain\User\Exception\UserNotFoundException;

interface UserRepository
{
    /**
     * @throws UserNotFoundException
     */
    public function findOrThrowByLogin(string $login): User;
}