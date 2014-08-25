<?php

namespace Synapse\CliCommand;

abstract class AbstractCliCommand
{
    public function __construct($executor)
    {
        $this->executor = $executor;
    }

    abstract protected function getBaseCommand();

    protected function buildCommand(CliCommandOptions $options)
    {
        return trim(sprintf(
            '%s %s',
            $this->getBaseCommand(),
            $options->getRedirect()
        ));
    }

    public function run(CliCommandOptions $options = null)
    {
        $options = $options ?: new CliCommandOptions;

        $command   = $this->buildCommand($options);
        $startTime = microtime(true);

        $response = $this->executor->execute(
            $command,
            $options->getCwd(),
            $options->getEnv()
        );

        $elapsedTime = microtime(true) - $startTime;
        $output      = $response->getOutput();
        $returnCode  = $response->getReturnCode();

        return new CliCommandResponse([
            'command'      => $command,
            'elapsed_time' => $elapsedTime,
            'output'       => $output,
            'start_time'   => $startTime,
            'return_code'  => $returnCode,
            'successfull'  => $returnCode === 0,
        ]);
    }
}
