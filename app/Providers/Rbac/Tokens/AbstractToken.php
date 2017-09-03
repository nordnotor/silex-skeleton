<?php

namespace App\Providers\Rbac\Tokens;

use App\Providers\Rbac\Interfaces\TokenInterface;
use App\Providers\Rbac\Interfaces\UserInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractToken
 * @package App\Providers\Rbac\Tokens
 *
 */
abstract class AbstractToken implements TokenInterface
{
    protected $app;

    private $user;
    private $authenticated = false;
    private $attributes = [];

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
     * @param UserInterface|null $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        $this->setAuthenticated(true);
    }

    /**
     * @return  UserInterface|null $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns the token attributes.
     *
     * @return array The token attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets the token attributes.
     *
     * @param array $attributes The token attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns true if the attribute exists.
     *
     * @param string $name The attribute name
     *
     * @return bool true if the attribute exists, false otherwise
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns an attribute value.
     *
     * @param string $name The attribute name
     *
     * @return mixed The attribute value
     *
     * @throws \InvalidArgumentException When attribute doesn't exist for this token
     */
    public function getAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('This token has no "%s" attribute.', $name));
        }

        return $this->attributes[$name];
    }

    /**
     * Sets an attribute.
     *
     * @param string $name The attribute name
     * @param mixed $value The attribute value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = (bool)$authenticated;
    }

    # magic methods

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            is_object($this->user) ? clone $this->user : $this->user,
            $this->authenticated,
            $this->attributes,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->user, $this->authenticated, $this->attributes) = unserialize($serialized);
    }
}
