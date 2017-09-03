<?php

namespace App\Providers\Config\Loaders;

use App\Providers\Config\FileLoader;

class JsonLoader extends FileLoader
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
        $data = json_decode(file_get_contents($resource), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(sprintf('Invalid JSON provided "%s" in "%s"', json_last_error(), $resource));
        }

        return $data ?? [];
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
        return is_string($resource) && 'json' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}