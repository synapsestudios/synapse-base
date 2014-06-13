<?php

namespace Synapse\Command;

use Synapse\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CommandProxy extends Command
{
    protected $factory;

    /**
     * The key of the command factory in the Silex application
     *
     * @param string $key
     */
    public function setFactory(callable $factory)
    {
        $this->factory = $factory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = $this->factory;
        $command = $factory();

        if (! $command instanceof CommandInterface) {
            throw new \LogicException('Illegal command.');
        }

        return $command->execute($input, $output);
    }
}
