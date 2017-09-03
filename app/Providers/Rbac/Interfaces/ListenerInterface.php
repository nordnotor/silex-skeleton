<?php

namespace App\Providers\Rbac\Interfaces;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

interface ListenerInterface
{
    /**
     * @param GetResponseEvent $event
     * @param TokenInterface $token
     * @param ProviderInterface $provider
     * @return
     */
    public function handle(GetResponseEvent $event, TokenInterface $token, ProviderInterface $provider);
}
