<?php

namespace Synapse\CliCommand;

abstract class AbstractCliCommand
{
    abstract protected function getBaseCommand();

    public function run(CliCommandOptions $options)
    {
        $descriptors = [
            // Stdin
            0 => ['pipe', 'r']

            // Stdout
            1 => ['pipe', 'w'],
        ];

        $response = [];

        $this->startTime = microtime(true);

        $fd = proc_open($this->getCommand($options), $descriptors, $pipes, $options->getCwd(), $options->getEnv());

        // Close the proc's stdin right away
        fclose($pipes[0]);

        // Read stdout
        $response['output'] = $this->parseOutput(stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        // Save exit status
        $response['returnCode']  = (int) trim(proc_close($fd));
        $response['elapsedTime'] = microtime(true) - $this->startTime;
        $response['executed']    = true;
        $response['successful']  = $response['returnCode'] === 0;

        return new CliCommandReponse($response);
    }

    public function getCommand(CliCommandOptions $options)
    {
        return trim(sprintf(
            '%s %s',
            $this->getBaseCommand(),
            $options->getRedirect()
        ));
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
