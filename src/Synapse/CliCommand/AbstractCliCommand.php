<?php

namespace Synapse\CliCommand;

abstract class AbstractCliCommand
{
    protected $lockedOptions = [];

    public function __construct($executor)
    {
        $this->executor = $executor;
    }

    abstract protected function getBaseCommand();

    protected function buildCommand()
    {
        return trim(sprintf(
            '%s %s',
            $this->getBaseCommand(),
            $this->options->getRedirect()
        ));
    }

    protected function getOptions(CliCommandOptions $options = null)
    {
        $options = $options ?: new CliCommandOptions;

        $this->options = $options->exchangeArray($this->lockedOptions);
    }

    public function run(CliCommandOptions $options = null)
    {
        $this->getOptions($options);

        $response = new CliCommandResponse();
        $command  = $this->buildCommand();

        $response->setCommand($command);
        $response->setStartTime(microtime(true));

        $output = $this->executor->execute(
            $command,
            $this->options->getCwd(),
            $this->options->getEnv()
        );

        list($output, $returnCode) = $output;

        // Save output
        $response->setOutput($output);
        $response->setReturnCode($returnCode);
        $response->setElapsedTime(microtime(true) - $response->getStartTime());
        $response->setSuccessfull($response->getReturnCode() === 0);

        return $response;
    }
}
