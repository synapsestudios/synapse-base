<?php

namespace Synapse\Validator;

use Silex\ServiceProviderInterface;
use Silex\Application;

class ValidatorServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['validation-error.formatter'] = $app->share(function ($app) {
            return new ValidationErrorFormatter();
        });

        $app->initializer(
            'Synapse\Validator\ValidationErrorFormatterAwareInterface',
            function ($object, $app) {
                $object->setValidationErrorFormatter($app['validation-error.formatter']);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        // Noop
    }
}
