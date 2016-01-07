<?php

namespace Equip\BeanstalkdConsumer;

class Job
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $data;

    /**
     * @param int $id
     * @param string $data
     */
    public function __construct($id, $data)
    {
        $this->id = (int) $id;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
