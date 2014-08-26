<?php

namespace Synapse\CliCommand;

interface CliCommandExecutorInterface
{
    /**
     * Runs a shell command in a new process
     *
     * @param string $command the command to execute
     * @param  string $cwd the working directory to execute in
     * @param  array $env array of environment variables to ues
     * @return PDO connection to the database
     */
    public function execute($command = '', $cwd = null, array $env = null);
}
