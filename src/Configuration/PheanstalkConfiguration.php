<?php

namespace Equip\BeanstalkdConsumer\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Equip\Env;
use Pheanstalk\Pheanstalk;

class PheanstalkConfiguration implements ConfigurationInterface
{
    const HOST_KEY = 'BEANSTALKD_HOST';
    const PORT_KEY = 'BEANSTALKD_PORT';

    public function apply(Injector $injector)
    {
        $injector->delegate(Pheanstalk::class, [$this, 'getPheanstalk']);
    }

    public function getPheanstalk(Env $env)
    {
        $host = $env->getValue(self::HOST_KEY, '127.0.0.1');
        $port = $env->getValue(self::PORT_KEY, 11300);
        return new Pheanstalk($host, $port);
    }
}
