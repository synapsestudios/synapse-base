<?php

namespace SynapseTest\Controller;

use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class AbstractRestControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->controller = new RestController;
    }

    /**
     * @expectedException Synapse\Rest\Exception\MethodNotImplementedException
     */
    public function testExecuteThrowsExceptionIfMethodNotImplemented()
    {
        $request = new Request;
        $request->setMethod('POST');
        $request->headers->set('Content-Type', 'application/json');

        $this->controller->execute($request);
    }

    public function testGetReturnsResponse()
    {
        $request = new Request;
        $request->setMethod('GET');
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->execute($request);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertEquals('test', (string) $response->getContent());
    }

    public function testPutReturnsResponse()
    {
        $request = new Request;
        $request->setMethod('PUT');
        $request->headers->set('Content-Type', 'application/json');

        $response = $this->controller->execute($request);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertEquals('{"test":"test"}', (string) $response->getContent());
    }
}
