<?php

namespace App\Dto;

class LoginDto
{
    public function __construct(
        private readonly string $user,
        private readonly string $password
    ) {
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
