<?php

namespace Im0rtality\TaskSchedulerBundle\Core;

class Scheduler
{
    /** @var  \MongoClient */
    protected $mongo;

    /**
     * @param \MongoClient $mongo
     */
    public function setMongo($mongo)
    {
        $this->mongo = $mongo;
    }

    public function consumeNextTask()
    {
        $current = $this->mongo->tasks->task->findAndModify(
            ['at' => ['$lte' => new \MongoDate((new \DateTime())->getTimestamp())]],
            null,
            null,
            [
                'sort'   => ['at' => 1],
                'remove' => true,
            ]
        );

        return $current ? Task::fromArray($current) : null;
    }

    public function addTask(Task $task)
    {
        $this->mongo->tasks->task->insert($task->toArray());
    }

    public function getAllTasks()
    {
        return $this->mongo->tasks->task
            ->find()
            ->sort(['at' => 1]);
    }

    public function getReadyTasks()
    {
        return $this->mongo->tasks->task
            ->find(['at' => ['$lte' => new \MongoDate()]])
            ->sort(['at' => 1]);
    }
}
