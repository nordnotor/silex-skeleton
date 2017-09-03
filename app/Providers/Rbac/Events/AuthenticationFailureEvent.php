<?php

namespace App\Providers\Rbac\Events;

use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Interfaces\GuardInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;

class AuthenticationFailureEvent extends AuthenticationSuccessEvent
{
    const AUTHENTICATION_FAILURE = 'security.authentication.failure';

    private $authenticationException;

    public function __construct(TokenInterface $token, AuthenticationException $ex)
    {
        parent::__construct($token);

        $this->authenticationException = $ex;
    }

    public function getAuthenticationException()
    {
        return $this->authenticationException;
    }
}