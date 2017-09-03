<?php
/**
 * Created by PhpStorm.
 * User: vlad
 * Date: 20.07.17
 * Time: 16:54
 */

namespace App\Providers\Config\Interfaces;


use Symfony\Component\Config\Definition\ConfigurationInterface;

interface ApplicationConfigurationInterface extends ConfigurationInterface
{
    /**
     * Return array Configuration directories.
     *
     * @return mixed
     */
    public function configurationDirs(): array;

    /**
     * Load configuration to app.
     *
     * @param array $array
     * @return void
     */
    public function loadConfiguration(array $array);
}