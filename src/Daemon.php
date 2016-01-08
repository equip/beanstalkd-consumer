<?php

namespace Equip\BeanstalkdConsumer;

use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Aura\Cli\Status;
use Relay\ResolverInterface;
use Pheanstalk\Pheanstalk;

class Daemon
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Stdio
     */
    private $stdio;

    /**
     * @var Context
     */
    private $context;

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
     * @param Context $context
     * @param Pheanstalk $pheanstalk
     */
    public function __construct(
        ResolverInterface $resolver,
        Stdio $stdio,
        Context $context,
        Pheanstalk $pheanstalk
    ) {
        $this->resolver = $resolver;
        $this->stdio = $stdio;
        $this->context = $context;
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
        $env = $this->context->env;
        $tube = $env->get('BEANSTALKD_TUBE') ?: 'default';
        $class = $env->get('BEANSTALKD_CONSUMER');
        if (!$class) {
            $this->stdio->outln('<<red>>BEANSTALKD_CONSUMER environmental variable is not set<<reset>>');
            return Status::USAGE;
        }
        if (!class_exists($class)) {
            $this->stdio->outln(sprintf('<<red>>BEANSTALKD_CONSUMER does not reference a locatable class: %s<<reset>>', $class));
            return Status::DATAERR;
        }
        if (!in_array(ConsumerInterface::class, class_implements($class))) {
            $this->stdio->outln(sprintf('<<red>>BEANSTALKD_CONSUMER references a class that does not implement ConsumerInterface: %s<<reset>>', $class));
            return Status::DATAERR;
        }

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

        return Status::SUCCESS;
    }
}
