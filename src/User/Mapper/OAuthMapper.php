<?php

namespace App\User\Mapper;

use App\User\DTO\OAuthUserDataDTO;
use App\User\Entity\OAuthProvider;

class OAuthMapper implements OAuthMapperInterface
{
    public function mapGoogleUserData(array $userData): OAuthUserDataDTO
    {
        $email = $userData['email'] ?? throw new \InvalidArgumentException('Email is required from Google OAuth data');
        $providerId = $userData['sub'] ?? throw new \InvalidArgumentException('Subject ID is required from Google OAuth data');

        return new OAuthUserDataDTO(
            provider: OAuthProvider::GOOGLE,
            providerId: $providerId,
            email: $email,
            avatar: $userData['picture'] ?? null,
            firstName: $userData['given_name'] ?? null,
            lastName: $userData['family_name'] ?? null,
            isVerified: ($userData['email_verified'] ?? false) === true
        );
    }

    public function mapFacebookUserData(array $userData): OAuthUserDataDTO
    {
        $email = $userData['email'] ?? throw new \InvalidArgumentException('Email is required from Facebook OAuth data');
        $providerId = $userData['id'] ?? throw new \InvalidArgumentException('User ID is required from Facebook OAuth data');

        return new OAuthUserDataDTO(
            provider: OAuthProvider::FACEBOOK,
            providerId: $providerId,
            email: $email,
            avatar: $userData['picture']['data']['url'] ?? null,
            firstName: $userData['first_name'] ?? null,
            lastName: $userData['last_name'] ?? null,
            isVerified: true // Facebook users are verified by default
        );
    }
}
