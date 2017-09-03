<?php

namespace App\Providers\Rbac\Interfaces;

interface UserInterface extends AccessInterface
{
    /**
     * Verifies a password against a hash.
     *
     * @param string $password
     * @return bool
     */
    public function validatePassword(string $password): bool;

    /**
     * Set new user password.
     *
     * @param  string $password
     * @return mixed
     */
    public function setPassword(string $password);

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword();

    /**
     * Generates a secure auth key.
     *
     * @return mixed
     */
    public function generateAuthKey();

    /**
     * Generates password reset token.
     *
     * @return mixed
     */
    public function generatePasswordResetToken();

//    /**
//     * Find user by credentials.
//     *
//     * @param string $token
//     * @return mixed
//     */
//    public function findByCredentials(string $token);

    /**
     * Get Auth key.
     * @return string
     */
    public function getAuthKey(): string;


    /**
     * @return string|null
     */
    public function getKey();

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials();
}