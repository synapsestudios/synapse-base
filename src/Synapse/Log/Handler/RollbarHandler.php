<?php

namespace Synapse\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use RollbarNotifier;
use Exception;
use Synapse\Config\Exception as ConfigException;
use Synapse\Stdlib\Arr;

/**
 * Sends errors to Rollbar
 *
 * @author Paul Statezny <paulstatezny@gmail.com>
 */
class RollbarHandler extends AbstractProcessingHandler
{
    /**
     * Rollbar notifier
     *
     * @var RollbarNotifier
     */
    protected $rollbarNotifier;

    /**
     * @param RollbarNotifier  $rollbarNotifier RollbarNotifier object constructed with valid token
     * @param string           $environment
     * @param boolean          $bubble          Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($config, $environment, $bubble = true)
    {
        $token = Arr::get($config, 'post_server_item_access_token');

        if (! $token) {
            throw new ConfigException('Rollbar is enabled but the post server item access token is not set.');
        }

        $this->rollbarNotifier = new RollbarNotifier([
            'access_token' => $token,
            'environment'  => $environment,
            'batched'      => false,
            'root'         => Arr::get($config, 'root')
        ]);

        $level = Arr::path($config, 'level') ?: Logger::ERROR;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Exception) {
            $this->rollbarNotifier->report_exception($record['context']['exception']);
        } else {
            $extraData = array(
                'level' => $record['level'],
                'channel' => $record['channel'],
                'datetime' => $record['datetime']->format('U'),
            );

            $this->rollbarNotifier->report_message(
                $record['message'],
                $record['level_name'],
                array_merge($record['context'], $record['extra'], $extraData)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->rollbarNotifier->flush();
    }
}
