<?php

namespace App\Dto;

class LoginDto
{

    public function __construct(
        private string $user,
        private string $password
    ){
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