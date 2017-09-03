<?php

namespace App\Providers\Rbac\Tokens;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtToken extends AbstractToken
{

    public function load(Request $request)
    {
        // TODO: Implement load() method.
    }

    public function store(Request $request, Response $response): bool
    {
        $user = $this->getUser();
        if ($user && $user->isDirty()) {
            return $user->save();
        }
        return false;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request): bool
    {
        // TODO: Implement handle() method.
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        // TODO: Implement getCredentials() method.
    }
}