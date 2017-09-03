<?php

namespace App\Providers\Rbac\Events;

use App\Providers\Rbac\Interfaces\GuardInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use Symfony\Component\EventDispatcher\Event;

class AuthenticationSuccessEvent extends Event
{
    const AUTHENTICATION_SUCCESS = 'security.authentication.success';

    private $token;

    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
    }

    public function getAuthenticationToken()
    {
        return $this->token;
    }
}