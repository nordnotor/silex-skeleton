<?php

namespace App\Providers\Config;

use Symfony\Component\Config\Loader\FileLoader as Loader;

abstract class FileLoader extends Loader
{
    const TYPE_TOML = 'toml';
    const TYPE_YAML = 'yaml';
    const TYPE_JSON = 'json';
    const TYPE_PHP = 'php';
}