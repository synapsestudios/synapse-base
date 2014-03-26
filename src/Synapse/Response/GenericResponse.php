<?php

namespace Synapse\Response;

use Symfony\Component\HttpFoundation\Response;

class GenericResponse extends AbstractResponse
{
    /**
     * The response body
     * @var string
     */
    protected $body = '';

    /**
     * The HTTP response code
     * @var integer
     */
    protected $httpCode = 200;

    /**
     * Optional HTTP headers to send with the response
     * @var array
     */
    protected $headers = [];

    /**
     * __construct
     *
     * @param string  $body     the response body
     * @param integer $httpCode the HTTP response code
     */
    public function __construct($body, $httpCode = 200)
    {
        $this->body     = $body;
        $this->httpCode = $httpCode;
    }

    /**
     * @inheritDoc
     */
    public function toHttpResponse()
    {
        return new Response($body, $httpCode, $this->headers);
    }

    /**
     * Set the HTTP response headers as an associative array
     *
     * @param array $headers an associative array of response headers
     * @return GenericResponse $this
     */
    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get the response as a string
     *
     * @return string a string representation of the response
     */
    public function __toString()
    {
        return $this->body;
    }
}
