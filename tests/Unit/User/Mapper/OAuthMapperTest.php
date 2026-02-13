<?php

namespace App\Tests\Unit\User\Mapper;

use App\User\Mapper\OAuthMapper;
use App\User\Mapper\OAuthMapperInterface;
use App\User\DTO\OAuthUserDataDTO;
use PHPUnit\Framework\TestCase;

class OAuthMapperTest extends TestCase
{
    private OAuthMapper $oauthMapper;

    protected function setUp(): void
    {
        $this->oauthMapper = new OAuthMapper();
    }

    public function testMapGoogleUserDataComplete(): void
    {
        $googleUserData = [
            'sub' => '123456789',
            'email' => 'test@example.com',
            'picture' => 'https://example.com/avatar.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email_verified' => true
        ];

        $dto = $this->oauthMapper->mapGoogleUserData($googleUserData);

        $this->assertInstanceOf(OAuthUserDataDTO::class, $dto);
        $this->assertEquals('google', $dto->getProvider());
        $this->assertEquals('123456789', $dto->getProviderId());
        $this->assertEquals('test@example.com', $dto->getEmail());
        $this->assertEquals('https://example.com/avatar.jpg', $dto->getAvatar());
        $this->assertEquals('John', $dto->getFirstName());
        $this->assertEquals('Doe', $dto->getLastName());
        $this->assertTrue($dto->isVerified());
    }

    public function testMapGoogleUserDataMinimal(): void
    {
        $googleUserData = [
            'sub' => '987654321',
            'email' => 'minimal@example.com',
            'email_verified' => false
        ];

        $dto = $this->oauthMapper->mapGoogleUserData($googleUserData);

        $this->assertInstanceOf(OAuthUserDataDTO::class, $dto);
        $this->assertEquals('google', $dto->getProvider());
        $this->assertEquals('987654321', $dto->getProviderId());
        $this->assertEquals('minimal@example.com', $dto->getEmail());
        $this->assertNull($dto->getAvatar());
        $this->assertNull($dto->getFirstName());
        $this->assertNull($dto->getLastName());
        $this->assertFalse($dto->isVerified());
    }

    public function testMapGoogleUserDataMissingEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email is required from Google OAuth data');

        $googleUserData = [
            'sub' => '123456789',
            'picture' => 'https://example.com/avatar.jpg'
        ];

        $this->oauthMapper->mapGoogleUserData($googleUserData);
    }

    public function testMapGoogleUserDataMissingSubject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject ID is required from Google OAuth data');

        $googleUserData = [
            'email' => 'test@example.com',
            'picture' => 'https://example.com/avatar.jpg'
        ];

        $this->oauthMapper->mapGoogleUserData($googleUserData);
    }

    public function testMapFacebookUserDataComplete(): void
    {
        $facebookUserData = [
            'id' => '456789123',
            'email' => 'facebook@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'picture' => [
                'data' => [
                    'url' => 'https://example.com/facebook-avatar.jpg'
                ]
            ]
        ];

        $dto = $this->oauthMapper->mapFacebookUserData($facebookUserData);

        $this->assertInstanceOf(OAuthUserDataDTO::class, $dto);
        $this->assertEquals('facebook', $dto->getProvider());
        $this->assertEquals('456789123', $dto->getProviderId());
        $this->assertEquals('facebook@example.com', $dto->getEmail());
        $this->assertEquals('https://example.com/facebook-avatar.jpg', $dto->getAvatar());
        $this->assertEquals('Jane', $dto->getFirstName());
        $this->assertEquals('Smith', $dto->getLastName());
        $this->assertTrue($dto->isVerified());
    }

    public function testMapFacebookUserDataMinimal(): void
    {
        $facebookUserData = [
            'id' => '789123456',
            'email' => 'fb-minimal@example.com'
        ];

        $dto = $this->oauthMapper->mapFacebookUserData($facebookUserData);

        $this->assertInstanceOf(OAuthUserDataDTO::class, $dto);
        $this->assertEquals('facebook', $dto->getProvider());
        $this->assertEquals('789123456', $dto->getProviderId());
        $this->assertEquals('fb-minimal@example.com', $dto->getEmail());
        $this->assertNull($dto->getAvatar());
        $this->assertNull($dto->getFirstName());
        $this->assertNull($dto->getLastName());
        $this->assertTrue($dto->isVerified());
    }

    public function testMapFacebookUserDataWithNestedPicture(): void
    {
        $facebookUserData = [
            'id' => '111222333',
            'email' => 'nested@example.com',
            'picture' => [
                'data' => [
                    'url' => 'https://example.com/nested-avatar.jpg',
                    'width' => 200,
                    'height' => 200
                ]
            ]
        ];

        $dto = $this->oauthMapper->mapFacebookUserData($facebookUserData);

        $this->assertEquals('https://example.com/nested-avatar.jpg', $dto->getAvatar());
    }

    public function testMapFacebookUserDataMissingEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email is required from Facebook OAuth data');

        $facebookUserData = [
            'id' => '456789123',
            'first_name' => 'Jane'
        ];

        $this->oauthMapper->mapFacebookUserData($facebookUserData);
    }

    public function testMapFacebookUserDataMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID is required from Facebook OAuth data');

        $facebookUserData = [
            'email' => 'facebook@example.com',
            'first_name' => 'Jane'
        ];

        $this->oauthMapper->mapFacebookUserData($facebookUserData);
    }

    public function testMapFacebookUserDataWithEmptyPictureData(): void
    {
        $facebookUserData = [
            'id' => '444555666',
            'email' => 'empty-picture@example.com',
            'picture' => [
                'data' => []
            ]
        ];

        $dto = $this->oauthMapper->mapFacebookUserData($facebookUserData);

        $this->assertNull($dto->getAvatar());
    }

    public function testMapFacebookUserDataWithNullPicture(): void
    {
        $facebookUserData = [
            'id' => '777888999',
            'email' => 'null-picture@example.com',
            'picture' => null
        ];

        $dto = $this->oauthMapper->mapFacebookUserData($facebookUserData);

        $this->assertNull($dto->getAvatar());
    }

    public function testGetFullName(): void
    {
        $dto = new OAuthUserDataDTO(
            provider: 'google',
            providerId: '123456789',
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->assertEquals('John Doe', $dto->getFullName());
    }

    public function testGetFullNameWithOnlyFirstName(): void
    {
        $dto = new OAuthUserDataDTO(
            provider: 'google',
            providerId: '123456789',
            email: 'test@example.com',
            firstName: 'John',
            lastName: null
        );

        $this->assertEquals('John', $dto->getFullName());
    }

    public function testGetFullNameWithOnlyLastName(): void
    {
        $dto = new OAuthUserDataDTO(
            provider: 'google',
            providerId: '123456789',
            email: 'test@example.com',
            firstName: null,
            lastName: 'Doe'
        );

        $this->assertEquals('Doe', $dto->getFullName());
    }

    public function testGetFullNameWithNullNames(): void
    {
        $dto = new OAuthUserDataDTO(
            provider: 'google',
            providerId: '123456789',
            email: 'test@example.com',
            firstName: null,
            lastName: null
        );

        $this->assertNull($dto->getFullName());
    }

    public function testImplementsOAuthMapperInterface(): void
    {
        $this->assertInstanceOf(OAuthMapperInterface::class, $this->oauthMapper);
    }
}
