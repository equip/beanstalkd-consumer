<?php

namespace Equip\BeanstalkdConsumer\Configuration;

use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\EnvConfiguration;
use Equip\Configuration\ConfigurationSet;

class DefaultConfigurationSet extends ConfigurationSet
{
    public function __construct(array $data = [])
    {
        $data = array_merge([
            EnvConfiguration::class,
            AurynConfiguration::class,
            AuraCliConfiguration::class,
            PheanstalkConfiguration::class,
        ], $data);

        parent::__construct($data);
    }
}
