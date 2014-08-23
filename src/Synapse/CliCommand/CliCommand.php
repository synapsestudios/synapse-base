<?php

namespace Synapse\CliCommand;

use Synapse\Stdlib\Arr;

class CliCommand extends AbstractCliCommand
{
    protected $arguments;
    protected $command;

    protected function getBaseCommand()
    {
        return trim(sprintf('%s %s', $this->command, $this->renderArguments()));
    }

    public function setBaseCommand($command, array $arguments = array())
    {
        $this->arguments = $arguments;
        $this->command   = $command;
    }

    protected function renderArguments()
    {
        $output = '';

        foreach ($this->arguments as $argument) {
            if (Arr::isArray($argument) and count($argument) >= 2) {
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
}
