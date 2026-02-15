<?php

namespace App\User\DTO;

class OAuthLoginResponseDTO
{
    public function __construct(
        public readonly string $token,
        public readonly UserDTO $user,
        public readonly string $provider,
        public readonly int $expiresIn = 3600
    ) {
    }

    public static function create(string $token, UserDTO $user, string $provider, int $expiresIn = 3600): self
    {
        return new self(
            token: $token,
            user: $user,
            provider: $provider,
            expiresIn: $expiresIn
        );
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'user' => $this->user->toArray(),
            'provider' => $this->provider,
            'expiresIn' => $this->expiresIn
        ];
    }
}
