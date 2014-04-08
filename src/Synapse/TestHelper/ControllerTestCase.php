<?php

namespace Synapse\TestHelper;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Synapse\Stdlib\Arr;

class ControllerTestCase extends PHPUnit_Framework_TestCase
{
    public function createJsonRequest($method, $params)
    {
        $this->request = new Request(
            Arr::get($params, 'getParams', []),
            [],
            Arr::get($params, 'attributes', []),
            [],
            [],
            [],
            Arr::get($params, 'content') ? json_encode($params['content']) : ''
        );
        $this->request->setMethod($method);
        $this->request->headers->set('CONTENT_TYPE', 'application/json');

        return $this->request;
    }
}
