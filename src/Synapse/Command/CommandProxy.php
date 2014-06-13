<?php

namespace Synapse\Command;

use Synapse\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CommandProxy extends Command
{
    protected $app;
    protected $factory;

    /**
     * The key of the command factory in the Silex application
     *
     * @param string $key
     */
    public function setFactory(callable $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    public function setApplication(Application $app)
    {
        $this->app = $app;
        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $factory = $this->factory;
        $command = $factory($this->app);

        if (! $command instanceof CommandInterface) {
            throw new \InvalidArgumentException('Illegal command.');
        }

        $expectedClass = substr(get_class($this), 0, -5);
        if (! $command instanceof $expectedClass) {
            throw new \InvalidArgumentException(sprintf(
                'Command with class \'%s\' is not an instance of expected \'%s\'.',
                get_class($command),
                $expectedClass
            ));
        }

        return $command->execute($input, $output);
    }
}
