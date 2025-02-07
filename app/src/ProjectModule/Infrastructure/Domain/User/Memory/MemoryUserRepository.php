<?php

namespace App\ProjectModule\Infrastructure\Domain\User\Memory;

use App\ProjectModule\Domain\User\Entity\User;
use App\ProjectModule\Domain\User\Exception\UserNotFoundException;
use App\ProjectModule\Domain\User\Repository\UserRepository;

class MemoryUserRepository implements UserRepository
{
    private const USER_LIST = [
        'user' => [
            'login' => 'user',
            'roles' => ['manager', 'worker'],
        ],
        'user1' => [
            'login' => 'user1',
            'roles' => ['admin', 'worker'],
        ],
    ];

    /**
     * @throws UserNotFoundException
     */
    public function findOrThrowByLogin(string $login): User
    {
        if (array_key_exists($login, self::USER_LIST) === false) {
            throw new UserNotFoundException();
        }

        //@todo mapping to UserDto
        $user = self::USER_LIST[$login];

        return new User(
            login: $user['login'],
            roles: $user['roles']
        );
    }
}