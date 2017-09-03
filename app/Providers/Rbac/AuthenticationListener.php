<?php

namespace App\Providers\Rbac;

use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Interfaces\AuthenticationManagerInterface;
use App\Providers\Rbac\Interfaces\GuardInterface;
use App\Providers\Rbac\Interfaces\ListenerInterface;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Interfaces\StorageInterface;
use App\Providers\Rbac\Tokens\Storage\TokenStorage;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AuthenticationListener
 * @package App\Providers\Rbac
 *
 * @property Logger|null $logger
 * @property TokenInterface $token
 * @property TokenStorage $storage
 * @property ProviderInterface $provider
 * @property AuthenticationManagerInterface $authenticationManager
 */
class AuthenticationListener implements ListenerInterface
{
    private $logger;
    private $storage;
    private $authenticationManager;

    public function __construct(TokenStorage $tokenStorage, AuthenticationManagerInterface $authenticationManager, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->storage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event, TokenInterface $token, ProviderInterface $provider)
    {
        $storageToken = $this->storage->getToken();
        if (null !== $storageToken && $storageToken->isAuthenticated()) {
            return;
        }

        if (null !== $this->logger) {
            $this->logger->info('Basic authentication Authorization header found for user.', []);
        }

        try {

            if (!$token->handle($event->getRequest())) {
                throw new AuthenticationException(sprintf('Token "%s" load credentials failed.', get_class($token)));
            }

            $this->authenticationManager->authenticate($token, $provider);
        } catch (AuthenticationException $e) {

            if (null !== $this->logger) {
                $this->logger->info('Basic authentication failed for user.', ['exception' => $e->getMessage()]);
            }
            throw $e;
        }

        if ($token->isAuthenticated()) {
            $this->storage->setToken($token);
        }
    }

    public function getStorage(): TokenStorage
    {
        return $this->storage;
    }
}