<?php

namespace Im0rtality\TaskSchedulerBundle\Controller;

use Im0rtality\TaskSchedulerBundle\SchedulerInterface;
use Im0rtality\TaskSchedulerBundle\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        /** @var SchedulerInterface $scheduler */
        $scheduler = $this->get('im0rtality_task_scheduler.scheduler');

        $scheduler->queueTask((new Task())->setAt(new \DateTime('+5 seconds')));

        return $this->render(
            'Im0rtalityTaskSchedulerBundle:Default:index.html.twig',
            array(
                'tasks' => [
                    'all' => $scheduler->count(),
                    'ready' => $scheduler->count(true)
                ],
                'peeked' => $scheduler->peekTask(),
            )
        );
    }
}
