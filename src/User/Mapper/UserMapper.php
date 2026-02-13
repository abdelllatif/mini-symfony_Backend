<?php

namespace App\User\Mapper;

use App\User\Entity\User;
use App\User\DTO\UserDTO;

class UserMapper implements UserMapperInterface
{
    public function entityToDTO(User $user): UserDTO
    {
        $oauthProvider = null;
        if ($user->getOauthProvider()) {
            $oauthProvider = $user->getOauthProvider()->getName();
        }

        return new UserDTO(
            $user->getId(),
            $user->getEmail(),
            $user->getRoles(),
            $user->getAvatar(),
            $user->isVerified(),
            $oauthProvider,
            $user->getCreatedAt(),
            $user->getUpdatedAt()
        );
    }

    public function dtoToEntity(UserDTO $dto): User
    {
        $user = new User();
        $user->setId($dto->getId());
        $user->setEmail($dto->getEmail());
        $user->setRoles($dto->getRoles());
        $user->setAvatar($dto->getAvatar());
        $user->setVerified($dto->isVerified());
        $user->setCreatedAt($dto->getCreatedAt());
        $user->setUpdatedAt($dto->getUpdatedAt());

        return $user;
    }

    public function entityToArray(User $user): array
    {
        $oauthProvider = null;
        if ($user->getOauthProvider()) {
            $oauthProvider = [
                'name' => $user->getOauthProvider()->getName(),
                'providerId' => $user->getOauthProvider()->getProviderId()
            ];
        }

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'avatar' => $user->getAvatar(),
            'isVerified' => $user->isVerified(),
            'oauthProvider' => $oauthProvider,
            'createdAt' => $user->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $user->getUpdatedAt()?->format(\DateTime::ATOM),
        ];
    }
}
