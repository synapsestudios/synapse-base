<?php

namespace Synapse\Log\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\Debug\Exception\DummyException;
use Synapse\Stdlib\Arr;

/**
 * Wrapper for handlers to ignore Symfony DummyExceptions
 */
class DummyExceptionHandler implements HandlerInterface
{
    /**
     * Wrapped handler
     *
     * @var HandlerInterface
     */
    protected $handler;

    /**
     * Inject the wrapped handler on construction
     *
     * @param HandlerInterface $handler
     */
    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(array $record)
    {
        return $this->handler->isHandling($record);
    }

    /**
     * Wrap the handle function and only allow a record through if it is not a DummyException
     *
     * {@inheritDoc}
     */
    public function handle(array $record)
    {
        $e = Arr::path($record, 'context.exception');

        if ($e !== null and $e instanceof DummyException) {
            return false;
        }

        return $this->handler->handle($record);
    }

    /**
     * {@inheritDoc}
     */
    public function handleBatch(array $records)
    {
        return $this->handler->handleBatch($records);
    }

    /**
     * {@inheritDoc}
     */
    public function pushProcessor($callback)
    {
        return $this->handler->pushProcessor($callback);
    }

    /**
     * {@inheritDoc}
     */
    public function popProcessor()
    {
        return $this->handler->popProcessor();
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        return $this->handler->setFormatter($formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatter()
    {
        return $this->handler->getFormatter();
    }
}
