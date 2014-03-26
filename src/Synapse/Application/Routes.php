<?php

namespace Synapse\Application;

use Synapse\Application;

/**
 * Define routes
 */
class Routes implements RoutesInterface
{
    /**
     * {@inheritDoc}
     * @param  Application $app
     */
    public function define(Application $app)
    {
        $app->error(function (\Synapse\Rest\Exception\MethodNotImplementedException $e, $code) {
            $response = new Symfony\Component\HttpFoundation\Response('Method not implemented');
            $response->setStatusCode(501);
            return $response;
        });

        $app->error(function (\Exception $e, $code) use ($app) {
            $app['log']->addError($e->getMessage(), ['exception' => $e]);

            if ($app['debug'] === false) {
                return new \Symfony\Component\HttpFoundation\Response('Something went wrong with your request');
            } else {
                throw $e;
            }
        });
    }
}
