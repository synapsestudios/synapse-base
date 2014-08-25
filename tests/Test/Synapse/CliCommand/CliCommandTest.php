<?php

namespace Test\Synapse\CliCommand;

use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\AbstractCliCommand;
use Synapse\CliCommand\CliCommand;
use Synapse\CliCommand\CliCommandOptions;
use Synapse\CliCommand\CliCommandResponse;

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

    /**
    * @dataProvider argumentsProvider
    */
    public function testArguments($arguments, $expected)
    {
        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                $this->createResponseObject('', 0)
            ));

        $this->command->setBaseCommand('pwd', $arguments);
        $expected = sprintf('%s %s %s', 'pwd', $expected, '2>&1');

        $response = $this->command->run();

        $this->assertEquals($expected, $response->getCommand());
    }

    public function argumentsProvider()
    {
        return [
            [[], ''],
            [[null], ''],
            [['foo'], '\'foo\''],
            [[9999], '\'9999\''],
            [['foo', 'bar'], '\'foo\' \'bar\''],
            [[['foo', 'bar']], '\'foo\'=\'bar\''],
            [[['foo', 9999]], '\'foo\'=\'9999\''],
            [[['foo', true]], '\'foo\'=true'],
            [[['foo', false]], '\'foo\'=false'],
            [[['foo', null]], '\'foo\'=NULL'],
            [[['foo', 'bar', 'baz']], '\'foo\'=\'bar\''],
            [[['foo', 'bar'], ['bar', 'baz']], '\'foo\'=\'bar\' \'bar\'=\'baz\''],
            [[['foo']], ''],
            [[], ''],
            [[new \StdClass], ''],
            [['$foo'], '\'$foo\''],
            [[[null, 'bar']], ''],
        ];
    }

    public function testCommandGivesExpectedResponse()
    {
        $expectedCommand = 'pwd \'foo\' \'bar\'=\'baz\' \'9999\' 2>&1';
        $expectedOutput  = '/current/working/directory';

        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo($expectedCommand),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject($expectedOutput, 0)
            ));

        $arguments = [
            'foo',
            ['bar', 'baz'],
            9999,
        ];

        $this->command->setBaseCommand('pwd', $arguments);

        $response = $this->command->run();

        $this->assertEquals($expectedCommand, (string) $response->getCommand());
        $this->assertEquals($expectedOutput, (string) $response->getOutput());
        $this->assertEquals(true, $response->getSuccessfull());
        $this->assertEquals(0, (int) $response->getReturnCode());
        $this->assertNotEmpty($response->getStartTime());
        $this->assertNotEmpty($response->getElapsedTime());
    }

    public function testCommandErrorGivesExpectedResponse()
    {
        $expectedCommand = 'stat \'/foo\' 2>&1';
        $expectedOutput  = 'stat: /foo: stat: No such file or directory';

        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo($expectedCommand),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject($expectedOutput, 1)
            ));

        $this->command->setBaseCommand('stat', ['/foo']);

        $response = $this->command->run();

        $this->assertEquals($expectedCommand, (string) $response->getCommand());
        $this->assertEquals($expectedOutput, (string) $response->getOutput());
        $this->assertEquals(false, $response->getSuccessfull());
        $this->assertEquals(1, (int) $response->getReturnCode());
    }

    public function testCommandRunsInCorrectDirectory()
    {
        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('pwd  2>&1'),
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
                $this->equalTo('printenv | grep TEST_ENV  2>&1'),
                $this->equalTo(null),
                $this->equalTo(['TEST_ENV' => 'test_env'])
            )
            ->will($this->returnValue(
                $this->createResponseObject('TEST_ENV=test_env', 0)
            ));

        $this->command->setBaseCommand('printenv | grep TEST_ENV');
        $this->options->exchangeArray([
            'env' => ['TEST_ENV' => 'test_env'],
        ]);

        $response = $this->command->run($this->options);

        $this->assertEquals('TEST_ENV=test_env', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectRedirect()
    {
        $this->executor
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('stat \'/foo\''),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue(
                $this->createResponseObject('', 1)
            ));

        $this->command->setBaseCommand('stat', ['/foo']);
        $this->options->exchangeArray(['redirect' => '']);

        $response = $this->command->run($this->options);

        $this->assertEquals('', (string) $response->getOutput());
    }
}
