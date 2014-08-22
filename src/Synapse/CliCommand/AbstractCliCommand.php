<?php

namespace Synapse\CliCommand;

abstract class AbstractCliCommand
{
    protected $executed    = false;
    protected $output      = null;
    protected $returnCode  = null;

    protected $environment = null;
    protected $cwd         = null;

    protected $startTime   = null;
    protected $elapsedTime = null;

    abstract protected function getBaseCommand();

    public function setEnvironment(array $env = array())
    {
        $this->environment = $env;
        return $this;
    }

    public function setCwd($cwd)
    {
        $this->cwd = $cwd;
        return $this;
    }

    public function run()
    {
        $descriptors = array(
            // Stdin
            0 => array('pipe', 'r'),

            // Stdout
            1 => array('pipe', 'w'),
        );

        $this->startTime = microtime(true);

        $fd = proc_open($this->getCommand(), $descriptors, $pipes, $this->cwd, $this->environment);

        // Close the proc's stdin right away
        fclose($pipes[0]);

        // Read stdout
        $this->output = $this->parseOutput(stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        // Save exit status
        $this->returnCode = (int) trim(proc_close($fd));

        $this->elapsedTime = microtime(true) - $this->startTime;

        $this->executed = true;
    }

    public function executed()
    {
        return $this->executed;
    }

    public function successful()
    {
        return $this->returnCode === 0;
    }

    public function getOutput()
    {
        if ($this->executed !== true) {
            throw new \LogicException('Output is not available before command is executed');
        }

        return $this->output;
    }

    public function getExitStatus()
    {
        if ($this->executed !== true) {
            throw new \LogicException('Exit status is not available before command is executed');
        }

        return $this->returnCode;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getCwd()
    {
        return $this->cwd;
    }

    public function getCommand()
    {
        return trim(sprintf(
            '%s %s',
            $this->getBaseCommand(),
            $this->getRedirect()
        ));
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getElapsedTime()
    {
        return $this->elapsedTime;
    }

    protected function getRedirect()
    {
        return '2>&1';
    }

    protected function parseOutput($output)
    {
        $lines = explode("\n", $output);

        // This is the escape sequence that kills the stuff on the line, then
        // adds new text to it. The fake lines are separated by CRs instead of
        // NLs. You can thank whoever invented Bash for that
        $escapeStr = "\x1b\x5b\x4b";

        $actualLines = array();
        foreach ($lines as $line) {
            if (stripos($line, $escapeStr) !== false) {
                // Explode on the CR and take the last item, as that is the
                // actual line we want to show
                $parts = explode("\r", $line);

                $actualLine = array_pop($parts);

                // Remove the escape sequence
                $actualLines[] = str_replace($escapeStr, '', $actualLine);
            } else {
                $actualLines[] = $line;
            }
        }

        return trim(implode("\n", $actualLines));
    }
}
