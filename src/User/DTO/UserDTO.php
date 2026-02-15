<?php

namespace App\User\DTO;

class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $email,
        public readonly array $roles,
        public readonly ?string $avatar,
        public readonly bool $isVerified,
        public readonly ?string $oauthProvider = null,
        public readonly ?string $oauthProviderId = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            email: $data['email'],
            roles: $data['roles'] ?? ['ROLE_USER'],
            avatar: $data['avatar'] ?? null,
            isVerified: $data['isVerified'] ?? false,
            oauthProvider: $data['oauthProvider'] ?? null,
            oauthProviderId: $data['oauthProviderId'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
            'avatar' => $this->avatar,
            'isVerified' => $this->isVerified,
            'oauthProvider' => $this->oauthProvider,
            'oauthProviderId' => $this->oauthProviderId
        ];
    }
}
