<?php

namespace Im0rtality\TaskSchedulerBundle\Core;

class Task
{
    /** @var  string */
    protected $taskId;
    /** @var  \DateTime */
    protected $at;
    /** @var  mixed */
    protected $payload;

    /** @var  string */
    protected $command;

    function __construct()
    {
        $this->taskId = md5(uniqid());
    }

    public function toMongo()
    {
        return [
            'id'      => $this->taskId,
            'at'      => new \MongoDate($this->at->getTimestamp()),
            'command' => $this->command,
            'payload' => $this->payload,
        ];
    }

    public static function fromMongo($data)
    {
        $task = new Task();

        $task->at      = new \DateTime('@' . $data['at']->sec);
        $task->taskId  = $data['id'];
        $task->command = @$data['command'];
        $task->payload = $data['payload'];

        return $task;
    }

    public function toJson()
    {
        return [
            'id'      => $this->taskId,
            'at'      => $this->at->format('c'),
            'command' => $this->command,
            'payload' => $this->payload,
        ];
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
     */
    public function setAt($at)
    {
        $this->at = $at;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
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
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }
}
