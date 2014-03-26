<?php

namespace Synapse\Controller;

use Synapse\Application\UrlGeneratorAwareInterface;
use Synapse\Application\UrlGeneratorAwareTrait;

use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract controller defining universal helper methods
 */
abstract class AbstractController implements UrlGeneratorAwareInterface
{
    use UrlGeneratorAwareTrait;

    public function createNotFoundResponse()
    {
        $response = new Response();
        $response->setStatusCode(404);
        $response->setContent('Not found');
        return $response;
    }

    protected function getSimpleResponse($code = 500, $content = 'Unknown error')
    {
        $response = new Response;
        $response->setStatusCode($code)
            ->setContent($content);

        return $response;
    }
}
