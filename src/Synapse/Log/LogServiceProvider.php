<?php

namespace Synapse\Log;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Monolog\Logger;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
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

        $handlers = $this->getHandlers($app);
        $app['log'] = $app->share(function ($app) use ($handlers) {
            return new Logger('main', $handlers);
        });

        $app->initializer('Synapse\\Log\\LoggerAwareInterface', function ($object, $app) {
            $object->setLogger($app['log']);
            return $object;
        });
    }

    /**
     * Perform extra chores on boot
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
     * @param  Application $app
     * @return  array
     */
    protected function getHandlers(Application $app)
    {
        $handlers = [];

        // File Handler
        $file = Arr::path($this->config, 'file.path');

        if ($file) {
            $handlers[] = $this->getFileHandler($file);
            $handlers[] = $this->getFileExceptionHandler($file);
        }

        // Loggly Handler
        $enableLoggly = Arr::path($this->config, 'loggly.enable');

        if ($enableLoggly) {
            $handlers[] = $this->getLogglyHandler();
        }

        // Rollbar Handler
        $enableRollbar = Arr::path($this->config, 'rollbar.enable');

        if ($enableRollbar) {
            $handlers[] = $this->getRollbarHandler($app['environment']);
        }

        // Syslog Handler
        $syslogIdent = Arr::path($this->config, 'syslog.ident');

        if ($syslogIdent) {
            $handlers[] = $this->getSyslogHandler($syslogIdent);
        }

        return $handlers;
    }

    /**
     * Create and return a syslog handler
     *
     * @param  string $ident
     * @return SyslogHandler
     */
    protected function getSyslogHandler($ident)
    {
        $handler = new SyslogHandler($ident, LOG_LOCAL0);
        return $handler;
    }

    /**
     * Log handler for files
     *
     * @param  string      $file Path of log file
     * @return DummyExceptionHandler
     */
    protected function getFileHandler($file)
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
     * @return StreamHandler
     */
    protected function getFileExceptionHandler($file)
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
    protected function getLogglyHandler()
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
    protected function getRollbarHandler($environment)
    {
        $rollbarConfig = Arr::get($this->config, 'rollbar', []);

        $token = Arr::get($rollbarConfig, 'post_server_item_access_token');

        if (! $token) {
            throw new ConfigException('Rollbar is enabled but the post server item access token is not set.');
        }

        $rollbarNotifier = new RollbarNotifier([
            'access_token' => $token,
            'environment'  => $environment,
            'batch'        => false,
            'root'         => Arr::get($rollbarConfig, 'root')
        ]);

        return new RollbarHandler(
            $rollbarNotifier,
            Logger::ERROR
        );
    }
}
