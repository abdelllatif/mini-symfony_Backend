<?php

namespace App\User\DTO;

class OAuthUserDataDTO
{
    private string $provider;
    private string $providerId;
    private string $email;
    private ?string $avatar;
    private ?string $firstName;
    private ?string $lastName;
    private bool $isVerified;

    public function __construct(
        string $provider,
        string $providerId,
        string $email,
        ?string $avatar = null,
        ?string $firstName = null,
        ?string $lastName = null,
        bool $isVerified = true
    ) {
        $this->provider = $provider;
        $this->providerId = $providerId;
        $this->email = $email;
        $this->avatar = $avatar;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->isVerified = $isVerified;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function setProviderId(string $providerId): void
    {
        $this->providerId = $providerId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function getFullName(): ?string
    {
        if ($this->firstName && $this->lastName) {
            return trim($this->firstName . ' ' . $this->lastName);
        }

        return $this->firstName ?: $this->lastName;
    }
}
