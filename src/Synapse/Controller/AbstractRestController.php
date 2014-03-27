<?php

namespace Synapse\Controller;

use RuntimeException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Synapse\Rest\Exception\MethodNotImplementedException;

/**
 * Abstract rest controller. Allows children to simply set get(), post(),
 * put(), and/or delete() methods.
 */
abstract class AbstractRestController extends AbstractController
{
    protected $content;

    /**
     * Silex hooks into REST controllers here
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function execute(Request $request)
    {
        $method = $request->getMethod();

        if (!method_exists($this, $method)) {
            throw new MethodNotImplementedException(
                sprintf(
                    'HTTP method "%s" has not been implemented in class "%s"',
                    $method,
                    get_class($this)
                )
            );
        }

        $this->content = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->getSimpleResponse(400, 'Could not parse json body');
        }

        $result = $this->{$method}($request);

        if ($result instanceof Response) {
            return $result;
        } elseif (is_array($result)) {
            return new JsonResponse($result);
        } else {
            throw new RuntimeException('Unhandled response type from controller');
        }
    }
}
