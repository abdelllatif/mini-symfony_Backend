<?php

namespace App\User\Service\Interface;

interface OAuthTokenServiceInterface
{
    public function validateGoogleToken(string $token): array;
    
    public function validateFacebookToken(string $token): array;
    
    public function getGoogleUserInfo(string $accessToken): array;
    
    public function getFacebookUserInfo(string $accessToken): array;
}
