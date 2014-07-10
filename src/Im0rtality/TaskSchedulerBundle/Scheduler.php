<?php

namespace Im0rtality\TaskSchedulerBundle;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Scheduler implements SchedulerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var  SchedulerInterface */
    protected $backend;

    /**
     * @param SchedulerBackendInterface $backend
     */
    public function setBackend(SchedulerBackendInterface $backend)
    {
        $this->backend = $backend;
    }

    public function queueTask(Task $task)
    {
        $this->backend->queueTask($task);

        $this->logger->info(
            'Task queued',
            [
                'id' => $task->getTaskId(),
                'at' => $task->getAt()
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getTask()
    {
        $task = $this->backend->getTask();

        if ($task) {
            $this->logger->info(
                'Task consumed',
                [
                    'id' => $task->getTaskId(),
                    'at' => $task->getAt(),
                    'delay' => $task->getAt()->getTimestamp() - time()
                ]
            );
        }

        return $task;
    }

    /**
     * @inheritdoc
     */
    public function peekTask()
    {
        $task = $this->backend->peekTask();

        if ($task) {
            $this->logger->info(
                'Task peeked',
                [
                    'id' => $task->getTaskId(),
                    'at' => $task->getAt(),
                    'delay' => $task->getAt()->getTimestamp() - time()
                ]
            );
        }

        return $task;
    }

    /**
     * @inheritdoc
     */
    public function count($onlyReady = false)
    {
        return $this->backend->count($onlyReady);
    }
}
