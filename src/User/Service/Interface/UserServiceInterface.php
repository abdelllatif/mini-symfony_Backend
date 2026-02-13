<?php

namespace App\User\Service\Interface;

use App\User\DTO\OAuthResponseDTO;

interface UserServiceInterface
{
    public function authenticateWithGoogle(string $accessToken): OAuthResponseDTO;
    
    public function authenticateWithFacebook(string $accessToken): OAuthResponseDTO;
    
    public function getUserById(int $id): ?array;
    
    public function updateUserProfile(int $userId, array $data): array;
}
