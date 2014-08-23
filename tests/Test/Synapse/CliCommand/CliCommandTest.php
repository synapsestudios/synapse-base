<?php

namespace Test\Synapse\CliCommand;

use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\AbstractCliCommand;
use Synapse\CliCommand\CliCommand;
use Synapse\CliCommand\CliCommandExecutor;
use Synapse\CliCommand\CliCommandOptions;

class CliCommandTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->setUpMock();
        $this->command = new CliCommand($this->mock);
        $this->options = new CliCommandOptions;
    }

    public function setUpMock()
    {
        $this->mock = $this->getMockBuilder('Synapse\CliCommand\CliCommandExecutor')
                             ->disableOriginalConstructor()
                             ->getMock();
    }

    public function testCommandBuildsArgumentsAndGivesExpectedResponse()
    {
        $expectedCommand = 'echo foo bar=baz 9999 a=b 2>&1';

        $this->mock
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo($expectedCommand),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue([
                'foo bar=baz 9999 a=b',
                0,
            ]));

        $arguments = [
            'foo',
            ['bar', 'baz'],
            9999,
            ['a', 'b', 'c'],
            [null, 'foo'],
            ['bar', null],
            new \StdClass,
            ['foo'],
            [],
        ];

        $this->command->setBaseCommand('echo', $arguments);

        $response = $this->command->run();

        $this->assertEquals($expectedCommand, (string) $response->getCommand());
        $this->assertEquals(true, $response->getSuccessfull());
        $this->assertEquals('foo bar=baz 9999 a=b', (string) $response->getOutput());
        $this->assertEquals(0, (int) $response->getReturnCode());
        $this->assertNotEmpty($response->getStartTime());
        $this->assertNotEmpty($response->getElapsedTime());
    }

    public function testCommandErrorGivesExpectedResponse()
    {
        $expectedCommand = 'stat /foo 2>&1';
        $expectedOutput  = 'stat: cannot stat `/foo\': No such file or directory';

        $this->mock
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo($expectedCommand),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue([
                $expectedOutput,
                1,
            ]));

        $this->command->setBaseCommand('stat', ['/foo']);

        $response = $this->command->run();

        $this->assertEquals($expectedCommand, (string) $response->getCommand());
        $this->assertEquals($expectedOutput, (string) $response->getOutput());
        $this->assertEquals(1, (int) $response->getReturnCode());
        $this->assertEquals(false, $response->getSuccessfull());
    }

    public function testCommandRunsInCorrectDirectory()
    {
        $this->mock
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('pwd 2>&1'),
                $this->equalTo('/tmp'),
                $this->equalTo(null)
            )
            ->will($this->returnValue([
                '/tmp',
                0,
            ]));

        $this->command->setBaseCommand('pwd');
        $this->options->exchangeArray(['cwd' => '/tmp']);

        $response = $this->command->run($this->options);

        $this->assertEquals('/tmp', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectEnvironment()
    {
        $this->mock
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('echo $TEST_ENV 2>&1'),
                $this->equalTo(null),
                $this->equalTo(['TEST_ENV' => 'test_env'])
            )
            ->will($this->returnValue([
                'test_env',
                0,
            ]));

        $this->command->setBaseCommand('echo', ['$TEST_ENV']);
        $this->options->exchangeArray([
            'env' => ['TEST_ENV' => 'test_env'],
        ]);

        $response = $this->command->run($this->options);

        $this->assertEquals('test_env', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectRedirect()
    {
        $this->mock
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('stat /foo'),
                $this->equalTo(null),
                $this->equalTo(null)
            )
            ->will($this->returnValue([
                '',
                1,
            ]));

        $this->command->setBaseCommand('stat', ['/foo']);
        $this->options->exchangeArray(['redirect' => '']);

        $response = $this->command->run($this->options);

        $this->assertEquals('', (string) $response->getOutput());
        $this->assertEquals(1, (int) $response->getReturnCode());
    }

    public function testCommandLockedOptions()
    {
        $this->mock
            ->expects($this->any())
            ->method('execute')
            ->with(
                $this->equalTo('pwd 2>&1'),
                $this->equalTo('/tmp'),
                $this->equalTo(['TEST_ENV' => 'test_env'])
            )
            ->will($this->returnValue([
                '/tmp',
                0,
            ]));

        $this->command = new CliCommandLockedOptions($this->mock);
        $this->options->exchangeArray([
            'cwd'      => null,
            'env'      => ['TEST_EVN' => 'env_broken'],
            'redirect' => '1> /dev/null',
        ]);

        $response = $this->command->run($this->options);

        $this->assertEquals('/tmp', (string) $response->getOutput());
    }
}

class CliCommandLockedOptions extends AbstractCliCommand
{
    protected $lockedOptions = [
        'cwd'      => '/tmp',
        'env'      => ['TEST_ENV' => 'test_env'],
        'redirect' => '2>&1',
    ];

    protected function getBaseCommand()
    {
        return 'pwd';
    }
}
