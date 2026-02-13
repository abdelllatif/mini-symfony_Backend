<?php

namespace App\User\Service\Impl;

use App\User\Service\Interface\UserServiceInterface;
use App\User\Service\Interface\JWTTokenServiceInterface;
use App\User\Repository\Interface\UserRepositoryInterface;
use App\User\Mapper\UserMapperInterface;
use App\User\Mapper\OAuthMapperInterface;
use App\User\DTO\OAuthResponseDTO;
use App\User\DTO\UserDTO;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserService implements UserServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private JWTTokenServiceInterface $jwtTokenService;
    private UserMapperInterface $userMapper;
    private OAuthMapperInterface $oauthMapper;
    private HttpClientInterface $httpClient;

    public function __construct(
        UserRepositoryInterface $userRepository,
        JWTTokenServiceInterface $jwtTokenService,
        UserMapperInterface $userMapper,
        OAuthMapperInterface $oauthMapper
    ) {
        $this->userRepository = $userRepository;
        $this->jwtTokenService = $jwtTokenService;
        $this->userMapper = $userMapper;
        $this->oauthMapper = $oauthMapper;
        $this->httpClient = HttpClient::create();
    }

    public function authenticateWithGoogle(string $accessToken): OAuthResponseDTO
    {
        $userData = $this->validateGoogleToken($accessToken);
        $oauthUserData = $this->oauthMapper->mapGoogleUserData($userData);

        $user = $this->userRepository->findByOAuthProvider(
            $oauthUserData->getProvider(),
            $oauthUserData->getProviderId()
        );

        if (!$user) {
            $user = $this->userRepository->createFromOAuth($oauthUserData);
        } else {
            $user = $this->userRepository->updateFromOAuth($user, $oauthUserData);
        }

        $userDTO = $this->userMapper->entityToDTO($user);
        $tokenData = $this->jwtTokenService->generateToken($user->getId(), $user->getRoles());

        return new OAuthResponseDTO(
            $userDTO,
            $tokenData['token'],
            $tokenData['refresh_token'],
            $tokenData['expires_in']
        );
    }

    public function authenticateWithFacebook(string $accessToken): OAuthResponseDTO
    {
        $userData = $this->validateFacebookToken($accessToken);
        $oauthUserData = $this->oauthMapper->mapFacebookUserData($userData);

        $user = $this->userRepository->findByOAuthProvider(
            $oauthUserData->getProvider(),
            $oauthUserData->getProviderId()
        );

        if (!$user) {
            $user = $this->userRepository->createFromOAuth($oauthUserData);
        } else {
            $user = $this->userRepository->updateFromOAuth($user, $oauthUserData);
        }

        $userDTO = $this->userMapper->entityToDTO($user);
        $tokenData = $this->jwtTokenService->generateToken($user->getId(), $user->getRoles());

        return new OAuthResponseDTO(
            $userDTO,
            $tokenData['token'],
            $tokenData['refresh_token'],
            $tokenData['expires_in']
        );
    }

    public function getUserById(int $id): ?array
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            return null;
        }

        return $this->userMapper->entityToArray($user);
    }

    public function updateUserProfile(int $userId, array $data): array
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $this->userRepository->save($user);

        return $this->userMapper->entityToArray($user);
    }

    private function validateGoogleToken(string $accessToken): array
    {
        $response = $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v2/userinfo', [
            'auth_bearer' => $accessToken
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \InvalidArgumentException('Invalid Google access token');
        }

        return $response->toArray();
    }

    private function validateFacebookToken(string $accessToken): array
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
