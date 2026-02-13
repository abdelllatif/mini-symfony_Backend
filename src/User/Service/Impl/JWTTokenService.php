<?php

namespace App\User\Service\Impl;

use App\User\Service\Interface\JWTTokenServiceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class JWTTokenService implements JWTTokenServiceInterface
{
    private string $secretKey;
    private int $tokenLifetime;
    private int $refreshTokenLifetime;
    private FilesystemAdapter $cache;
    private string $issuer;
    private string $audience;

    public function __construct(
        string $secretKey,
        int $tokenLifetime = 3600,
        int $refreshTokenLifetime = 2592000,
        string $issuer = 'music-platform',
        string $audience = 'music-platform-client'
    ) {
        $this->secretKey = $secretKey;
        $this->tokenLifetime = $tokenLifetime;
        $this->refreshTokenLifetime = $refreshTokenLifetime;
        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->cache = new FilesystemAdapter();
    }

    public function generateToken(int $userId, array $roles = []): array
    {
        $now = time();
        $tokenId = bin2hex(random_bytes(16));
        
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->tokenLifetime,
            'jti' => $tokenId,
            'sub' => (string) $userId,
            'roles' => $roles,
            'type' => 'access'
        ];

        $accessToken = JWT::encode($payload, $this->secretKey, 'HS256');

        $refreshPayload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->refreshTokenLifetime,
            'jti' => bin2hex(random_bytes(16)),
            'sub' => (string) $userId,
            'type' => 'refresh',
            'access_jti' => $tokenId
        ];

        $refreshToken = JWT::encode($refreshPayload, $this->secretKey, 'HS256');

        return [
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->tokenLifetime,
            'token_type' => 'bearer'
        ];
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $payload = (array) $decoded;

            if ($this->isTokenRevoked($token)) {
                throw new \Exception('Token has been revoked');
            }

            return $payload;
        } catch (ExpiredException $e) {
            throw new \Exception('Token has expired');
        } catch (BeforeValidException $e) {
            throw new \Exception('Token is not valid yet');
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Invalid token signature');
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }

    public function refreshToken(string $refreshToken): array
    {
        try {
            $payload = $this->validateToken($refreshToken);
            
            if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
                throw new \Exception('Invalid refresh token');
            }

            $userId = (int) $payload['sub'];
            
            if (isset($payload['access_jti'])) {
                $this->revokeTokenByJti($payload['access_jti']);
            }

            return $this->generateToken($userId);
        } catch (\Exception $e) {
            throw new \Exception('Cannot refresh token: ' . $e->getMessage());
        }
    }

    public function revokeToken(string $token): void
    {
        try {
            $payload = $this->validateToken($token);
            $jti = $payload['jti'] ?? null;
            
            if ($jti) {
                $this->revokeTokenByJti($jti);
            }
        } catch (\Exception $e) {
            throw new \Exception('Cannot revoke token: ' . $e->getMessage());
        }
    }

    public function isTokenRevoked(string $token): bool
    {
        try {
            $payload = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $jti = $payload->jti ?? null;
            
            if (!$jti) {
                return false;
            }

            $cacheItem = $this->cache->getItem('revoked_token_' . $jti);
            return $cacheItem->isHit();
        } catch (\Exception $e) {
            return true;
        }
    }

    private function revokeTokenByJti(string $jti): void
    {
        $cacheItem = $this->cache->getItem('revoked_token_' . $jti);
        $cacheItem->set(true);
        $cacheItem->expiresAfter($this->refreshTokenLifetime);
        $this->cache->save($cacheItem);
    }
}
