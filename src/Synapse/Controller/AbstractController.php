<?php

namespace Synapse\Controller;

use Synapse\Application\UrlGeneratorAwareInterface as UrlGenInterface;
use Synapse\Application\UrlGeneratorAwareTrait;
use Synapse\Db\Transaction;
use Synapse\Db\TransactionAwareInterface;
use Synapse\Db\TransactionAwareTrait;
use Synapse\Debug\DebugModeAwareInterface as DebugInterface;
use Synapse\Debug\DebugModeAwareTrait;
use Synapse\Validator\ValidationErrorFormatterAwareInterface as ValidationInterface;
use Synapse\Validator\ValidationErrorFormatterAwareTrait;
use Synapse\Log\LoggerAwareInterface as LoggerInterface;
use Synapse\Log\LoggerAwareTrait;
use Synapse\Response\EntityResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Abstract controller defining universal helper methods
 */
abstract class AbstractController implements
    UrlGenInterface,
    LoggerInterface,
    DebugInterface,
    ValidationInterface,
    TransactionAwareInterface
{
    use UrlGeneratorAwareTrait,
        LoggerAwareTrait,
        DebugModeAwareTrait,
        ValidationErrorFormatterAwareTrait,
        TransactionAwareTrait;

    /**
     * Create and return a 404 response object
     *
     * @return Response
     */
    public function createNotFoundResponse()
    {
        return $this->createErrorResponse('Not found', 404);
    }

    /**
     * Create and return an error response with a standard format
     *
     * Response will be formatted as a JSON object with the format:
     * {
     *     "message" : "text"
     * }
     *
     * @param  string  $content Response content
     * @param  integer $code    HTTP response code
     * @return Response
     */
    protected function createErrorResponse($content = 'Unknown Error', $code = 500)
    {
        $data = [
            'message' => $content
        ];

        $response = $this->createJsonResponse($data, $code);
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
     * @param  ConstraintViolationListInterface $violationList
     * @return JsonResponse
     */
    protected function createConstraintViolationResponse(ConstraintViolationListInterface $violationList)
    {
        $errors = $this->validationErrorFormatter->groupViolationsByField($violationList);

        return $this->createJsonResponse(
            ['errors' => $errors],
            422
        );
    }

    /**
     * Create and return a 204 response with an empty string as the body
     *
     * @return Response
     */
    protected function create204Response()
    {
        return $this->createSimpleResponse(204, '');
    }
}
