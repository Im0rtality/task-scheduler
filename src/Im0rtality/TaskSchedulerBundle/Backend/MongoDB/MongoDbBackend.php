<?php

namespace Im0rtality\TaskSchedulerBundle\Backend\MongoDB;

use Im0rtality\TaskSchedulerBundle\SchedulerBackendInterface;
use Im0rtality\TaskSchedulerBundle\Task;

class MongoDbBackend implements SchedulerBackendInterface
{
    /** @var  \MongoCollection */
    protected $collection;

    /**
     * @param \MongoCollection $collection
     */
    public function setMongoCollection(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @inheritdoc
     */
    public function count($onlyReady = true)
    {
        return $this->collection
            ->count($onlyReady ? ['at' => ['$lte' => new \MongoDate()]] : []);
    }

    /**
     * @inheritdoc
     */
    public function queueTask(Task $task)
    {
        $this->collection->insert($this->serializeToBackendFormat($task));
    }

    /**
     * @inheritdoc
     */
    public function getTask()
    {
        $current = $this->collection->findAndModify(
            ['at' => ['$lte' => new \MongoDate()]],
            null,
            null,
            [
                'sort'   => ['at' => 1],
                'remove' => true,
            ]
        );

        return $this->deserializeFromBackendFormat($current);
    }

    /**
     * @inheritdoc
     */
    public function peekTask()
    {
        $cursor = $this->collection
            ->find(['at' => ['$lte' => new \MongoDate()]])
            ->sort(['at' => 1])
            ->limit(1);

        return $cursor->hasNext() ? $this->deserializeFromBackendFormat($cursor->getNext()) : null;
    }

    /**
     * @inheritdoc
     */
    public function serializeToBackendFormat(Task $task)
    {
        return [
            'taskId' => $task->getTaskId(),
            'at' => new \MongoDate($task->getAt()->getTimestamp()),
            'data' => json_encode($task->getData()),
        ];
    }

    /**
     * @inheritdoc
     */
    public function deserializeFromBackendFormat($task)
    {
        return $task ? (new Task())
            ->setTaskId($task['taskId'])
            ->setAt(new \DateTime(sprintf('@%d', $task['at']->sec)))
            ->setData(json_decode($task['data'], true)) : null;
    }
}
