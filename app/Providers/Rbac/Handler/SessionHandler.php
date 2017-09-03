<?php

namespace App\Providers\Rbac\Handler;

use App\Providers\Rbac\Interfaces\HandlerInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use App\Providers\Rbac\Exception\AuthenticationException;

class SessionHandler implements HandlerInterface
{
    public function onSuccess(TokenInterface $token, GetResponseEvent $event)
    {

    }

    public function onError(AuthenticationException $e, TokenInterface $token, GetResponseEvent $event)
    {

    }
}