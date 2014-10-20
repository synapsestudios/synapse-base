<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Synapse\Stdlib\Arr;
use stdClass;

abstract class CommandTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Child classes should set this to the command to be tested in setUp
     *
     * @var Symfony\Component\Console\Command\Command
     */
    public $command;

    /**
     * This should be called in setUp in child classes
     */
    public function setUp()
    {
        $this->captured = new stdClass;

        $this->setUpMockOutput();
    }

    public function setUpMockOutput()
    {
        $this->mockOutput = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock();

        $this->captured->outputWrittenToConsole = [];

        $outputCapturer = function ($message) {
            $this->captured->outputWrittenToConsole[] = $message;
        };

        $this->mockOutput->expects($this->any())
            ->method('write')
            ->will($this->returnCallback($outputCapturer));

        $this->mockOutput->expects($this->any())
            ->method('writeln')
            ->will($this->returnCallback($outputCapturer));
    }

    public function setMockInputWithArguments(array $args = [])
    {
        $this->mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\ArrayInput')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockInput->expects($this->any())
            ->method('getArguments')
            ->will($this->returnValue($args));

        $getter = function ($field) use ($args) {
            return Arr::get($args, $field);
        };

        $this->mockInput->expects($this->any())
            ->method('getArgument')
            ->will($this->returnCallback($getter));
    }

    /**
     * Call this with an array of arguments to pass in via the Input object
     *
     * @param  array $args Array of arguments to inject into the mock Input object before executing
     */
    public function executeCommand($args = [])
    {
        $this->setMockInputWithArguments($args);

        return $this->command->run($this->mockInput, $this->mockOutput);
    }
}
