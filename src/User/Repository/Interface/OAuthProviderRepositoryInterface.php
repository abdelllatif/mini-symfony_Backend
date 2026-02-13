<?php

namespace App\User\Repository\Interface;

use App\User\Entity\OAuthProvider;

interface OAuthProviderRepositoryInterface
{
    public function findById(int $id): ?OAuthProvider;
    
    public function findByProviderAndId(string $provider, string $providerId): ?OAuthProvider;
    
    public function save(OAuthProvider $oauthProvider): void;
    
    public function delete(OAuthProvider $oauthProvider): void;
}
