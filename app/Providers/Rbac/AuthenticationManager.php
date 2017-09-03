<?php

namespace App\Providers\Rbac;


use App\Providers\Rbac\Interfaces\AuthenticationManagerInterface;
use App\Providers\Rbac\Interfaces\GuardInterface;
use App\Providers\Rbac\Interfaces\ListenerInterface;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use App\Providers\Rbac\Interfaces\StorageInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Interfaces\UserInterface;

use App\Providers\Rbac\Events\AuthenticationFailureEvent;
use App\Providers\Rbac\Events\AuthenticationSuccessEvent;

use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Exception\ProviderNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthenticationManager
 * @package App\Providers\Rbac
 *
 * @property bool $eraseCredentials
 * @property ProviderInterface $provider
 * @property array|GuardInterface[] $guards
 * @property array|ListenerInterface[] $listeners
 * @property FirewallMap $map
 * @property EventDispatcherInterface $eventDispatcher
 */
class AuthenticationManager implements AuthenticationManagerInterface
{
    private $guards;
    private $eventDispatcher;
    private $eraseCredentials = true;

    public function setGuard(GuardInterface $guard)
    {
        $this->guards[] = $guard;
    }

    public function getGuards()
    {
        return $this->guards;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function authenticate(TokenInterface $token, ProviderInterface $provider)
    {
        $lastException = null;
        $result = null;
        $guard = null;

        foreach ($this->guards as $guard) {

            if (!$guard instanceof GuardInterface) {
                throw new \InvalidArgumentException(sprintf('Guard "%s" must implement the GuardInterface or must register.', get_class($guard)));
            }

            try {
                $guard->authenticate($token, $provider);

                if (!$token->isAuthenticated()) {
                    throw new AuthenticationException(sprintf('Guard "%s" authenticator failed.', get_class($guard)));
                }

                break;
            } catch (AuthenticationException $e) {
                $lastException = $e;
            }
        }

        $user = $token->getUser();
        if ($user instanceof UserInterface) {

            if (true === $this->eraseCredentials) {
                $user->eraseCredentials();
            }

            if (null !== $this->eventDispatcher) {
                $this->eventDispatcher->dispatch(AuthenticationSuccessEvent::AUTHENTICATION_SUCCESS, new AuthenticationSuccessEvent($token));
            }
            return;
        }

        if (null === $lastException) {
            $lastException = new ProviderNotFoundException(sprintf('No Authentication Guard found for token of class "%s".', get_class($token)));
        }

        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(AuthenticationFailureEvent::AUTHENTICATION_FAILURE, new AuthenticationFailureEvent($token, $lastException));
        }

        $lastException->setToken($token);

        throw $lastException;
    }
}