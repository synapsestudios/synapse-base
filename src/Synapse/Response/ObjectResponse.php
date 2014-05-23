<?php

namespace Synapse\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * A response whose data comes from an object implementing
 * ArraySerializableInterface.
 */
class ObjectResponse extends JsonResponse
{
    /**
     * An entity or entity iterator.
     *
     * @var mixed
     */
    protected $object;

    /**
     * Constructor.
     *
     * @param ArraySerializableInterface  $data  The response data as an object implementing
     *                                           ArraySerializableInterface
     * @param integer $status                    The response status code
     * @param array   $headers                   An array of response headers
     */
    public function __construct(ArraySerializableInterface $data = null, $status = 200, $headers = array())
    {
        $this->object = $data;

        parent::__construct($data->getArrayCopy(), $status, $headers);
    }

    /**
     * Get the object from which the response data was retrieved.
     *
     * @return ArraySerializableInterface
     */
    public function getObject()
    {
        return $this->object;
    }
}
