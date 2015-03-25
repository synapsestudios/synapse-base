<?php

namespace Test\Synapse\Controller;

use Exception;
use stdClass;
use Synapse\Controller\AbstractRestController;
use Synapse\TestHelper\ControllerTestCase;
use Symfony\Component\HttpFoundation\Request;

class AbstractRestControllerTest extends ControllerTestCase
{
    const ERROR_MESSAGE = 'I am Error';

    public function setUp()
    {
        $this->captured = new stdClass();

        $this->setUpMockLogger();

        $this->controller = new RestController;
        $this->controller->setLogger($this->mockLogger);
    }

    public function setUpMockLogger()
    {
        $this->mockLogger = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function capturingLoggedErrors()
    {
        $this->captured->loggedErrors = [];

        $this->mockLogger->expects($this->any())
            ->method('addError')
            ->will($this->returnCallback(function($message, $context) {
                $this->captured->loggedErrors[] = [
                    'message' => $message,
                    'context' => $context
                ];
            }));
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

    /**
     * @expectedException Synapse\Controller\BadRequestException
     */
    public function testGetContentAsArrayThrowsBadRequestExceptionIfContentInvalid()
    {
        $invalidJson = 'This is not JSON.';
        $request     = new Request([], [], [], [], [], [], $invalidJson);
        $request->setMethod('delete');

        $response = $this->controller->execute($request);
    }
}
