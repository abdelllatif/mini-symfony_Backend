<?php

namespace App\User\Repository\Interface;

use App\User\Entity\User;
use App\User\DTO\OAuthUserDataDTO;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function findByOAuthProvider(string $provider, string $providerId): ?User;
    
    public function save(User $user): void;
    
    public function delete(User $user): void;
    
    public function createFromOAuth(OAuthUserDataDTO $oauthData): User;
    
    public function updateFromOAuth(User $user, OAuthUserDataDTO $oauthData): User;
}
