<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints;
use App\Constraint as Mati;

class RegistrationDto
{

    #[Constraints\Email(message: 'The email {{ value }} is not a valid email.',)]
    private ?string $email = null;

    #[Mati\Password]
    private ?string $password = null;

    #[Mati\Username]
    private ?string $username = null;

    private ?array $roles = null;
    private ?string $numberPhone = null;
    private ?string $country = null;
    private ?string $state = null;

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return array|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array|null $roles
     */
    public function setRoles(?array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string|null
     */
    public function getNumberPhone(): ?string
    {
        return $this->numberPhone;
    }

    /**
     * @param string|null $numberPhone
     */
    public function setNumberPhone(?string $numberPhone): void
    {
        $this->numberPhone = $numberPhone;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

}