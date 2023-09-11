<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints;
use App\Validation\Constraint as Assert;

class RegistrationDto
{
    public function __construct(
        #[Constraints\Email(message: 'The email {{ value }} is not a valid email.')]
        private string $email,
        #[Assert\Password]
        private string $password,
        #[Assert\Username]
        private string $username,
        private ?array $roles,
        private ?string $numberPhone,
        private ?string $country,
        private ?string $state
    ){
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getNumberPhone(): ?string
    {
        return $this->numberPhone;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getState(): ?string
    {
        return $this->state;
    }
}