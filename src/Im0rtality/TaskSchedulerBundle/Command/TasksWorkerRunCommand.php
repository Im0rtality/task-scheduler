<?php

namespace Im0rtality\TaskSchedulerBundle\Command;

use Im0rtality\TaskSchedulerBundle\Core\MongoDbBackendInterface;
use Im0rtality\TaskSchedulerBundle\SchedulerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TasksWorkerRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tasks:worker:run')
            ->setDescription('Starts task scheduler worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SchedulerInterface $scheduler */
        $scheduler = $this->getContainer()->get('im0rtality_task_scheduler.scheduler');
        /** @var LoggerInterface $logger */
        $logger = $this->getContainer()->get('monolog.logger.runner');
        $output->writeln(sprintf('| %25s | %15s | %4s |', 'Timestamp', 'Command', 'Late (s)'));
        while (true) {
            $task = $scheduler->getTask();
            if ($task) {
                $output->writeln(
                    sprintf(
                        '| %25s | %15s | %4d',
                        (new \DateTime())->format('Y-m-d H:i:s'),
                        json_encode($task->getData()) ?: 'null',
                        time() - $task->getAt()->getTimestamp()
                    )
                );
                $logger->info(
                    'Scheduled task executed',
                    [
                        'id' => $task->getTaskId(),
                        'at' => $task->getAt()->format('Y-m-d H:i:s'),
                        'data' => $task->getData()
                    ]
                );
            } else {
                sleep(1);
            }
        }
    }
}
