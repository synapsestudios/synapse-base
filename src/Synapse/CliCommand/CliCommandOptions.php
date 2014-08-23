<?php

namespace Synapse\CliCommand;

use Synapse\Stdlib\DataObject;

class CliCommandOptions extends DataObject
{
    protected $object = [
        'cwd'      => null,   // Use current working directory
        'env'      => null,   // Use current environment vars
        'redirect' => '2>&1', // Send errors to STDOUT
    ];
}
