<?php

namespace App\Providers\Rbac;

use App\Providers\Rbac\Exception\AuthenticationException;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Tokens\Storage\TokenStorage;
use Pimple\Container;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Application;
use App\Interfaces\ServiceProviderInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PredisServiceProvider
 * @package e1\providers\Predis
 *
 * @property Application $app
 * @property Client $client
 *
 *
 * @REQUIRE
 *  -
 *
 * @REGISTER:
 *  $app->register(new \App\Providers\Rbac\SecurityServiceProvider(), [
 *      'security.drivers' => [
 *          \App\Providers\Rbac\Drivers\TokenGuard::class => [],
 *          \App\Providers\Rbac\Drivers\JWTGuard::class => [2, '#qsd!$b[toi~i1390-dsafyj'],
 *          \App\Providers\Rbac\Drivers\SessionGuard::class => ['#qsd!$b[toi~i1390-dsafyj', false],
 *      ],
 * ]);
 *
 * @USAGE:
 *
 */
class SecurityServiceProvider implements ServiceProviderInterface, BootableProviderInterface, EventListenerProviderInterface
{
    protected $app;

    public function boot(Application $app)
    {

    }

    public function register(Container $app)
    {
        $this->app = $app;
        $app['security'] = $this;

        $app['security.storage'] = function () use ($app) {
            return new TokenStorage();
        };

        $app['security.user'] = function () use ($app) {
            /** @var TokenInterface $token */
            if (null !== $token = $app['security.storage']->getToken()) {
                return $token->getUser();
            }
            return null;
        };

        $app['security.manager'] = function () use ($app) {

            $guards = $app['security.guards'] ?? [];

            $manager = new AuthenticationManager();

            foreach ($guards as $class) {
                $manager->setGuard(new $class($app));
            }

            return $manager;
        };

        $app['security.map'] = function () use ($app) {

            $rules = $app['security.rules'] ?? [];

            $map = new FirewallMap();

            foreach ($rules as $rule) {
                $map->add(new RequestMatcher(
                    $rule['path'] ?? null,
                    $rule['host'] ?? null,
                    $rule['methods'] ?? null,
                    $rule['ips'] ?? null,
                    $rule['attributes'] ?? [],
                    $rule['schemes'] ?? null
                ),
                    (array)$rule['listeners'],
                    (array)$rule['roles'],
                    (bool)$rule['security']
                );
            }
            return $map;
        };

        $app['security.listener'] = function () use ($app) {

            return new AuthenticationListener(
                $app['security.storage'],
                $app['security.manager'],
                $app['security.logger']
            );
        };

        $app['security.subscriber'] = function () use ($app) {

            $listeners = $app['security.listeners'] ?? [];

            $subscriber = new Subscriber($app['security.map'], $app['security.listener']);

            foreach ($listeners as $name => $listener) {

                $subscriber->addRequestProvider(
                    $name,
                    new $listener['token']['class']($listener['token']['parameters']),
                    new $listener['provider']($app)
                );
            }

            return $subscriber;
        };
    }

    public function getConfigTreeBuilder(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('security');

        $node->children()
            ->scalarNode('logger')->end()
            ->arrayNode('rules')->prototype('array')->children()
            ->scalarNode('path')->isRequired()->end()
            ->scalarNode('host')->end()
            ->scalarNode('methods')->end()
            ->scalarNode('ips')->end()
            ->scalarNode('attributes')->end()
            ->scalarNode('schemes')->end()
            ->arrayNode('listeners')->prototype('scalar')->end()->end()
            ->arrayNode('roles')->prototype('scalar')->end()->end()
            ->booleanNode('security')->defaultFalse()->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('guards')->requiresAtLeastOneElement()->prototype('scalar')
            ->end()
            ->end()
            ->arrayNode('listeners')->requiresAtLeastOneElement()->isRequired()
            ->useAttributeAsKey('name')->prototype('array')->children()
            ->scalarNode('class')->defaultValue('\App\Providers\Rbac\AuthenticationListener')->end()
            ->arrayNode('token')->children()
            ->scalarNode('class')->end()
            ->arrayNode('parameters')->prototype('scalar')->end()->end()
            ->end()
            ->end()
            ->scalarNode('provider')->defaultValue('\App\Providers\Rbac\Providers\EloquentProvider')->end()
            ->end()
            ->end()
            ->end();

        return $node;
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['security.subscriber']);
    }
}