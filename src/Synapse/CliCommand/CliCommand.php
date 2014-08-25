<?php

namespace Synapse\CliCommand;

class CliCommand extends AbstractCliCommand
{
    protected $arguments = array();
    protected $command   = '';

    protected function buildArguments()
    {
        $output = '';

        foreach ($this->arguments as $argument) {
            if (is_array($argument) and count($argument) >= 2) {
                list($name, $value) = $argument;

                if ((is_string($name) or is_int($name)) and (is_scalar($value) or is_null($value))) {
                    if (is_bool($name) or is_null($name)) {
                        $name = var_export($name, true);
                    } else {
                        $name = escapeshellarg($name);
                    }

                    if (is_bool($value) or is_null($value)) {
                        $value = var_export($value, true);
                    }else {
                        $value = escapeshellarg($value);
                    }

                    $output .= sprintf(
                        '%s=%s ',
                        $name,
                        $value
                    );
                }
            } elseif (is_scalar($argument)) {
                if (is_bool($argument)) {
                    $argument = var_export($argument, true);
                } else {
                    $argument = escapeshellarg($argument);
                }

                $output .= sprintf('%s ', $argument);
            }
        }

        return trim($output);
    }

    protected function getBaseCommand()
    {
        return sprintf('%s %s', $this->command, $this->buildArguments());
    }

    public function setBaseCommand($command, array $arguments = array())
    {
        $this->arguments = $arguments;
        $this->command   = $command;
    }
}
