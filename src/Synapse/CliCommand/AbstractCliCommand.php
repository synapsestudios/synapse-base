<?php

namespace Synapse\CliCommand;

abstract class AbstractCliCommand
{
    public function __construct(CliCommandExecutorInterface $executor)
    {
        $this->executor = $executor;
    }

    /**
     * Returns a string of the entire command to be executed
     * minus the output redirect
     *
     * @return string shell command plus parameters
     */
    abstract protected function getBaseCommand(CliCommandOptions $options);

    protected function buildCommand(CliCommandOptions $options)
    {
        return trim(sprintf(
            '%s %s',
            $this->getBaseCommand(),
            $options->getRedirect()
        ));
    }

    /**
     * Returns an options object to use during execution.
     * Override to return preset options of needed.
     *
     * @return CliCommandOptions options to use during execution
     */
    protected function getOptions(CliCommandOptions $options = null)
    {
        return $options ?: new CliCommandOptions;
    }

    /**
     * Executes a cli command
     *
     * @param  CliCommandOptions $options object of `cwd`, `env`, and `redirect`
     * @return CliCommandResponse object with output and comand information
     */
    public function run(CliCommandOptions $options = null)
    {
        $options   = $this->getOptions($options);
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
            'successful'   => $returnCode === 0,
        ]);
    }
}
