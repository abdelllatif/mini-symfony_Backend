<?php

namespace App\User\Mapper;

use App\User\Entity\User;
use App\User\DTO\UserDTO;

interface UserMapperInterface
{
    public function entityToDTO(User $user): UserDTO;
    
    public function dtoToEntity(UserDTO $dto): User;
    
    public function entityToArray(User $user): array;
}
