<?php

namespace App\ProjectModule\Domain\User\Entity;

class User
{
    public function __construct(
        private string $login,
        private array $roles,
    ) {}

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function isManager(): bool
    {
        return in_array('manager', $this->roles, true);
    }
}