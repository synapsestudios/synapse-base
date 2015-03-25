<?php

namespace Synapse\Application;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Synapse\Application;
use Synapse\Rest\Exception\MethodNotImplementedException;
use Exception;

/**
 * Define routes
 */
class Routes implements RoutesInterface
{
    /**
     * Message for 501 responses
     */
    const METHOD_NOT_IMPLEMENTED_MESSAGE = 'Method not implemented';

    /**
     * {@inheritDoc}
     *
     * Has entries for both Synapse's and Symfony's 501 exceptions so that both return the same response
     *
     * @param  Application $app
     */
    public function define(Application $app)
    {
        $routes = $this;

        $getCorsResponse = function ($message, $statusCode) use ($app, $routes) {
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

        $app->error(function (Exception $e, $code) use ($getCorsResponse, $app) {
            $app['log']->addError($e->getMessage(), ['exception' => $e]);

            if ($app['debug'] === true) {
                throw $e;
            }

            return $getCorsResponse('Something went wrong with your request', 500);
        });
    }
}
