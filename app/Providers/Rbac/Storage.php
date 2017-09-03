<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 27.07.17
 * Time: 3:28
 */

namespace App\Providers\Rbac;


use App\Providers\Rbac\Interfaces\StorageInterface;
use App\Providers\Rbac\Interfaces\UserInterface;
use Jenssegers\Mongodb\Eloquent\Model;

class Storage implements StorageInterface
{
    protected $user;
    protected $authenticated;

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        if ($this->getUser() instanceof UserInterface) {
            $this->getUser()->eraseCredentials();
        }
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        $this->setAuthenticated(true);
    }

    /**
     * @return  UserInterface|null|Model $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = (bool)$authenticated;
    }


    public function flush()
    {
        $this->user = null;
        $this->setAuthenticated(false);
    }
}