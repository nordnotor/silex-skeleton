<?php

namespace App\Providers\Rbac\Interfaces;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface AuthenticationManagerInterface
 * @package App\Providers\Rbac\Interfaces
 */
interface AuthenticationManagerInterface
{
    /**
     * @param TokenInterface $token
     * @param ProviderInterface $provider
     * @return
     */
    public function authenticate(TokenInterface $token, ProviderInterface $provider);

    /**
     * @param EventDispatcherInterface $dispatcher
     * @return mixed
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher);
}