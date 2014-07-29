<?php

namespace Synapse\Controller;

use Synapse\Application\UrlGeneratorAwareInterface;
use Synapse\Application\UrlGeneratorAwareTrait;
use Synapse\Debug\DebugModeAwareInterface;
use Synapse\Debug\DebugModeAwareTrait;
use Synapse\Log\LoggerAwareInterface;
use Synapse\Log\LoggerAwareTrait;
use Synapse\Response\EntityResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Abstract controller defining universal helper methods
 */
abstract class AbstractController implements UrlGeneratorAwareInterface, LoggerAwareInterface, DebugModeAwareInterface
{
    use UrlGeneratorAwareTrait, LoggerAwareTrait, DebugModeAwareTrait;

    /**
     * Create and return a 404 response object
     *
     * @return Response
     */
    public function createNotFoundResponse()
    {
        $response = new Response();
        $response->setStatusCode(404);
        $response->setContent('Not found');
        return $response;
    }

    /**
     * Create and return an error response object
     *
     * @param  integer $code    HTTP response code
     * @param  string  $content Response content
     * @return Response
     */
    protected function createSimpleResponse($code = 500, $content = 'Unknown error')
    {
        $response = new Response;
        $response->setStatusCode($code)
            ->setContent($content);

        return $response;
    }

    /**
     * Create and return a JSON response object
     *
     * @param  array $data  Response data
     * @param  int $code    HTTP response code
     * @return JsonResponse
     */
    protected function createJsonResponse($data, $code = 200)
    {
        $response = new JsonResponse($data, $code);
        return $response;
    }

    /**
     * Create a response from an ArraySerializable object.
     *
     * @param  ArraySerializableInterface $object
     * @param  int                        $code
     * @return EntityResponse
     */
    protected function createEntityResponse(ArraySerializableInterface $object, $code = 200)
    {
        $response = new EntityResponse($object, $code);
        return $response;
    }

    /**
     * Create a response for constraint violations
     *
     * Response has a list of errors that looks like this:
     * {
     *     "errors" : {
     *         "current_password" : [
     *             "This field is expected"
     *         ],
     *         "field_2" : [
     *             "This field cannot be the same as current_password",
     *             "This field must be less than 5 characters long"
     *         ]
     *     }
     * }
     *
     * @param  ConstraintViolationListInterface $violationList
     * @return JsonResponse
     */
    protected function createConstraintViolationResponse(ConstraintViolationListInterface $violationList)
    {
        $errors = [];

        foreach ($violationList as $violation) {
            $field = $violation->getPropertyPath();
            $field = str_replace(
                ['[', ']'],
                '',
                $field
            );

            $errors[$field][] = $violation->getMessage();
        }

        return $this->createJsonResponse(
            ['errors' => $errors],
            422
        );
    }
}
