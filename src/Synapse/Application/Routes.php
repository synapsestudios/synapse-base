<?php

namespace Synapse\Application;

use Symfony\Component\HttpFoundation\JsonResponse;
use Synapse\Application;

/**
 * Define routes
 */
class Routes implements RoutesInterface
{
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

        $app->error(function (\Synapse\Rest\Exception\MethodNotImplementedException $e, $code) use ($routes) {
            return $routes->getMethodNotImplementedResponse();
        });

        $app->error(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $code) use ($routes) {
            return $routes->getMethodNotImplementedResponse();
        });

        $app->error(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $code) {
            return new JsonResponse(['message' => 'Not found'], 404);
        });

        $app->error(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $code) {
            return new JsonResponse(['message' => 'Access denied'], 403);
        });

        $app->error(function (\Exception $e, $code) use ($app) {
            $app['log']->addError($e->getMessage(), ['exception' => $e]);

            if ($app['debug'] === true) {
                throw $e;
            }

            return new JsonResponse(['message' => 'Something went wrong with your request'], 500);
        });
    }

    /**
     * Return a JSON 501 response
     *
     * @return JsonResponse
     */
    protected function getMethodNotImplementedResponse()
    {
        return new JsonResponse(['message' => 'Method not implemented'], 501);
    }
}
