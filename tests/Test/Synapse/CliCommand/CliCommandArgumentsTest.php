<?php

namespace Test\Synapse\CliCommand;

use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\CliCommandArguments;

class CliCommandArgumentsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->input = ['foo', '--bar', ['--baz', '9999'], 9999];
    }

    public function testArgumentsRenderCorrectly()
    {
        $arguments = new CliCommandArguments($this->input);

        $this->assertEquals('foo --bar --baz=9999 9999', $arguments->render());
    }
}
