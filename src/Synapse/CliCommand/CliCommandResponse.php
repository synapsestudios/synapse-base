<?php

namespace Synapse\CliCommand;

use Synapse\StdLib\DataObject;

class CliCommandResponse extends DataObject
{
    protected $object = [
        'elapsed_time' => null,
        'executed'     => false,
        'output'       => null,
        'return_code'  => null,
        'start_time'   => null,
        'successfull'  => null
    ];
}
