<?php

namespace App\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

use App\Console\Command\{
    CacheClearCommand, ExampleCommand, PubSubCommand
};
use Twig\Cache\FilesystemCache;

/**
 * @var \App\Application $app
 */

$console = new Application('Console Application', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'App.'));

$console->add(new PubSubCommand('redis:PubSubCommand', $app));
$console->add(new ExampleCommand('app:ExampleCommand', $app));
$console->add(new CacheClearCommand('cache:clear', $app));

return $console;

