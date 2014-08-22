<?php

namespace Test\Synapse\CliCommand;

use Exception;
use stdClass;
use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\CliCommandOptions;

class AbstractCliCommandTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->options = new CliCommandOptions([
            'cwd' => '/home/vagrant',
            'env' => ['TEST_ENV' => 'test_env'],
        ]);

        $this->command     = new CliCommand();
        $this->failCommand = new CliFailCommand();
    }

    public function testRunCommandGivesExpectedOutput()
    {
        $response = $this->command->run($this->options);

        $this->assertEquals('success', (string) $response->getOutput());
    }

    public function testSuccessfullCommandGivesExpectedReturnCode()
    {
        $response = $this->command->run($this->options);

        $this->assertEquals(0, (string) $response->getReturnCode());
    }

    public function testFailedCommandGivesExpectedReturnCode()
    {
        $response = $this->failCommand->run($this->options);

        $this->assertEquals(127, (string) $response->getReturnCode());
    }
}
