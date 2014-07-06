<?php

namespace Im0rtality\TaskSchedulerBundle\Controller;

use Im0rtality\TaskSchedulerBundle\Core\Scheduler;
use Im0rtality\TaskSchedulerBundle\Core\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        /** @var Scheduler $scheduler */
        $scheduler = $this->get('im0rtality_task_scheduler.scheduler');

        $task = new Task();
        $task->setAt(new \DateTime('+5 seconds'));

        $scheduler->addTask($task);

        return $this->render(
            'Im0rtalityTaskSchedulerBundle:Default:index.html.twig',
            array(
                'tasks'     => $scheduler->getAllTasks(),
                'now'       => new \DateTime(),
                'ready'     => $scheduler->getReadyTasks(),
                'executing' => $scheduler->consumeNextTask(),
            )
        );
    }
}
