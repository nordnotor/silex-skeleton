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
class CacheClearCommand extends Command
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
        $output->writeln($this->rmR(
            $this->app['cache.dir']) ? 'cache clear.' : 'cache not clear.'
        );
    }

    public function rmR(string $dir): bool
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {

                if (!$this->rmR("$dir/$file")) {
                    return false;
                }
            } elseif (!unlink("$dir/$file")) {
                return false;
            }
        }
        return rmdir($dir);
    }
}