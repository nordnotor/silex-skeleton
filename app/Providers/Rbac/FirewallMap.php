<?php

namespace App\Providers\Rbac;

use App\Providers\Rbac\Interfaces\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class FirewallMap
{
    private $map = [];

    /**
     * @param RequestMatcherInterface $requestMatcher
     * @param string[] $listeners
     * @param string[] $roles
     */
    public function add(RequestMatcherInterface $requestMatcher = null, array $listeners = [], array $roles, $security)
    {
        $this->map[] = [$requestMatcher, $listeners, $roles, $security];
    }

    /**
     * @param Request $request
     * @return array [$listeners, $roles, $security]
     */
    public function getListeners(Request $request): array
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->matches($request)) {
                return [$elements[1], $elements[2], $elements[3]];
            }
        }
        return [[], [], false];
    }
}