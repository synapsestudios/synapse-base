<?php

namespace Synapse\CliCommand;

use Synapse\StdLib\DataObject;

class CliCommandOptions extends DataObject
{
    protected $object = [
        'cwd'      => null,
        'env'      => [],
        'redirect' => '2>&1'
    ];
}
