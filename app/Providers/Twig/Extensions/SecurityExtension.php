<?php

namespace App\Providers\Twig\Extensions;

use Silex\Application;

/**
 * Class SecurityExtension
 * @package e1\providers\Twig\Extensions
 *
 * @property Application $app
 * @property string $securityChecker
 */
class SecurityExtension extends \Twig_Extension
{
    private $app;
    private $securityChecker;

    public function __construct(string $securityChecker = null, Application $app)
    {
        $this->app = $app;
        $this->securityChecker = $securityChecker;
    }

    public function isGranted(array $roles)
    {
        if (!isset($this->app[$this->securityChecker])) {
            return false;
        }
        return $this->app[$this->securityChecker]->isGranted($roles, $this->app['request_stack']->getCurrentRequest());
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_granted', array($this, 'isGranted')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'security';
    }
}