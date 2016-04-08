<?php

namespace Synapse\Email;

use Http\Adapter\Guzzle6\Client as HttpClient;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Synapse\Stdlib\Arr;
use Mailgun\Mailgun;
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

        $app['email-sender.mandrill'] = $app->share(function (Application $app) {
            $emailConfig = $app['config']->load('email');

            if (! $apiKey = Arr::path($emailConfig, 'sender.mandrill.apiKey')) {
                return;
            }

            $sender = new MandrillSender(
                new Mandrill($apiKey),
                $app['email.mapper']
            );

            $sender->setConfig($emailConfig);

            return $sender;
        });

        $app['email-sender.mailgun'] = $app->share(function (Application $app) {
            $emailConfig = $app['config']->load('email');

            if (! $apiKey = Arr::path($emailConfig, 'sender.mailgun.apiKey')) {
                return;
            }

            $sender = new MailgunSender(
                new Mailgun($apiKey, new HttpClient()),
                $app['email.mapper'],
                $app['handlebars']
            );

            $sender->setConfig($emailConfig);

            return $sender;
        });

        $app['email.sender'] = $app->share(function ($app) {
            return $app['email-sender.mailgun'];
        });

        $app['email.send'] = $app->share(function (Application $app) {
            $command = new SendEmailCommand('email:send');

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
        $app->command('email.send');
    }
}
