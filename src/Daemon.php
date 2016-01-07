<?php

namespace Equip\BeanstalkdConsumer;

use Aura\Cli\Stdio;
use Aura\Cli\Status;
use Equip\Env;
use Relay\ResolverInterface;
use Pheanstalk\Pheanstalk;

class Daemon
{
    const TUBE_KEY = 'BEANSTALKD_TUBE';
    const CONSUMER_KEY = 'BEANSTALKD_CONSUMER';

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Stdio
     */
    private $stdio;

    /**
     * @var Env
     */
    private $env;

    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    /**
     * @var callable
     */
    private $listener;

    /**
     * @param ResolverInterface $resolver
     * @param Stdio $stdio
     * @param Env $env
     * @param Pheanstalk $pheanstalk
     */
    public function __construct(
        ResolverInterface $resolver,
        Stdio $stdio,
        Env $env,
        Pheanstalk $pheanstalk
    ) {
        $this->resolver = $resolver;
        $this->stdio = $stdio;
        $this->env = $env;
        $this->pheanstalk = $pheanstalk;
        $this->listener = function () { return true; };
    }

    /**
     * @param callable $listener
     */
    public function setListener(callable $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @return int Status code to exit with
     */
    public function run()
    {
        if (!$this->env->hasValue(self::CONSUMER_KEY)) {
            $this->stdio->outln('<<red>>BEANSTALKD_CONSUMER environmental variable is not set<<reset>>');
            return Status::USAGE;
        }

        $class = $this->env->getValue(self::CONSUMER_KEY);
        if (!class_exists($class)) {
            $this->stdio->outln(sprintf('<<red>>BEANSTALKD_CONSUMER does not reference a locatable class: %s<<reset>>', $class));
            return Status::DATAERR;
        }
        if (!in_array(ConsumerInterface::class, class_implements($class))) {
            $this->stdio->outln(sprintf('<<red>>BEANSTALKD_CONSUMER references a class that does not implement ConsumerInterface: %s<<reset>>', $class));
            return Status::DATAERR;
        }

        $tube = $this->env->getValue(self::TUBE_KEY, 'default');
        $this->pheanstalk->watchOnly($tube);
        $consumer = call_user_func($this->resolver, $class);

        while (call_user_func($this->listener)) {
            $reserved = $this->pheanstalk->reserve();
            if (!$reserved) {
                continue;
            }
            $job = new Job($reserved->getId(), $reserved->getData());

            try {
                $result = $consumer->consume($job);
                if ($result === false) {
                    $this->pheanstalk->release($job);
                    continue;
                }
            } catch (\Exception $e) {
                $this->pheanstalk->release($job);
                throw $e;
            }

            $this->pheanstalk->delete($job);
        }
    }
}
