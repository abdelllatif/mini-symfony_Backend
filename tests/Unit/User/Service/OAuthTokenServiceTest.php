<?php

namespace App\Tests\Unit\User\Service;

use App\User\Service\Impl\OAuthTokenService;
use App\User\Service\Interface\OAuthTokenServiceInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OAuthTokenServiceTest extends TestCase
{
    private OAuthTokenService $oauthTokenService;
    private HttpClientInterface|MockObject $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->oauthTokenService = new OAuthTokenService($this->httpClient);
    }

    public function testValidateGoogleTokenSuccess(): void
    {
        $token = 'valid-google-token';
        $googleUserData = [
            'sub' => '123456789',
            'email' => 'test@example.com',
            'picture' => 'https://example.com/avatar.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email_verified' => true
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($googleUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.googleapis.com/oauth2/v2/userinfo', [
                'auth_bearer' => $token
            ])
            ->willReturn($response);

        $result = $this->oauthTokenService->validateGoogleToken($token);

        $this->assertEquals($googleUserData, $result);
    }

    public function testValidateGoogleTokenMissingEmail(): void
    {
        $token = 'invalid-google-token';
        $googleUserData = [
            'sub' => '123456789',
            'picture' => 'https://example.com/avatar.jpg'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($googleUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Google token: missing required fields');

        $this->oauthTokenService->validateGoogleToken($token);
    }

    public function testValidateGoogleTokenMissingSub(): void
    {
        $token = 'invalid-google-token';
        $googleUserData = [
            'email' => 'test@example.com',
            'picture' => 'https://example.com/avatar.jpg'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($googleUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Google token: missing required fields');

        $this->oauthTokenService->validateGoogleToken($token);
    }

    public function testValidateGoogleTokenInvalidToken(): void
    {
        $token = 'invalid-google-token';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Google access token');

        $this->oauthTokenService->validateGoogleToken($token);
    }

    public function testValidateFacebookTokenSuccess(): void
    {
        $token = 'valid-facebook-token';
        $facebookUserData = [
            'id' => '987654321',
            'email' => 'facebook@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'picture' => [
                'data' => [
                    'url' => 'https://example.com/facebook-avatar.jpg'
                ]
            ]
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($facebookUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://graph.facebook.com/me', [
                'query' => [
                    'fields' => 'id,email,first_name,last_name,picture',
                    'access_token' => $token
                ]
            ])
            ->willReturn($response);

        $result = $this->oauthTokenService->validateFacebookToken($token);

        $this->assertEquals($facebookUserData, $result);
    }

    public function testValidateFacebookTokenMissingEmail(): void
    {
        $token = 'invalid-facebook-token';
        $facebookUserData = [
            'id' => '987654321',
            'first_name' => 'Jane'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($facebookUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Facebook token: missing required fields');

        $this->oauthTokenService->validateFacebookToken($token);
    }

    public function testValidateFacebookTokenMissingId(): void
    {
        $token = 'invalid-facebook-token';
        $facebookUserData = [
            'email' => 'facebook@example.com',
            'first_name' => 'Jane'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($facebookUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Facebook token: missing required fields');

        $this->oauthTokenService->validateFacebookToken($token);
    }

    public function testValidateFacebookTokenInvalidToken(): void
    {
        $token = 'invalid-facebook-token';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Facebook access token');

        $this->oauthTokenService->validateFacebookToken($token);
    }

    public function testGetGoogleUserInfoSuccess(): void
    {
        $token = 'valid-google-token';
        $googleUserData = [
            'sub' => '123456789',
            'email' => 'test@example.com',
            'picture' => 'https://example.com/avatar.jpg'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($googleUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.googleapis.com/oauth2/v2/userinfo', [
                'auth_bearer' => $token
            ])
            ->willReturn($response);

        $result = $this->oauthTokenService->getGoogleUserInfo($token);

        $this->assertEquals($googleUserData, $result);
    }

    public function testGetGoogleUserInfoInvalidToken(): void
    {
        $token = 'invalid-google-token';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Google access token');

        $this->oauthTokenService->getGoogleUserInfo($token);
    }

    public function testGetFacebookUserInfoSuccess(): void
    {
        $token = 'valid-facebook-token';
        $facebookUserData = [
            'id' => '987654321',
            'email' => 'facebook@example.com',
            'first_name' => 'Jane'
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($facebookUserData);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', 'https://graph.facebook.com/me', [
                'query' => [
                    'fields' => 'id,email,first_name,last_name,picture',
                    'access_token' => $token
                ]
            ])
            ->willReturn($response);

        $result = $this->oauthTokenService->getFacebookUserInfo($token);

        $this->assertEquals($facebookUserData, $result);
    }

    public function testGetFacebookUserInfoInvalidToken(): void
    {
        $token = 'invalid-facebook-token';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Facebook access token');

        $this->oauthTokenService->getFacebookUserInfo($token);
    }

    public function testImplementsOAuthTokenServiceInterface(): void
    {
        $this->assertInstanceOf(OAuthTokenTokenServiceInterface::class, $this->oauthTokenService);
    }
}
