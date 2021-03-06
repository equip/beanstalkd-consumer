<?php

namespace EquipTests\BeanstalkdConsumer;

use Aura\Cli\Stdio;
use Aura\Cli\Context;
use Aura\Cli\Context\Env;
use Aura\Cli\Status;
use Phake;
use Relay\ResolverInterface;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;
use Equip\BeanstalkdConsumer\ConsumerInterface;
use Equip\BeanstalkdConsumer\Daemon;
use Equip\BeanstalkdConsumer\Job;

class DaemonTest extends \PHPUnit_Framework_TestCase
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
     * @var Env
     */
    private $context;

    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    /**
     * @var Daemon
     */
    private $daemon;

    /**
     * @var string
     */
    private $tube = 'foo';

    /**
     * @var int
     */
    private $job_id = 2;

    /**
     * @var string
     */
    private $job_data = 'bar';

    /**
     * @var boolean
     */
    private $listening = false;

    protected function setUp()
    {
        $this->resolver = Phake::mock(ResolverInterface::class);
        $this->stdio = Phake::mock(Stdio::class);
        $this->env = Phake::mock(Env::class);
        $context = Phake::mock(Context::class);
        Phake::when($context)->__get('env')->thenReturn($this->env);
        $this->pheanstalk = Phake::mock(Pheanstalk::class);
        $this->daemon = new Daemon(
            $this->resolver,
            $this->stdio,
            $context,
            $this->pheanstalk
        );
        $this->daemon->setListener(function () {
            return $this->listening = !$this->listening;
        });
    }

    public function testRunWithoutConsumer()
    {
        $this->setTube();
        $result = $this->daemon->run();
        $this->assertSame(Status::USAGE, $result);
        Phake::verify($this->stdio)->outln('<<red>>BEANSTALKD_CONSUMER environmental variable is not set<<reset>>');
    }

    public function testRunWithNonexistentConsumer()
    {
        $this->setTube();
        $this->setConsumer('NonExistentClass');
        $result = $this->daemon->run();
        $this->assertSame(Status::DATAERR, $result);
        Phake::verify($this->stdio)->outln('<<red>>BEANSTALKD_CONSUMER does not reference a locatable class: NonExistentClass<<reset>>');
    }

    public function testRunWithInvalidConsumer()
    {
        $this->setTube();
        $this->setConsumer('\stdClass');
        $result = $this->daemon->run();
        $this->assertSame(Status::DATAERR, $result);
        Phake::verify($this->stdio)->outln('<<red>>BEANSTALKD_CONSUMER references a class that does not implement ConsumerInterface: \stdClass<<reset>>');
    }

    public function testRunWithNoJob()
    {
        $this->setTube();
        $consumer = $this->getConsumer();
        $result = $this->daemon->run();
        $this->assertSame(Status::SUCCESS, $result);
        $this->verifyTube();
        Phake::verify($consumer, Phake::never())->consume(Phake::anyParameters());
    }

    public function testRunWithJobThatThrowsException()
    {
        $this->setTube();
        $job = $this->getJob();
        $consumer = $this->getConsumer();
        $exception = new \Exception('message');
        Phake::when($consumer)->consume($job)->thenThrow($exception);
        try {
            $this->daemon->run();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertSame($exception, $e);
        }
        $this->verifyTube();
        Phake::verify($this->pheanstalk)->release($job);
    }

    public function testRunWithJobThatReturnsFalse()
    {
        $this->setTube();
        $job = $this->getJob();
        $consumer = $this->getConsumer();
        Phake::when($consumer)->consume($job)->thenReturn(false);
        $result = $this->daemon->run();
        $this->assertSame(Status::SUCCESS, $result);
        $this->verifyTube();
        Phake::verify($this->pheanstalk)->release($job);
    }

    public function testRunWithJobThatSucceeds()
    {
        $this->setTube();
        $job = $this->getJob();
        $consumer = $this->getConsumer();
        Phake::when($consumer)->consume($job)->thenReturn(true);
        $result = $this->daemon->run();
        $this->assertSame(Status::SUCCESS, $result);
        $this->verifyTube();
        Phake::verify($this->pheanstalk)->delete($job);
    }

    public function testRunWithDefaultTube()
    {
        $this->tube = 'default';
        $job = $this->getJob();
        $consumer = $this->getConsumer();
        Phake::when($consumer)->consume($job)->thenReturn(true);
        $result = $this->daemon->run();
        $this->assertSame(Status::SUCCESS, $result);
        $this->verifyTube();
        Phake::verify($this->pheanstalk)->delete($job);
    }

    private function setTube()
    {
        Phake::when($this->env)->get('BEANSTALKD_TUBE')->thenReturn($this->tube);
    }

    private function verifyTube()
    {
        Phake::verify($this->pheanstalk)->watchOnly($this->tube);
    }

    private function setConsumer($consumer)
    {
        Phake::when($this->env)->get('BEANSTALKD_CONSUMER')->thenReturn($consumer);
    }

    private function getConsumer()
    {
        $mock = Phake::mock(ConsumerInterface::class);
        $class = get_class($mock);
        $this->setConsumer($class);
        Phake::when($this->resolver)->__invoke($class)->thenReturn($mock);
        return $mock;
    }

    private function getJob()
    {
        $mock = Phake::mock(PheanstalkJob::class);
        Phake::when($mock)->getId()->thenReturn($this->job_id);
        Phake::when($mock)->getData()->thenReturn($this->job_data);
        Phake::when($this->pheanstalk)->reserve()->thenReturn($mock);
        return $this->isInstanceOf(Job::class);
    }
}
