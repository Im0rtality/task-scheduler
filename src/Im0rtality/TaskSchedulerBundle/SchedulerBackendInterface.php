<?php

namespace Im0rtality\TaskSchedulerBundle;

interface SchedulerBackendInterface extends SchedulerInterface
{

    /**
     * Converts Task instance to backend compatible structure for storage
     *
     * @param Task $task
     * @return mixed
     */
    public function serializeToBackendFormat(Task $task);

    /**
     * Converts backend data structure to Task instance
     *
     * @param mixed $task
     * @return Task
     */
    public function deserializeFromBackendFormat($task);
}
