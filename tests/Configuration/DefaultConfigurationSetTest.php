<?php

namespace EquipTests\BeanstalkdConsumer\Configuration;

use Auryn\Injector;
use Phake;
use Equip\BeanstalkdConsumer\Configuration\AuraCliConfiguration;
use Equip\BeanstalkdConsumer\Configuration\DefaultConfigurationSet;
use Equip\BeanstalkdConsumer\Configuration\PheanstalkConfiguration;
use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\ConfigurationInterface;
use Equip\Configuration\EnvConfiguration;

class DefaultConfigurationSetTest extends \PHPUnit_Framework_TestCase
{
    public function testApply()
    {
        $mock = Phake::mock(ConfigurationInterface::class);
        $class = get_class($mock);
        $configuration = new DefaultConfigurationSet([$class]);

        foreach ([
            EnvConfiguration::class,
            AurynConfiguration::class,
            AuraCliConfiguration::class,
            PheanstalkConfiguration::class,
            $class,
        ] as $value) {
            $this->assertTrue($configuration->hasValue($value));
        }
    }
}
