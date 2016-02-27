# Equip Beanstalkd Consumer

[![Latest Stable Version](https://img.shields.io/packagist/v/equip/beanstalkd-consumer.svg)](https://packagist.org/packages/equip/beanstalkd-consumer)
[![License](https://img.shields.io/packagist/l/equip/beanstalkd-consumer.svg)](https://github.com/equip/beanstalkd-consumer/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/equip/beanstalkd-consumer.svg)](https://travis-ci.org/equip/beanstalkd-consumer)
[![Code Coverage](https://scrutinizer-ci.com/g/equip/beanstalkd-consumer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/equip/beanstalkd-consumer/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/equip/beanstalkd-consumer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/equip/beanstalkd-consumer/?branch=master)

A small library for writing command line [beanstalkd](http://kr.github.io/beanstalkd/) consumers in [Equip](https://github.com/equip/framework) applications.

## Installation

Use [Composer](https://getcomposer.org/).

```
composer require equip/beanstalkd-consumer
```

## Writing Consumers

A consumer is a PHP class that implements [`ConsumerInterface`](https://github.com/equip/beanstalkd-consumer/tree/master/src/ConsumerInterface.php) to process jobs received from beanstalkd, each of which is represented by an instance of the [`Job`](https://github.com/equip/beanstalkd-consumer/blob/master/src/Job.php) class.

Here's an example of a consumer implementation.

```php
namespace Acme;

use Equip\BeanstalkConsumer\ConsumerInterface;
use Equip\BeanstalkConsumer\Job;

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
BEANSTALKD_CONSUMER="Acme\\FooConsumer" ./vendor/bin/beanstalkd-consumer
```

If this library is installed as a repository clone:

```
BEANSTALKD_CONSUMER="Acme\\FooConsumer" ./bin/beanstalkd-consumer
```

## Configuration

These environmental variables may be used to configure the runner.

* `BEANSTALKD_CONSUMER` - fully-qualified name of a PHP class implementing [`ConsumerInterface`](https://github.com/equip/beanstalkd-consumer/tree/master/src/ConsumerInterface.php) to consume jobs
* `BEANSTALKD_HOST` - hostname of the beanstalkd server, defaults to `'127.0.0.1'`
* `BEANSTALKD_PORT` - port on which the beanstalkd server listens, defaults to `11300`
* `BEANSTALKD_TUBE` - tube from which the consumer should consume jobs, defaults to `'default'`

## Consumer Dependencies

By default, [Auryn](https://github.com/rdlowrey/Auryn) is used internally as a resolver to create consumer instances. As such, with some additional code, it can be used to inject dependencies into consumers as well.

In order to apply any additional [configurations](http://equipframework.readthedocs.org/en/latest/#configuration) needed for consumers to the Auryn [`Injector`](https://github.com/rdlowrey/auryn/blob/master/lib/Injector.php) instance in use, a custom runner must be written. It will likely look familiar similar to the [stock runner](https://github.com/equip/beanstalkd-consumer/blob/master/bin/beanstalkd-consumer) except that, in addition to the [`DefaultConfigurationSet`](https://github.com/equip/beanstalkd-consumer/blob/master/src/Configuration/DefaultConfigurationSet.php) class that establishes a basic level of configuration for the runner, it will also apply any configurations that consumers require. This can be done using a subclass of [`ConfigurationSet`](https://github.com/equip/framework/blob/master/src/Configuration/ConfigurationSet.php) as shown in the example below.

```php
namespace Acme;

use Equip\BeanstalkdConsumer\Configuration\DefaultConfigurationSet;
use Equip\Configuration\ConfigurationSet;

class Configuration extends ConfigurationSet
{
    public function __construct()
    {
        parent::__construct([
            DefaultConfigurationSet::class,
            FooConfiguration::class,
            BarConfiguration::class,
            // etc.
        ]);
    }
}
```

The only needed difference between the stock and custom runners would be that the class shown above is used instead of [`DefaultConfigurationSet`](https://github.com/equip/beanstalkd-consumer/blob/master/src/Configuration/DefaultConfigurationSet.php) when configuring the Auryn [`Injector`](https://github.com/rdlowrey/auryn/blob/master/lib/Injector.php) instance.
