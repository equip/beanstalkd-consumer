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

    /**
     * @var Env
     */
    private $env;

    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    public function apply(Injector $injector)
    {
        $injector->define(Pheanstalk::class, [
            ':host' => $this->env->getValue(self::HOST_KEY, '127.0.0.1'),
            ':port' => $this->env->getValue(self::PORT_KEY, 11300),
        ]);
    }
}
