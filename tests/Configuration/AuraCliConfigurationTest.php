<?php

namespace EquipTests\BeanstalkdConsumer\Configuration;

use Auryn\Injector;
use Aura\Cli\Context;
use Aura\Cli\Stdio;
use Equip\BeanstalkdConsumer\Configuration\AuraCliConfiguration;

class AuraCliConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function dataMapping()
    {
        return [
            [Stdio::class],
            [Context::class],
        ];
    }

    /**
     * @param string $class
     * @dataProvider dataMapping
     */
    public function testApply($class)
    {
        $injector = new Injector;
        $configuration = new AuraCliConfiguration;
        $configuration->apply($injector);
        $instance = $injector->make($class);
        $this->assertInstanceOf($class, $instance);
    }
}
