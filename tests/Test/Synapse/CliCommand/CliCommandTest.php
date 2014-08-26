<?php

namespace Test\Synapse\CliCommand;

use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\AbstractCliCommand;
use Synapse\CliCommand\CliCommandOptions;
use Synapse\CliCommand\CliCommandResponse;

use Test\Synapse\CliCommand\CliCommand;

class CliCommandTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->setUpExecutor();
        $this->command = new CliCommand($this->executor);
        $this->options = new CliCommandOptions;
    }

    public function setUpExecutor()
    {
        $this->executor = $this->getMockBuilder('Synapse\CliCommand\CliCommandExecutor')
                             ->disableOriginalConstructor()
                             ->getMock();
    }

    public function createResponseObject($output, $returnCode)
    {
        return new CliCommandResponse([
            'output'      => $output,
            'return_code' => $returnCode,
        ]);
    }

    public function testCommandGivesExpectedResponse()
    {
        $command  = 'pwd foo bar=baz 9999 2>&1';
        $expected = '/current/working/directory';

        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo($command),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject($expected, 0)
            ));

        $this->command->setBaseCommand('pwd foo bar=baz 9999');

        $response = $this->command->run();

        $this->assertEquals($expected, (string) $response->getOutput());
        $this->assertEquals(true, $response->getSuccessful());
        $this->assertEquals(0, (int) $response->getReturnCode());
        $this->assertNotEmpty($response->getStartTime());
        $this->assertNotEmpty($response->getElapsedTime());
    }

    public function testCommandErrorGivesExpectedResponse()
    {
        $command  = 'stat /foo 2>&1';
        $expected = 'stat: /foo: stat: No such file or directory';

        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo($command),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject($expected, 1)
            ));

        $this->command->setBaseCommand('stat /foo');

        $response = $this->command->run();

        $this->assertEquals($command, (string) $response->getCommand());
        $this->assertEquals($expected, (string) $response->getOutput());
        $this->assertEquals(false, $response->getSuccessful());
        $this->assertEquals(1, (int) $response->getReturnCode());
    }

    public function testCommandRunsInCorrectDirectory()
    {
        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('pwd 2>&1'),
                $this->equalTo('/tmp'),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject('/tmp', 0)
            ));

        $this->command->setBaseCommand('pwd');
        $this->options->exchangeArray(['cwd' => '/tmp']);

        $response = $this->command->run($this->options);

        $this->assertEquals('/tmp', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectEnvironment()
    {
        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('printenv TEST_ENV 2>&1'),
                $this->equalTo(null),
                $this->equalTo(['TEST_ENV' => 'test_env'])
            )
            ->will($this->returnValue(
                $this->createResponseObject('test_env', 0)
            ));

        $this->command->setBaseCommand('printenv TEST_ENV');
        $this->options->exchangeArray([
            'env' => ['TEST_ENV' => 'test_env'],
        ]);

        $response = $this->command->run($this->options);

        $this->assertEquals('test_env', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectRedirect()
    {
        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('stat /foo'),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject('', 1)
            ));

        $this->command->setBaseCommand('stat /foo');
        $this->options->exchangeArray(['redirect' => '']);

        $response = $this->command->run($this->options);

        $this->assertEquals('', (string) $response->getOutput());
    }
}
