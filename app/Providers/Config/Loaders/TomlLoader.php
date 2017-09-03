<?php

namespace App\Providers\Config\Loaders;

use Toml\Parser;
use App\Providers\Config\FileLoader;

# see: https://github.com/jamesmoss/toml

class TomlLoader extends FileLoader
{

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string|null $type The resource type or null if unknown
     * @return array
     *
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null)
    {
        if (!class_exists('Toml\\Parser')) {
            throw new \RuntimeException('Unable to read toml as the Toml Parser is not installed.');
        }
        $config = Parser::fromFile($resource);
        return $config ?: array();
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string|null $type The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'toml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}