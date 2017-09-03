<?php

namespace App\Providers\Rbac\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface GuardInterface
{
    /**
     * Load User by Credentials.
     *
     * @param TokenInterface $token
     * @param ProviderInterface $provider
     * @return mixed|void
     */
    public function authenticate(TokenInterface $token, ProviderInterface $provider);
}