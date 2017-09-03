<?php

namespace App\Console\Command;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PubSubCommand
 * @package App\Console\Command
 */
class PubSubCommand extends Command
{
    protected $app;

    public function __construct($name = null, Application $application)
    {
        $this->app = $application;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('...')->setDescription('...')->setHelp('...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);

        $this->app['redis']->subscribe($this->app['redis.channels'], function ($payload, $channel) {

        });
    }
}