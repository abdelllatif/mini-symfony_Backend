<?php

namespace App\Tests\Unit\User\Service;

use App\User\Service\Impl\UserService;
use App\User\Service\Interface\UserServiceInterface;
use App\User\Service\Interface\JWTTokenServiceInterface;
use App\User\Repository\Interface\UserRepositoryInterface;
use App\User\Mapper\UserMapperInterface;
use App\User\Mapper\OAuthMapperInterface;
use App\User\DTO\OAuthResponseDTO;
use App\User\DTO\UserDTO;
use App\User\DTO\OAuthUserDataDTO;
use App\User\Entity\User;
use App\User\Entity\OAuthProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepositoryInterface|MockObject $userRepository;
    private JWTTokenServiceInterface|MockObject $jwtTokenService;
    private UserMapperInterface|MockObject $userMapper;
    private OAuthMapperInterface|MockObject $oauthMapper;
    private HttpClientInterface|MockObject $httpClient;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->jwtTokenService = $this->createMock(JWTTokenServiceInterface::class);
        $this->userMapper = $this->createMock(UserMapperInterface::class);
        $this->oauthMapper = $this->createMock(OAuthMapperInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->jwtTokenService,
            $this->userMapper,
            $this->oauthMapper
        );
    }

    public function testAuthenticateWithGoogleCreatesNewUser(): void
    {
        $accessToken = 'google-access-token';
        $googleUserData = [
            'id' => '123456789',
            'email' => 'test@example.com',
            'picture' => 'https://example.com/avatar.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email_verified' => true
        ];

        $oauthUserData = new OAuthUserDataDTO(
            provider: 'google',
            providerId: '123456789',
            email: 'test@example.com',
            avatar: 'https://example.com/avatar.jpg',
            firstName: 'John',
            lastName: 'Doe',
            isVerified: true
        );

        $user = new User();
        $user->setId(1);
        $user->setEmail('test@example.com');

        $userDTO = new UserDTO(
            id: 1,
            email: 'test@example.com',
            roles: ['ROLE_USER'],
            avatar: 'https://example.com/avatar.jpg',
            isVerified: true,
            oauthProvider: 'google',
            createdAt: new \DateTimeImmutable()
        );

        $tokenData = [
            'token' => 'jwt-access-token',
            'refresh_token' => 'jwt-refresh-token',
            'expires_in' => 3600
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($googleUserData);

        $this->httpClient->method('request')->willReturn($response);

        $this->oauthMapper->method('mapGoogleUserData')
            ->with($googleUserData)
            ->willReturn($oauthUserData);

        $this->userRepository->method('findByOAuthProvider')
            ->with('google', '123456789')
            ->willReturn(null);

        $this->userRepository->method('createFromOAuth')
            ->with($oauthUserData)
            ->willReturn($user);

        $this->userMapper->method('entityToDTO')
            ->with($user)
            ->willReturn($userDTO);

        $this->jwtTokenService->method('generateToken')
            ->with(1, ['ROLE_USER'])
            ->willReturn($tokenData);

        $result = $this->userService->authenticateWithGoogle($accessToken);

        $this->assertInstanceOf(OAuthResponseDTO::class, $result);
        $this->assertEquals($userDTO, $result->getUser());
        $this->assertEquals('jwt-access-token', $result->getToken());
        $this->assertEquals('jwt-refresh-token', $result->getRefreshToken());
        $this->assertEquals(3600, $result->getExpiresIn());
    }

    public function testAuthenticateWithGoogleUpdatesExistingUser(): void
    {
        $accessToken = 'google-access-token';
        $googleUserData = [
            'id' => '123456789',
            'email' => 'test@example.com',
            'picture' => 'https://example.com/new-avatar.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email_verified' => true
        ];

        $oauthUserData = new OAuthUserDataDTO(
            provider: 'google',
            providerId: '123456789',
            email: 'test@example.com',
            avatar: 'https://example.com/new-avatar.jpg',
            firstName: 'John',
            lastName: 'Doe',
            isVerified: true
        );

        $user = new User();
        $user->setId(1);
        $user->setEmail('test@example.com');

        $userDTO = new UserDTO(
            id: 1,
            email: 'test@example.com',
            roles: ['ROLE_USER'],
            avatar: 'https://example.com/new-avatar.jpg',
            isVerified: true,
            oauthProvider: 'google',
            createdAt: new \DateTimeImmutable()
        );

        $tokenData = [
            'token' => 'jwt-access-token',
            'refresh_token' => 'jwt-refresh-token',
            'expires_in' => 3600
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($googleUserData);

        $this->httpClient->method('request')->willReturn($response);

        $this->oauthMapper->method('mapGoogleUserData')
            ->with($googleUserData)
            ->willReturn($oauthUserData);

        $this->userRepository->method('findByOAuthProvider')
            ->with('google', '123456789')
            ->willReturn($user);

        $this->userRepository->method('updateFromOAuth')
            ->with($user, $oauthUserData)
            ->willReturn($user);

        $this->userMapper->method('entityToDTO')
            ->with($user)
            ->willReturn($userDTO);

        $this->jwtTokenService->method('generateToken')
            ->with(1, ['ROLE_USER'])
            ->willReturn($tokenData);

        $result = $this->userService->authenticateWithGoogle($accessToken);

        $this->assertInstanceOf(OAuthResponseDTO::class, $result);
        $this->assertEquals($userDTO, $result->getUser());
    }

    public function testAuthenticateWithFacebookCreatesNewUser(): void
    {
        $accessToken = 'facebook-access-token';
        $facebookUserData = [
            'id' => '987654321',
            'email' => 'test@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'picture' => [
                'data' => [
                    'url' => 'https://example.com/facebook-avatar.jpg'
                ]
            ]
        ];

        $oauthUserData = new OAuthUserDataDTO(
            provider: 'facebook',
            providerId: '987654321',
            email: 'test@example.com',
            avatar: 'https://example.com/facebook-avatar.jpg',
            firstName: 'Jane',
            lastName: 'Smith',
            isVerified: true
        );

        $user = new User();
        $user->setId(2);
        $user->setEmail('test@example.com');

        $userDTO = new UserDTO(
            id: 2,
            email: 'test@example.com',
            roles: ['ROLE_USER'],
            avatar: 'https://example.com/facebook-avatar.jpg',
            isVerified: true,
            oauthProvider: 'facebook',
            createdAt: new \DateTimeImmutable()
        );

        $tokenData = [
            'token' => 'jwt-access-token',
            'refresh_token' => 'jwt-refresh-token',
            'expires_in' => 3600
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($facebookUserData);

        $this->httpClient->method('request')->willReturn($response);

        $this->oauthMapper->method('mapFacebookUserData')
            ->with($facebookUserData)
            ->willReturn($oauthUserData);

        $this->userRepository->method('findByOAuthProvider')
            ->with('facebook', '987654321')
            ->willReturn(null);

        $this->userRepository->method('createFromOAuth')
            ->with($oauthUserData)
            ->willReturn($user);

        $this->userMapper->method('entityToDTO')
            ->with($user)
            ->willReturn($userDTO);

        $this->jwtTokenService->method('generateToken')
            ->with(2, ['ROLE_USER'])
            ->willReturn($tokenData);

        $result = $this->userService->authenticateWithFacebook($accessToken);

        $this->assertInstanceOf(OAuthResponseDTO::class, $result);
        $this->assertEquals($userDTO, $result->getUser());
        $this->assertEquals('jwt-access-token', $result->getToken());
    }

    public function testGetUserByIdReturnsNullWhenUserNotFound(): void
    {
        $this->userRepository->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->userService->getUserById(999);

        $this->assertNull($result);
    }

    public function testGetUserByIdReturnsUserDataWhenUserExists(): void
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail('test@example.com');

        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'roles' => ['ROLE_USER'],
            'avatar' => null,
            'isVerified' => false,
            'oauthProvider' => null,
            'createdAt' => (new \DateTimeImmutable())->format(\DateTime::ATOM),
            'updatedAt' => null
        ];

        $this->userRepository->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->userMapper->method('entityToArray')
            ->with($user)
            ->willReturn($userData);

        $result = $this->userService->getUserById(1);

        $this->assertEquals($userData, $result);
    }

    public function testUpdateUserProfile(): void
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setAvatar('old-avatar.jpg');

        $updatedData = [
            'avatar' => 'new-avatar.jpg',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN']
        ];

        $updatedUserData = [
            'id' => 1,
            'email' => 'test@example.com',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
            'avatar' => 'new-avatar.jpg',
            'isVerified' => false,
            'oauthProvider' => null,
            'createdAt' => (new \DateTimeImmutable())->format(\DateTime::ATOM),
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTime::ATOM)
        ];

        $this->userRepository->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userMapper->method('entityToArray')
            ->with($user)
            ->willReturn($updatedUserData);

        $result = $this->userService->updateUserProfile(1, $updatedData);

        $this->assertEquals($updatedUserData, $result);
        $this->assertEquals('new-avatar.jpg', $user->getAvatar());
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $user->getRoles());
    }

    public function testUpdateUserProfileThrowsExceptionWhenUserNotFound(): void
    {
        $this->userRepository->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->userService->updateUserProfile(999, ['avatar' => 'new-avatar.jpg']);
    }
}
