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


    public function run(CliCommandOptions $options = null)
    {
        $options = $this->getOptions($options);

        $response = new CliCommandResponse();
        $command  = $this->buildCommand($options);

        $response->setCommand($command);
        $response->setStartTime(microtime(true));

        $output = $this->executor->execute(
            $command,
            $options->getCwd(),
            $options->getEnv()
        );

        list($output, $returnCode) = $output;

        // Save output
        $response->setOutput($output);
        $response->setReturnCode($returnCode);
        $response->setElapsedTime(microtime(true) - $response->getStartTime());
        $response->setSuccessfull($response->getReturnCode() === 0);

        return $response;
    }

    protected function buildCommand(CliCommandOptions $options)
    {
        return trim(sprintf(
            '%s %s',
            $this->getBaseCommand(),
            $options->getRedirect()
        ));
    }

    protected function getOptions(CliCommandOptions $options = null)
    {
        $options = $options ?: new CliCommandOptions;

        return $options->exchangeArray($this->lockedOptions);
    }
}
