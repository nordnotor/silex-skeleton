<?php

namespace App\Providers\Rbac\Guards;


use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Tokens\ApiKeyToken;

/**
 * Class TokenGuard
 * @package App\Providers\Rbac\Drivers
 */
class TokenGuard extends AbstractGuard
{
    public function authenticate(TokenInterface $token, ProviderInterface $provider)
    {
        $tokenKey = $token->getCredentials();
        if (!empty($tokenKey) && $user = $provider->retrieveByToken($tokenKey)) {
            $token->setUser($user);
        }
  }

    public function supports(TokenInterface $token): bool
    {
        return $token instanceof ApiKeyToken;
    }
}

