<?php

namespace App\User\Service\Interface;

interface JWTTokenServiceInterface
{
    public function generateToken(int $userId, array $roles = []): array;
    
    public function validateToken(string $token): ?array;
    
    public function refreshToken(string $refreshToken): array;
    
    public function revokeToken(string $token): void;
    
    public function isTokenRevoked(string $token): bool;
}
