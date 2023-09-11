<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints;

class RegistrationDto
{
    #[Constraints\Email(message: 'The email {{ value }} is not a valid email.')]
    private ?string $email = null;

    #[\App\Validation\Constraint\Password]
    private ?string $password = null;

    #[\App\Validation\Constraint\Username]
    private ?string $username = null;

    private ?array $roles = null;
    private ?string $numberPhone = null;
    private ?string $country = null;
    private ?string $state = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): void
    {
        $this->roles = $roles;
    }

    public function getNumberPhone(): ?string
    {
        return $this->numberPhone;
    }

    public function setNumberPhone(?string $numberPhone): void
    {
        $this->numberPhone = $numberPhone;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }
}