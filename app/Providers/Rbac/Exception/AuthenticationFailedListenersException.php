<?php

namespace App\Providers\Rbac\Exception;

/**
 * Class AuthenticationFailedListenersException
 * @package App\Providers\Rbac\Exception
 */
class AuthenticationFailedListenersException extends AuthenticationException
{
    public function __construct(AuthenticationException ...$errors)
    {
        parent::__construct($this->getMessageKey(), 401);
    }

    public function getMessageKey()
    {
        return 'Authentication Failed.';
    }
}