<?php

namespace Synapse\CliCommand;

use Synapse\Stdlib\DataObject;

class CliCommandResponse extends DataObject
{
    protected $object = [
        'command'      => null,
        'elapsed_time' => null,
        'output'       => null,
        'return_code'  => null,
        'start_time'   => null,
        'successful'   => null
    ];
}
