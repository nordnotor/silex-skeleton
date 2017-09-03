<?php

namespace App\Providers\Rbac\Exception;

class AuthenticationTokenException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Failed load credentials for token.';
    }
}