<?php

namespace App\Interfaces;

use App\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\EventDispatcher\Event;

interface MiddlewareInterface
{
    public function handle(Event $event, Application $app);

    public function support(Event $event): bool;
}