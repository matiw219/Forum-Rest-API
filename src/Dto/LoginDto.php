<?php

namespace App\Dto;

class LoginDto
{
    // username or Email
    private ?string $user;

    private ?string $password;

    public function __construct(?string $user, ?string $password)
    {
        $this->user = $user;
        $this->password = $password;
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