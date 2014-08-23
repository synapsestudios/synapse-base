<?php

namespace Test\Synapse\CliCommand;

use PHPUnit_Framework_TestCase;

use Synapse\CliCommand\CliCommand;
use Synapse\CliCommand\CliCommandOptions;

class CliCommandTest extends PHPUnit_Framework_TestCase
{
    public function testCommandArgumentsRenderCorrectly()
    {
        $arguments = ['foo', '--bar', ['--baz', '9999'], 9999];
        $command   = new CliCommand('echo', $arguments);
        $response  = $command->run();

        $this->assertEquals('foo --bar --baz=9999 9999', $response->getOutput());
    }

    public function testCommandIgnoresBadArguments()
    {
        $arguments = [['foo', 'bar', 'baz'], [null, '9999'], new \StdClass];
        $command   = new CliCommand('echo', $arguments);
        $response  = $command->run();

        $this->assertEquals('foo=bar', $response->getOutput());
    }

    public function testEchoCommandGivesExpectedResponse()
    {
        $command  = new CliCommand('echo', ['success']);
        $response = $command->run();

        $this->assertEquals('echo success 2>&1', (string) $response->getCommand());
        $this->assertEquals('success', (string) $response->getOutput());
        $this->assertEquals(0, (int) $response->getReturnCode());
        $this->assertEquals(true, $response->getSuccessfull());
    }

    public function testErrorCommandGivesExpectedResponse()
    {
        $command  = new CliCommand('stat', ['/foo']);
        $response = $command->run();

        $output = 'stat: cannot stat `/foo\': No such file or directory';

        $this->assertEquals('stat /foo 2>&1', (string) $response->getCommand());
        $this->assertEquals($output, (string) $response->getOutput());
        $this->assertEquals(1, (int) $response->getReturnCode());
        $this->assertEquals(false, $response->getSuccessfull());
    }

    public function testCommandRunsInCorrectDirectory()
    {
        $command  = new CliCommand('pwd');
        $options  = new CliCommandOptions(['cwd' => '/tmp']);
        $response = $command->run($options);

        $this->assertEquals('/tmp', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectEnvironment()
    {
        $command = new CliCommand('echo', ['$TEST_ENV']);
        $options = new CliCommandOptions([
            'env' => ['TEST_ENV' => 'test_env'],
        ]);
        $response = $command->run($options);

        $this->assertEquals('test_env', (string) $response->getOutput());
    }

    public function testCommandRunsWithCorrectRedirect()
    {
        $command = new CliCommand('stat', ['/foo']);
        $options = new CliCommandOptions([
            'redirect' => '',
        ]);
        $response = $command->run($options);

        $this->assertEquals('', (string) $response->getOutput());
        $this->assertEquals(1, (int) $response->getReturnCode());
    }

    public function testCommandLockedOptions()
    {
        $command = new CliCommandLockedOptions('pwd');
        $options = new CliCommandOptions([
            'cwd'      => null,
            'env'      => ['TEST_EVN' => 'env_broken'],
            'redirect' => '1> /dev/null',
        ]);
        $pwdResponse = $command->run($options);

        $command->setBaseCommand('echo', ['$TEST_ENV']);
        $envResponse = $command->run($options);

        $this->assertEquals('/tmp', (string) $pwdResponse->getOutput());
        $this->assertEquals('test_env', (string) $envResponse->getOutput());
    }
}

class CliCommandLockedOptions extends CliCommand
{
    protected $lockedOptions = [
        'cwd'      => '/tmp',
        'env'      => ['TEST_ENV' => 'test_env'],
        'redirect' => '2>&1',
    ];
}
