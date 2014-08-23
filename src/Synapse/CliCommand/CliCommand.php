<?php

namespace Synapse\CliCommand;

class CliCommand extends AbstractCliCommand
{
    protected $arguments = array();
    protected $command   = '';

    protected function getBaseCommand()
    {
        return trim(sprintf('%s %s', $this->command, $this->getArguments()));
    }

    protected function getArguments()
    {
        $output = '';

        foreach ($this->arguments as $argument) {
            if (is_array($argument) and count($argument) >= 2) {
                list($name, $value) = $argument;

                if (is_scalar($name) and is_scalar($value)) {
                    $output .= sprintf(
                        '%s=%s ',
                        $name,
                        $value
                    );
                }
            } elseif (is_scalar($argument)) {
                $output .= sprintf('%s ', $argument);
            }
        }

        return trim($output);
    }

    public function setBaseCommand($command, array $arguments = array())
    {
        $this->arguments = $arguments;
        $this->command   = $command;
    }
}
