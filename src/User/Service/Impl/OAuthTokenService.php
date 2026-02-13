<?php

namespace App\User\Service\Impl;

use App\User\Service\Interface\OAuthTokenServiceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OAuthTokenService implements OAuthTokenServiceInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function validateGoogleToken(string $token): array
    {
        $userInfo = $this->getGoogleUserInfo($token);
        
        if (!isset($userInfo['email']) || !isset($userInfo['sub'])) {
            throw new \InvalidArgumentException('Invalid Google token: missing required fields');
        }

        return $userInfo;
    }

    public function validateFacebookToken(string $token): array
    {
        $userInfo = $this->getFacebookUserInfo($token);
        
        if (!isset($userInfo['email']) || !isset($userInfo['id'])) {
            throw new \InvalidArgumentException('Invalid Facebook token: missing required fields');
        }

        return $userInfo;
    }

    public function getGoogleUserInfo(string $accessToken): array
    {
        $response = $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v2/userinfo', [
            'auth_bearer' => $accessToken
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \InvalidArgumentException('Invalid Google access token');
        }

        return $response->toArray();
    }

    public function getFacebookUserInfo(string $accessToken): array
    {
        $response = $this->httpClient->request('GET', 'https://graph.facebook.com/me', [
            'query' => [
                'fields' => 'id,email,first_name,last_name,picture',
                'access_token' => $accessToken
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \InvalidArgumentException('Invalid Facebook access token');
        }

        return $response->toArray();
    }
}
