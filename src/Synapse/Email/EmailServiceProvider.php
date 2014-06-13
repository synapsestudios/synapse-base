<?php

namespace Synapse\Email;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Synapse\Stdlib\Arr;
use Mandrill;

/**
 * Service provider for email related services
 */
class EmailServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['email.mapper'] = $app->share(function (Application $app) {
            return new EmailMapper($app['db'], new EmailEntity);
        });

        $app['email.service'] = $app->share(function (Application $app) {
            $service = new EmailService;

            $service->setEmailMapper($app['email.mapper'])
                ->setEmailConfig($app['config']->load('email'))
                ->setResque($app['resque']);

            return $service;
        });

        $app['email.sender'] = $app->share(function (Application $app) {
            $emailConfig = $app['config']->load('email');

            if (! $apiKey = Arr::path($emailConfig, 'sender.mandrill.apiKey')) {
                return;
            }

            return new MandrillSender(
                new Mandrill($apiKey),
                $app['email.mapper']
            );
        });

        $app['email.send-proxy'] = $app->share(function (Application $app) {
            $command = new SendEmailCommandProxy('email:send');
            $command->setFactory($app->raw('email.send'))
                ->setApp($app);
            return $command;
        });

        $app['email.send'] = $app->share(function (Application $app) {
            $command = new SendEmailCommand();

            $command->setEmailMapper($app['email.mapper']);

            if ($app['email.sender']) {
                $command->setEmailSender($app['email.sender']);
            }

            return $command;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // Register command routes
        $app->command('email.send-proxy');
    }
}
