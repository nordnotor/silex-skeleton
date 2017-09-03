<?php

namespace App\Providers\Rbac\Interfaces;

interface AccessInterface
{
    /**
     * Get Role.
     * @return string
     */
    public function getRoles();

    /**
     * Check is Role.
     * @param string|array $roles
     * @return bool
     */
    public function isRole(array $roles): bool;

//    /**
//     * Check if user has a permission by its name.
//     *
//     * @param string|array $name
//     * @param null|ModelInterface $model
//     * @param array $data
//     * @return bool
//     */
//    public function can($name, $model = null, array $data = []): bool;
}