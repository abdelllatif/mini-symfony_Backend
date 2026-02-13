<?php

namespace App\User\DTO;

class OAuthResponseDTO
{
    private UserDTO $user;
    private string $token;
    private string $refreshToken;
    private int $expiresIn;
    private string $tokenType;

    public function __construct(
        UserDTO $user,
        string $token,
        string $refreshToken,
        int $expiresIn,
        string $tokenType = 'bearer'
    ) {
        $this->user = $user;
        $this->token = $token;
        $this->refreshToken = $refreshToken;
        $this->expiresIn = $expiresIn;
        $this->tokenType = $tokenType;
    }

    public function getUser(): UserDTO
    {
        return $this->user;
    }

    public function setUser(UserDTO $user): void
    {
        $this->user = $user;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function setTokenType(string $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'token' => $this->token,
            'refreshToken' => $this->refreshToken,
            'expiresIn' => $this->expiresIn,
            'tokenType' => $this->tokenType,
        ];
    }
}
