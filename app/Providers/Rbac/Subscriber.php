<?php

namespace App\Providers\Rbac;

use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Exception\AuthenticationFailedListenersException;
use App\Providers\Rbac\Interfaces\HandlerInterface;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class Subscriber
 * @package App\Providers\Rbac
 *
 * @property AuthenticationListener $listener
 * @property FirewallMap $map
 */
class Subscriber implements EventSubscriberInterface
{
    private $map;
    private $listener;
    private $requestProvider;

    public function __construct(FirewallMap $map, AuthenticationListener $listener)
    {
        $this->map = $map;
        $this->listener = $listener;
    }

    public function addRequestProvider(string $name, TokenInterface $token, ProviderInterface $provider)
    {
        $this->requestProvider[$name] = [$token, $provider];
    }

    public function getRequestProvider(string $name)
    {
        return $this->requestProvider[$name];
    }


    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $errors = [];

        list($listeners, $roles, $security) = $this->map->getListeners($event->getRequest());

        # todo: handel all errors.
        foreach ($listeners as $name) {

            list($token, $provider) = $this->getRequestProvider($name);

            try {
                $this->listener->handle($event, $token, $provider);
            } catch (AuthenticationException $e) {

                $errors[] = $e;

                # todo: on error event.
            }
            # todo: on success event.

            if ($security) {
//                throw new AuthenticationFailedListenersException(...$errors);
            }
        }

        $a = 1;
    }

    public function onKernelFinishRequest(FilterResponseEvent $event)
    {
        if (null !== $token = $this->listener->getStorage()->getToken()) {
            $token->store($event->getRequest(), $event->getResponse());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => ['onKernelRequest', 8],
            KernelEvents::RESPONSE => 'onKernelFinishRequest',
        );
    }
}