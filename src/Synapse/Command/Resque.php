<?php

namespace Synapse\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Synapse\Log\LoggerAwareInterface;
use Synapse\Log\LoggerAwareTrait;

use Synapse\Resque\Resque as ResqueService;
use Resque_Event;
use Resque_Worker;

use RuntimeException;

class Resque extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Synapse\Resque\Resque
     */
    protected $resque;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function setResque(ResqueService $resque)
    {
        $this->resque = $resque;
        return $this;
    }

    protected function configure()
    {
        $this->setName('resque')
            ->setDescription('Control worker processes')
            ->addArgument(
                'queue',
                InputArgument::IS_ARRAY,
                'Which queues should the worker process(es) watch? (comma-separated)'
            )
            ->addOption(
                'interval',
                null,
                InputOption::VALUE_REQUIRED,
                'How often should the workers check for new jobs? (seconds)',
                5
            )
            ->addOption(
                'count',
                null,
                InputOption::VALUE_REQUIRED,
                'How many worker processes should run?',
                1
            )
            ->addOption(
                'shutdown',
                null,
                InputOption::VALUE_NONE,
                'Specify this option to shut down the workers',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $logger       = $this->logger;

        Resque_Event::listen(
            'onFailure',
            function ($exception, $job) use ($logger) {
                $logger->error('Error processing job', [
                    'exception' => $exception
                ]);
            }
        );

        if ($input->getOption('shutdown')) {
            $this->shutdownWorkers();
            return;
        }

        $queues = $input->getArgument('queue');
        if (! count($queues)) {
            throw new RuntimeException('Not enough arguments.');
        }

        $count    = $input->getOption('count');
        $interval = $input->getOption('interval');

        if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $logLevel = Resque_Worker::LOG_NORMAL;
        } elseif ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $logLevel = Resque_Worker::LOG_VERBOSE;
        } else {
            $logLevel = Resque_Worker::LOG_NONE;
        }

        $this->startWorkers($queues, $count, $logLevel, $interval);
    }

    protected function shutdownWorkers()
    {
        $workers = Resque_Worker::all();

        foreach ($workers as $worker) {
            list($name, $pid, $queues) = explode(':', (string) $worker);
            posix_kill((int) $pid, SIGQUIT);
        }

        $this->output->writeln('<info>SIGQUIT sent to '.count($workers).' workers.</info>');
    }

    protected function startWorkers($queues, $count = 1, $logLevel = 0, $interval = 5)
    {
        for ($i = 0; $i < $count; ++$i) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                // Could not fork
                $this->output->writeln('<error>Could not fork worker '.$i.'</error>');
                return;
            } elseif (! $pid) {
                // Child now
                $worker = new Resque_Worker($queues);
                $worker->logLevel = $logLevel;

                $this->output->writeln('<info>*** Starting worker '.$worker.'</info>');
                $worker->work($interval);

                // Have to break now to stop the child from forking! This will
                // not run in the parent.
                break;
            }
        }
    }
}
