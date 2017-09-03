<?php

namespace App\Interfaces;

use Silex\Api\ControllerProviderInterface;

interface ControllerInterface extends ControllerProviderInterface
{
    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array;

    /**
     * @param string $name
     * @return array
     */
    public function getAdditions(string $name): array;
}