<?php

namespace Synapse\Response;

abstract class AbstractResponse
{
    /**
     * Transform this response into an HTTP response
     *
     * @return Symfony\Component\HttpFoundation\Response the HTTP response to return
     */
    abstract public function toHttpResponse();
}
