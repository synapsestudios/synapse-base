<?php

namespace Synapse\Controller;

use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Synapse\Response\EntityResponse;
use Synapse\Rest\Exception\MethodNotImplementedException;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Abstract rest controller. Allows children to simply set get(), post(),
 * put(), and/or delete() methods.
 */
abstract class AbstractRestController extends AbstractController
{
    const SERVER_ERROR_MESSAGE = 'An unknown error has occurred';

    /**
     * Request body content decoded from JSON
     *
     * @var mixed
     */
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

        try {
            $result = $this->{$method}($request);
        } catch (BadRequestException $e) {
            return $this->createSimpleResponse(400, 'Could not parse json body');
        } catch (Exception $e) {
            return $this->createErrorResponse($e);
        }

        if ($result instanceof ArraySerializableInterface) {
            return new EntityResponse($result);
        } elseif (is_array($result)) {
            return new JsonResponse($result);
        } elseif ($result instanceof Response) {
            return $result;
        } else {
            throw new RuntimeException(
                sprintf(
                    'Unhandled response type %s from controller',
                    gettype($result)
                )
            );
        }
    }

    /**
     * Get a JSON decoded array from the request content
     *
     * @param  Request $request
     * @return array
     */
    protected function getContentAsArray(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException();
        }

        if ($content === null) {
            return [];
        }

        return $content;
    }

    /**
     * Create a 500 response.  If debugging, it will contain the error message
     * and a stack trace.
     *
     * @return Response
     */
    protected function createErrorResponse(Exception $exception)
    {
        if ($this->logger) {
            $this->logger->addError(
                $exception->getMessage(),
                ['exception' => $exception]
            );
        }

        $responseData = [
            'error' => $this->debug ? $exception->getMessage() : self::SERVER_ERROR_MESSAGE
        ];

        if ($this->debug) {
            $responseData['trace'] = $exception->getTraceAsString();
        };

        return $this->createJsonResponse($responseData, 500);
    }
}
