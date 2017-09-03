<?php

namespace App\Providers\Config\Interfaces;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface ConfigurationProviderInterface
{
    /**
     * Generates the configuration tree builder.
     * @return NodeDefinition
     */
    public function getConfigTreeBuilder(): NodeDefinition;
}