<?php

namespace Synapse\Log\Formatter;

use Monolog\Formatter\LineFormatter;
use Exception;

/**
 * Line formatter modified to output stack trace of exceptions
 */
class ExceptionLineFormatter extends LineFormatter
{
    const STACKTRACE_PLACEHOLDER = '%context.stacktrace%';

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = $this->normalize($record);

        $output = $this->format;
        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->convertToString($val), $output);
                unset($vars['extra'][$var]);
            }
        }
        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->convertToString($val), $output);
            }

            if ($var === 'context' && isset($val['exception'])) {
                $output = str_replace(self::STACKTRACE_PLACEHOLDER, ($val['exception']), $output);
            }
        }

        // If no stacktrace was provided, remove the placeholder
        $output = str_replace(self::STACKTRACE_PLACEHOLDER, '', $output);

        // If the output is just a newline, make it an empty string
        if ($output === PHP_EOL) {
            $output = '';
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalize($data)
    {
        if ($data instanceof Exception) {
            return $this->normalizeException($data);
        }

        return parent::normalize($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeException(Exception $e)
    {
        $message = 'Stack Trace: '.PHP_EOL;
        $message .= sprintf(
            'in %s on line %s',
            $e->getFile(),
            $e->getLine()
        );
        $message .= PHP_EOL.$e->getTraceAsString();

        return $message;
    }
}
