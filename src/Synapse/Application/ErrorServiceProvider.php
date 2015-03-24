<?php

namespace Synapse\Application;

use Silex\ServiceProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Synapse\Rest\Exception\MethodNotImplementedException;
use Synapse\Controller\BadRequestException;
use Exception;

/**
 * Define routes
 */
class ErrorServiceProvider implements ServiceProviderInterface
{
    /**
     * Message for 501 responses
     */
    const METHOD_NOT_IMPLEMENTED_MESSAGE = 'Method not implemented';

    /**
     * Message for exceptions if not in debug mode
     */
    const SERVER_ERROR_MESSAGE = 'Something went wrong with your request';

    /**
     * Register error handlers on the application.
     * Has entries for both Synapse's and Symfony's 501 exceptions so that both return the same response.
     *
     * @param Application $app
     */
    public function register(Application $app)
    {
        $getCorsResponse = function ($message, $statusCode) use ($app) {
            $response = new JsonResponse(['message' => $message], $statusCode);

            $app['cors']($app['request'], $response);

            return $response;
        };

        $app->error(function (MethodNotImplementedException $e, $code) use ($getCorsResponse) {
            return $getCorsResponse(self::METHOD_NOT_IMPLEMENTED_MESSAGE, 501);
        });

        $app->error(function (MethodNotAllowedHttpException $e, $code) use ($getCorsResponse) {
            return $getCorsResponse(self::METHOD_NOT_IMPLEMENTED_MESSAGE, 501);
        });

        $app->error(function (NotFoundHttpException $e, $code) use ($getCorsResponse) {
            return $getCorsResponse('Not found', 404);
        });

        $app->error(function (AccessDeniedHttpException $e, $code) use ($getCorsResponse) {
            return $getCorsResponse('Access denied', 403);
        });

        $app->error(function (BadRequestException $e, $code) use ($getCorsResponse) {
            return $getCorsResponse('Could not parse json body', 400);
        });

        $app->error(function (Exception $e, $code) use ($app) {
            $app['log']->addError($e->getMessage(), ['exception' => $e]);

            $debug = $app['config']->load('init')['debug'];

            if ($debug) {
                $responseBody = [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTrace(),
                ];

                $response = new JsonResponse($responseBody, 500);
            } else {
                $response = $getCorsResponse(self::SERVER_ERROR_MESSAGE, 500);
            }

            $app['cors']($app['request'], $response);

            return $response;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        // Noop
    }
}
