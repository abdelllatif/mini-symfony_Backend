<?php

namespace App\User\Mapper;

use App\User\DTO\OAuthUserDataDTO;

interface OAuthMapperInterface
{
    public function mapGoogleUserData(array $userData): OAuthUserDataDTO;
    
    public function mapFacebookUserData(array $userData): OAuthUserDataDTO;
}
