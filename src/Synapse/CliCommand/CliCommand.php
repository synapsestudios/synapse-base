<?php

namespace Synapse\CliCommand;

class CliCommand extends AbstractCliCommand
{
    protected $command   = '';

    /**
     * {@inheritdoc }
     */
    protected function getBaseCommand()
    {
        return escapeshellcmd($this->command);
    }

    /**
     * Sets the command and arguments to be executed
     *
     * @param string $command   the command to be executed
     */
    public function setBaseCommand($command = '')
    {
        $this->command = $command;
    }
}
