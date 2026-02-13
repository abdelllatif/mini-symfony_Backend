<?php

namespace App\Tests\Unit\User\Mapper;

use App\User\Mapper\UserMapper;
use App\User\Mapper\UserMapperInterface;
use App\User\Entity\User;
use App\User\Entity\OAuthProvider;
use App\User\DTO\UserDTO;
use PHPUnit\Framework\TestCase;

class UserMapperTest extends TestCase
{
    private UserMapper $userMapper;

    protected function setUp(): void
    {
        $this->userMapper = new UserMapper();
    }

    public function testEntityToDTOWithOAuthProvider(): void
    {
        $oauthProvider = new OAuthProvider(new User());
        $oauthProvider->setName(OAuthProvider::GOOGLE);
        $oauthProvider->setProviderId('123456789');

        $user = new User();
        $user->setId(1);
        $user->setEmail('test@example.com');
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user->setAvatar('https://example.com/avatar.jpg');
        $user->setVerified(true);
        $user->setOauthProvider($oauthProvider);
        $user->setCreatedAt(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $user->setUpdatedAt(new \DateTimeImmutable('2023-01-02 10:00:00'));

        $dto = $this->userMapper->entityToDTO($user);

        $this->assertInstanceOf(UserDTO::class, $dto);
        $this->assertEquals(1, $dto->getId());
        $this->assertEquals('test@example.com', $dto->getEmail());
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $dto->getRoles());
        $this->assertEquals('https://example.com/avatar.jpg', $dto->getAvatar());
        $this->assertTrue($dto->isVerified());
        $this->assertEquals(OAuthProvider::GOOGLE, $dto->getOauthProvider());
        $this->assertEquals(new \DateTimeImmutable('2023-01-01 10:00:00'), $dto->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2023-01-02 10:00:00'), $dto->getUpdatedAt());
    }

    public function testEntityToDTOWithoutOAuthProvider(): void
    {
        $user = new User();
        $user->setId(2);
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setAvatar(null);
        $user->setVerified(false);
        $user->setCreatedAt(new \DateTimeImmutable('2023-01-01 10:00:00'));

        $dto = $this->userMapper->entityToDTO($user);

        $this->assertInstanceOf(UserDTO::class, $dto);
        $this->assertEquals(2, $dto->getId());
        $this->assertEquals('user@example.com', $dto->getEmail());
        $this->assertEquals(['ROLE_USER'], $dto->getRoles());
        $this->assertNull($dto->getAvatar());
        $this->assertFalse($dto->isVerified());
        $this->assertNull($dto->getOauthProvider());
        $this->assertEquals(new \DateTimeImmutable('2023-01-01 10:00:00'), $dto->getCreatedAt());
        $this->assertNull($dto->getUpdatedAt());
    }

    public function testDtoToEntity(): void
    {
        $dto = new UserDTO(
            id: 3,
            email: 'dto@example.com',
            roles: ['ROLE_USER', 'ROLE_MODERATOR'],
            avatar: 'https://example.com/dto-avatar.jpg',
            isVerified: true,
            oauthProvider: OAuthProvider::FACEBOOK,
            createdAt: new \DateTimeImmutable('2023-01-01 10:00:00'),
            updatedAt: new \DateTimeImmutable('2023-01-02 10:00:00')
        );

        $entity = $this->userMapper->dtoToEntity($dto);

        $this->assertInstanceOf(User::class, $entity);
        $this->assertEquals(3, $entity->getId());
        $this->assertEquals('dto@example.com', $entity->getEmail());
        $this->assertEquals(['ROLE_USER', 'ROLE_MODERATOR'], $entity->getRoles());
        $this->assertEquals('https://example.com/dto-avatar.jpg', $entity->getAvatar());
        $this->assertTrue($entity->isVerified());
        $this->assertEquals(new \DateTimeImmutable('2023-01-01 10:00:00'), $entity->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2023-01-02 10:00:00'), $entity->getUpdatedAt());
    }

    public function testEntityToArrayWithOAuthProvider(): void
    {
        $oauthProvider = new OAuthProvider(new User());
        $oauthProvider->setName(OAuthProvider::GOOGLE);
        $oauthProvider->setProviderId('987654321');

        $user = new User();
        $user->setId(4);
        $user->setEmail('array@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setAvatar('https://example.com/array-avatar.jpg');
        $user->setVerified(true);
        $user->setOauthProvider($oauthProvider);
        $user->setCreatedAt(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $user->setUpdatedAt(new \DateTimeImmutable('2023-01-02 10:00:00'));

        $array = $this->userMapper->entityToArray($user);

        $this->assertIsArray($array);
        $this->assertEquals(4, $array['id']);
        $this->assertEquals('array@example.com', $array['email']);
        $this->assertEquals(['ROLE_USER'], $array['roles']);
        $this->assertEquals('https://example.com/array-avatar.jpg', $array['avatar']);
        $this->assertTrue($array['isVerified']);
        $this->assertEquals([
            'name' => OAuthProvider::GOOGLE,
            'providerId' => '987654321'
        ], $array['oauthProvider']);
        $this->assertEquals('2023-01-01T10:00:00+00:00', $array['createdAt']);
        $this->assertEquals('2023-01-02T10:00:00+00:00', $array['updatedAt']);
    }

    public function testEntityToArrayWithoutOAuthProvider(): void
    {
        $user = new User();
        $user->setId(5);
        $user->setEmail('noauth@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setAvatar(null);
        $user->setVerified(false);
        $user->setCreatedAt(new \DateTimeImmutable('2023-01-01 10:00:00'));

        $array = $this->userMapper->entityToArray($user);

        $this->assertIsArray($array);
        $this->assertEquals(5, $array['id']);
        $this->assertEquals('noauth@example.com', $array['email']);
        $this->assertEquals(['ROLE_USER'], $array['roles']);
        $this->assertNull($array['avatar']);
        $this->assertFalse($array['isVerified']);
        $this->assertNull($array['oauthProvider']);
        $this->assertEquals('2023-01-01T10:00:00+00:00', $array['createdAt']);
        $this->assertNull($array['updatedAt']);
    }

    public function testEntityToArrayWithNullUpdatedAt(): void
    {
        $user = new User();
        $user->setId(6);
        $user->setEmail('nullupdate@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable('2023-01-01 10:00:00'));

        $array = $this->userMapper->entityToArray($user);

        $this->assertIsArray($array);
        $this->assertEquals(6, $array['id']);
        $this->assertEquals('nullupdate@example.com', $array['email']);
        $this->assertEquals(['ROLE_USER'], $array['roles']);
        $this->assertNull($array['updatedAt']);
    }

    public function testImplementsUserMapperInterface(): void
    {
        $this->assertInstanceOf(UserMapperInterface::class, $this->userMapper);
    }
}
