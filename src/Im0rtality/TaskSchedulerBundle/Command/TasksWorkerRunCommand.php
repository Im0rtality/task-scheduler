<?php

namespace Im0rtality\TaskSchedulerBundle\Command;

use Im0rtality\TaskSchedulerBundle\Core\Scheduler;
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
        /** @var Scheduler $scheduler */
        $scheduler = $this->getContainer()->get('im0rtality_task_scheduler.scheduler');
        /** @var LoggerInterface $logger */
        $logger = $this->getContainer()->get('monolog.logger.runner');
        $output->writeln(sprintf('| %25s | %15s | %4s |', 'Timestamp', 'Command', 'Late (s)'));
        while (true) {
            $task = $scheduler->consumeNextTask();
            if ($task) {
                $output->writeln(
                    sprintf(
                        '| %25s | %15s | %4d',
                        (new \DateTime())->format('c'),
                        $task->getCommand() ?: '',
                        (new \DateTime())->getTimestamp() - $task->getAt()->getTimestamp()
                    )
                );
                $logger->info('Scheduled task executed', $task->toJson());
            } else {
                sleep(1);
            }
        }
    }
}
