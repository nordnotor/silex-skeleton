<?php

namespace App\Interfaces;

use App\Providers\Config\Interfaces\ConfigurationProviderInterface;
use Pimple\ServiceProviderInterface as SilexServiceProviderInterface;

interface ServiceProviderInterface extends ConfigurationProviderInterface, SilexServiceProviderInterface
{

}