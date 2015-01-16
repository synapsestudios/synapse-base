<?php

namespace Synapse\Log;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Monolog\Logger;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\ErrorHandler as MonologErrorHandler;
use Synapse\Log\Handler\RollbarHandler;
use Synapse\Log\Handler\DummyExceptionHandler;
use Synapse\Log\Formatter\ExceptionLineFormatter;
use Synapse\Config\Exception as ConfigException;
use Synapse\Stdlib\Arr;
use RollbarNotifier;

/**
 * Service provider for logging services.
 *
 * Register application logger and injected log handlers.
 */
class LogServiceProvider implements ServiceProviderInterface
{
    /**
     * Log configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Register logging related services
     *
     * @param  Application $app Silex application
     */
    public function register(Application $app)
    {
        $this->config = $app['config']->load('log');

        $handlers = $this->getHandlers();
        $app['log'] = $app->share(function ($app) use ($handlers) {
            return new Logger('main', $handlers);
        });

        $app->initializer('Synapse\\Log\\LoggerAwareInterface', function ($object, $app) {
            $object->setLogger($app['log']);
            return $object;
        });
    }

    /**
     * Perform extra chores on boot (none needed here)
     *
     * @param  Application $app
     */
    public function boot(Application $app)
    {
        // Register Monolog error handler for fatal errors here because Symfony's handler overrides it
        $monologErrorHandler = new MonologErrorHandler($app['log']);

        $monologErrorHandler->registerErrorHandler();
        $monologErrorHandler->registerFatalHandler();
    }

    /**
     * Get an array of logging handlers to use
     *
     * @return  array
     */
    protected function getHandlers()
    {
        $handlers = [];

        // File Handler
        $file = Arr::path($this->config, 'file.path');

        if ($file) {
            $handlers[] = $this->fileHandler($file);
            $handlers[] = $this->fileExceptionHandler($file);
        }

        // Loggly Handler
        $enableLoggly = Arr::path($this->config, 'loggly.enable');

        if ($enableLoggly) {
            $handlers[] = $this->logglyHandler();
        }

        // Rollbar Handler
        $enableRollbar = Arr::path($this->config, 'rollbar.enable');

        if ($enableRollbar) {
            $handlers[] = $this->rollbarHandler($app['environment']);
        }

        return $handlers;
    }

    /**
     * Log handler for files
     *
     * @param  string      $file Path of log file
     * @return FileHandler
     */
    protected function fileHandler($file)
    {
        $format = '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'.PHP_EOL;

        $handler = new StreamHandler($file, Logger::INFO);
        $handler->setFormatter(new LineFormatter($format));

        return new DummyExceptionHandler($handler);
    }

    /**
     * Exception log handler for files
     *
     * @param  string      $file Path of log file
     * @return FileHandler
     */
    protected function fileExceptionHandler($file)
    {
        $format = '%context.stacktrace%'.PHP_EOL;

        $handler = new StreamHandler($file, Logger::ERROR);
        $handler->setFormatter(new ExceptionLineFormatter($format));

        return $handler;
    }

    /**
     * Log handler for Loggly
     *
     * @return LogglyHandler
     */
    protected function logglyHandler()
    {
        $token = Arr::path($this->config, 'loggly.token');

        if (! $token) {
            throw new ConfigException('Loggly is enabled but the token is not set.');
        }

        return new LogglyHandler($token, Logger::INFO);
    }

    /**
     * Register log handler for Rollbar
     *
     * @return RollbarHandler
     */
    protected function rollbarHandler($environment)
    {
        $rollbarConfig = Arr::get($this->config, 'rollbar', []);

        $token = Arr::get($rollbarConfig, 'post_server_item_access_token');

        if (! $token) {
            throw new ConfigException('Rollbar is enabled but the post server item access token is not set.');
        }

        $rollbarNotifier = new RollbarNotifier([
            'access_token' => $token,
            'environment'  => $environment,
            Arr::get($rollbarConfig, 'root')
        ]);

        return new RollbarHandler(
            $rollbarNotifier,
            Logger::ERROR
        );
    }
}
