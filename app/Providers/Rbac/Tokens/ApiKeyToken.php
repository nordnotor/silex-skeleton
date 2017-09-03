<?php

namespace App\Providers\Rbac\Tokens;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyToken extends AbstractToken
{
    private $inputKey;
    private $credentials;

    public function __construct(array $config)
    {
        $this->inputKey = $config['input_key'];
    }

    public function handle(Request $request): bool
    {
        if ($key = $request->headers->get($this->inputKey, $request->get($this->inputKey))) {

            $this->credentials = $key;

            return true;
        }
        return false;
    }

    public function store(Request $request, Response $response): bool
    {
        $user = $this->getUser();
        if ($user && $user->isDirty()) {
            return $user->save();
        }
        return false;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}