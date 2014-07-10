<?php
/**
 * Created by PhpStorm.
 * User: laurynasverzukauskas
 * Date: 10/07/14
 * Time: 20:31
 */
namespace Im0rtality\TaskSchedulerBundle;

interface SchedulerInterface
{
    /**
     * Queue ups given task
     *
     * @param Task $task
     */
    public function queueTask(Task $task);

    /**
     * Gets next ready task and deletes it from storage
     *
     * @return Task|null
     */
    public function getTask();

    /**
     * Peeks at next ready task without removing it
     *
     * @return Task|null
     */
    public function peekTask();

    /**
     * Counts tasks
     *
     * @param bool $onlyReady Counts only ready tasks if true
     * @return int
     */
    public function count($onlyReady = false);
}
