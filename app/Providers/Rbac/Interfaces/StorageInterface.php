<?php

namespace App\Providers\Rbac\Interfaces;

interface StorageInterface
{
    public function getUser();

    public function setUser(UserInterface $user);

    public function flush();

    public function isAuthenticated();

    public function setAuthenticated($authenticated);
}