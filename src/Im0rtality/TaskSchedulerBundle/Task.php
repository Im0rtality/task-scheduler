<?php

namespace Im0rtality\TaskSchedulerBundle;

class Task
{
    /** @var  string */
    protected $taskId;

    /** @var  \DateTime */
    protected $at;

    /** @var  mixed */
    protected $data;

    function __construct()
    {
        $this->taskId = md5(uniqid());
    }

    /**
     * @return \DateTime
     */
    public function getAt()
    {
        return $this->at;
    }

    /**
     * @param \DateTime $at
     * @return $this
     */
    public function setAt($at)
    {
        $this->at = $at;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $payload
     * @return $this
     */
    public function setData($payload)
    {
        $this->data = $payload;

        return $this;
    }

    /**
     * @return string
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param string $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;

        return $this;
    }
}
