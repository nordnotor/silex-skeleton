<?php

namespace App\Interfaces;

use App\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\EventDispatcher\Event;

interface ControllerMiddlewareInterface
{
    /**
     * @return array
     */
    public function getMiddlewares(): array;
}