<?php

namespace App\Providers\Rbac\Guards;

use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Tokens\SessionToken;

/**
 * Class SessionGuard
 * @package App\Providers\Rbac\Drivers
 */
class SessionGuard extends AbstractGuard
{
    public function authenticate(TokenInterface $token, ProviderInterface $provider)
    {
        $attributes = $token->getAttributes();

        if (!empty($attributes)) {

            $user = $provider->newInstance()->setRawAttributes($attributes);

            $token->setUser($user);

            return;
        }

        $id = $token->getCredentials();

        if (!empty($id) && $user = $provider->retrieveById($id)) {
            $token->setUser($user);
            return;
        }
        throw new AuthenticationException('Session authenticator failed to return an authenticated token.');
    }

    public function supports(TokenInterface $token): bool
    {
        return $token instanceof SessionToken;
    }
}
