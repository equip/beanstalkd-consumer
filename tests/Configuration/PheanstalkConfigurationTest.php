<?php

namespace EquipTests\BeanstalkdConsumer\Configuration;

use Auryn\Injector;
use Pheanstalk\Pheanstalk;
use Equip\BeanstalkdConsumer\Configuration\PheanstalkConfiguration;

class PheanstalkConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testApply()
    {
        $injector = new Injector;
        $configuration = $injector->make(PheanstalkConfiguration::class);
        $configuration->apply($injector);
        $instance = $injector->make(Pheanstalk::class);
        $this->assertInstanceOf(Pheanstalk::class, $instance);
    }
}
