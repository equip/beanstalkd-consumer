<?php

namespace Equip\BeanstalkdConsumer;

interface ConsumerInterface
{
    /**
     * @return boolean FALSE if the job could not be successfully processed
     */
    public function consume(Job $job);
}
