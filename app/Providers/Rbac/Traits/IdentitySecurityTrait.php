<?php

namespace App\Providers\Rbac\Traits;

/**
 * Class IdentitySecurityTrait
 * @package App\Providers\Rbac\Traits
 *
 * @property string $token
 * @property string $password_hash
 * @property string $password_reset_token
 */
trait IdentitySecurityTrait
{
    /**
     * Check is Role.
     *
     * @param array $roles
     * @return bool
     */
    public function isRole(array $roles): bool
    {
        return in_array($this->getRoles(), $roles, false);
    }

    /**
     * Generate auth key.
     *
     * @return void
     */
    public function generateAuthKey()
    {
        $this->token = $this->generateRandomString();
    }

    /**
     * Get auth key.
     *
     * @return string
     */
    public function getAuthKey(): string
    {
        return $this->token;
    }

    /**
     * Generate str_time for verification.
     *
     * @return void
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = $this->generateRandomString() . '_' . time();
    }

    /**
     * Verifies a password against a hash.
     *
     * @param string $password The password to verify.
     * @return boolean whether the password is correct.
     * @throws \Exception
     */
    public function validatePassword(string $password): bool
    {
        $hash = $this->getPassword();

        if (!is_string($password) || $password === '') {
            throw new \Exception('Password must be a string and cannot be empty.');
        }

        if (!is_string($hash) || $hash === '') {
            throw new \Exception('Hash is invalid.');
        }

        return password_verify(trim($password), $hash);
    }

    /**
     * Generates a secure hash password.
     *
     * @param string $password The password to be hashed.
     * @param integer $cost Cost parameter.
     * @return void
     */
    public function setPassword(string $password, $cost = 10)
    {
        $this->password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
    }

    /**
     * Get password hash.
     *
     * @return string
     */
    public function getPassword():string
    {
        return $this->password_hash;
    }

    /**
     * Generate str_time for verification.
     *
     * @return string
     */
    private function generateRandomString(): string
    {
        return uniqid('', true);  # TODO: make safer.
    }

    /**
     * Verification Password Reset Token
     *
     * @param string $token
     * @return bool
     */
    public function isPasswordResetTokenValid(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $expire = 86400; # 1 day.
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);

        return $timestamp + $expire >= time();
    }
}