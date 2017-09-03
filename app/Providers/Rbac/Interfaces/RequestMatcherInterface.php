<?php

namespace App\Providers\Rbac\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface RequestMatcherInterface
{
    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
     *
     * @param Request $request The request to check for a match
     *
     * @return bool true if the request matches, false otherwise
     */
    public function matches(Request $request);
}