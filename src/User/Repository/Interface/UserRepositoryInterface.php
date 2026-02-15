<?php

namespace App\User\Repository\Interface;

use App\User\Entity\User;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;

    public function findOneBy(array $criteria): ?User;

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array;

    public function findAll(): array;

    public function save(User $user): void;

    public function remove(User $user): void;

    public function findByEmail(string $email): ?User;

    public function findByOAuthProvider(string $provider, string $providerId): ?User;

    public function create(User $user): User;
}
