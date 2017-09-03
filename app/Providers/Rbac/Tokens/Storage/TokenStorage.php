<?php

namespace App\Providers\Rbac\Tokens\Storage;

use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Interfaces\TokenStorageInterface;
use Pimple\Container;

class TokenStorage implements TokenStorageInterface
{
    private $token;

    /**
     * @return TokenInterface|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param TokenInterface|null $token
     */
    public function setToken(TokenInterface $token = null)
    {
        $this->token = $token;
    }
}