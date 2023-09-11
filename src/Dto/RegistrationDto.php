<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints;
use App\Validation\Constraint as Assert;

class RegistrationDto
{
    #[Constraints\Email(message: 'The email {{ value }} is not a valid email.')]
    private ?string $email = null;

    #[Assert\Password]
    private ?string $password = null;

    #[Assert\Username]
    private ?string $username = null;

    private ?array $roles = null;
    private ?string $numberPhone = null;
    private ?string $country = null;
    private ?string $state = null;

    public function __construct(?string $email, ?string $password, ?string $username, ?array $roles, ?string $numberPhone, ?string $country, ?string $state)
    {
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
        $this->roles = $roles;
        $this->numberPhone = $numberPhone;
        $this->country = $country;
        $this->state = $state;
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