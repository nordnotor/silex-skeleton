<?php

namespace App\Providers\Rbac\Interfaces;

use App\Model;

interface ProviderInterface
{
    /**
     * @return UserInterface|Model
     */
    public function newInstance();

    /**
     * Get user By ID.
     *
     * @param $identifier
     * @return UserInterface|Model
     */
    public function retrieveById($identifier);

    /**
     * Get user by Token.
     *
     * @param string $token
     * @return UserInterface|Model
     */
    public function retrieveByToken(string $token);

    /**
     * Get User by Credentials.
     *
     * @param array $credentials
     * @return UserInterface|Model
     */
    public function retrieveByCredentials(array $credentials);
}