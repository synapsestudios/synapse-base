<?php

namespace Synapse\CliCommand;

class CliCommand extends AbstractCliCommand
{
    protected $arguments;
    protected $command;

    public function __construct($command = null, CliCommandArguments $arguments = null)
    {
        $this->arguments = $arguments ?: new CliCommandArguments;
        $this->command   = $command;
    }

    protected function getBaseCommand()
    {
        return trim(sprintf('%s %s', $this->command, $this->arguments->render()));
    }

    public function setCommand($command, CliCommandArguments $arguments = null)
    {
        $this->arguments = $arguments ?: new CliCommandArguments;
        $this->command   = $command;
    }
}
