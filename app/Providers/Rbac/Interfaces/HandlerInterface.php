<?php

namespace App\Providers\Rbac\Interfaces;

use App\Providers\Rbac\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

interface HandlerInterface
{
    public function onSuccess(TokenInterface $token, GetResponseEvent $event);

    public function onError(AuthenticationException $e, TokenInterface $token, GetResponseEvent $event);
}