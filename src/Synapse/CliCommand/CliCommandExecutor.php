<?php

namespace Synapse\CliCommand;

class CliCommandExecutor implements CliCommandExecutorInterface
{
    protected $descriptors = [
        0 => ['pipe', 'r'], // Stdin
        1 => ['pipe', 'w'], // Stdout
    ];

    public function execute($command, $cwd, $env)
    {
        $fd = proc_open(
            $command,
            $this->descriptors,
            $pipes,
            $cwd,
            $env
        );

        // Close the proc's stdin right away
        fclose($pipes[0]);

        // Read stdout
        $output = $this->parseOutput(stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        $returnCode = (int) trim(proc_close($fd));

        return [$output, $returnCode];
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
