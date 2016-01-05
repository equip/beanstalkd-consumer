<?php

namespace Equip\BeanstalkdConsumer\Configuration;

use Aura\Cli\Context;
use Auryn\Injector;
use Pheanstalk\Pheanstalk;
use Equip\Configuration\ConfigurationInterface;

class PheanstalkConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->delegate(Pheanstalk::class, [$this, 'getPheanstalk']);
    }

    public function getPheanstalk(Context $context)
    {
        $env = $context->env;
        $host = $env->get('BEANSTALKD_HOST') ?: '127.0.0.1';
        $port = $env->get('BEANSTALKD_PORT') ?: 11300;
        return new Pheanstalk($host, $port);
    }
}
