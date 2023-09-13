<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints;
use App\Validation\Constraint as Assert;

class RegistrationDto
{
    public function __construct(
        #[Constraints\Email(message: 'The email {{ value }} is not a valid email.')]
        private readonly string $email,
        #[Assert\Password]
        private readonly string $password,
        #[Assert\Username]
        private readonly string $username,
        private readonly ?string $numberPhone,
        private readonly ?string $country,
        private readonly ?string $state
    ) {
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
