<?php

namespace Synapse\CliCommand;

use Synapse\StdLib\DataObject;

class CliCommandResponse extends DataObject
{
    protected $object = [
        'elapsedTime' => null,
        'executed'    => false,
        'output'      => null,
        'returnCode'  => null,
        'startTime'   => null,
        'successfull' => null
    ];
}
