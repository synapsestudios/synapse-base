<?php

namespace Synapse\CliCommand;

interface CliCommandExecutorInterface
{
    public function execute($command, $cwd, $env);
}
