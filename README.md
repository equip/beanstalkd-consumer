# Equip Beanstalkd Consumer

A small library for writing command line [beanstalkd](http://kr.github.io/beanstalkd/) consumers in [Equip](https://github.com/equip/framework) applications.

## Installation

Use [Composer](https://getcomposer.org/).

```
composer require equip/beanstalkd-consumer
```

## Writing Consumers

A consumer is a PHP class that implements [`ConsumerInterface`](https://github.com/equip/beanstalkd-consumer/tree/master/src/ConsumerInterface.php) to process jobs received from beanstalkd, each of which is represented by an instance of the [Pheanstalk](https://github.com/pda/pheanstalk) [`Job`](https://github.com/pda/pheanstalk/blob/master/src/Job.php) class.

Here's an example of a consumer implementation.

```php
namespace Acme;

use Equip\BeanstalkConsumer\ConsumerInterface;
use Pheanstalk\Job;

class FooConsumer implements ConsumerInterface
{
    public function consume(Job $job)
    {
        $id = $job->getId();
        $data = $job->getData();

        // unserialize and process $data here
    }
}
```

## Using Consumers

To use the consumer shown in the last section, be sure its namespace is included in your [Composer autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading), then invoke the runner as shown in the examples below.

If this library is installed as a project dependency:

```
BEANSTALKD_CONSUMER="Acme\\FooConsumer" ./vendor/bin/beanstalk-consumer
```

If this library is installed as a repository clone:

```
BEANSTALKD_CONSUMER="Acme\\FooConsumer" ./bin/beanstalkd-consumer
```

## Configuration

These environmental variables may be used to configure the command line consumer.

* `BEANSTALKD_CONSUMER` - fully-qualified name of a PHP class implementing [`ConsumerInterface`](https://github.com/equip/beanstalkd-consumer/tree/master/src/ConsumerInterface.php) to consume jobs
* `BEANSTALKD_HOST` - hostname of the beanstalkd server, defaults to `'127.0.0.1'`
* `BEANSTALKD_PORT` - port on which the beanstalkd server listens, defaults to `11300`
* `BEANSTALKD_TUBE` - tube from which the consumer should consume jobs, defaults to `'default'`
