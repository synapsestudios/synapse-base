<?php

namespace Synapse\CliCommand;

use Synapse\Stdlib\Arr;

class CliCommandArguments
{
    protected $arguments;

    public function __construct(array $arguments = array())
    {
        $this->arguments = $arguments;
    }

    public function __toString()
    {
        $output = '';

        foreach ($this->arguments as $argument) {
            if (Arr::isArray($argument)) {
                list($name, $value) = $argument;

                $output .= sprintf(
                    '%s=%s ',
                    $name,
                    $value
                );
            } else {
                $output .= sprintf('%s ', $argument);
            }
        }

        return trim($output);
    }

    public function render()
    {
        return $this->__toString();
    }
}
