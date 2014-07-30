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

    public function testGetContentAsArrayThrowsExceptionWhichExecuteCatchesAndReturns400IfContentInvalid()
    {
        $invalidJson = 'This is not JSON.';

        $request = new Request([], [], [], [], [], [], $invalidJson);

        $request->setMethod('delete');

        $response = $this->controller->execute($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testRequestThatThrowsAnExceptionReturnsA500Response()
    {
        $this->controller->withExceptionThrownOnGet(new Exception(self::ERROR_MESSAGE));

        $response = $this->controller->execute($this->createJsonRequest('GET'));

        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testRequestThatThrowsExceptionReturnsContentWithExceptionMessageIfDebugging()
    {
        $this->controller->withExceptionThrownOnGet(new Exception(self::ERROR_MESSAGE));
        $this->controller->setDebug(true);

        $response = $this->controller->execute($this->createJsonRequest('GET'));

        $error = json_decode($response->getContent(), true)['error'];

        $this->assertEquals(self::ERROR_MESSAGE, $error);
    }

    public function testRequestThatThrowsExceptionReturnsContentWithGenericMessageIfNotDebugging()
    {
        $this->controller->withExceptionThrownOnGet(new Exception(self::ERROR_MESSAGE));
        $this->controller->setDebug(false);

        $response = $this->controller->execute($this->createJsonRequest('GET'));

        $error = json_decode($response->getContent(), true)['error'];

        $this->assertEquals(AbstractRestController::SERVER_ERROR_MESSAGE, $error);
    }

    public function testRequestThatThrowsExceptionReturnsContentWithStackTraceIfDebugging()
    {
        $exception = new Exception(self::ERROR_MESSAGE);
        $this->controller->withExceptionThrownOnGet($exception);
        $this->controller->setDebug(true);

        $response = $this->controller->execute($this->createJsonRequest('GET'));

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('trace', $content);
        $this->assertTrue(is_array($content['trace']));
    }

    public function testRequestThatThrowsExceptionReturnsContentWithNoStackTraceIfNotDebugging()
    {
        $this->controller->withExceptionThrownOnGet(new Exception(self::ERROR_MESSAGE));
        $this->controller->setDebug(false);

        $response = $this->controller->execute($this->createJsonRequest('GET'));

        $content = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('trace', $content);
    }

    public function testRequestThatThrowsExceptionLogsErrorMessageAndStackTraceIfDebugging()
    {
        $this->capturingLoggedErrors();
        $exception = new Exception(self::ERROR_MESSAGE);
        $this->controller->withExceptionThrownOnGet($exception);
        $this->controller->setDebug(true);

        $this->controller->execute($this->createJsonRequest('GET'));

        $this->assertEquals(1, count($this->captured->loggedErrors));
        $this->assertEquals($exception->getMessage(), $this->captured->loggedErrors[0]['message']);
        $this->assertEquals(
            ['exception' => $exception],
            $this->captured->loggedErrors[0]['context']
        );
    }

    public function testRequestThatThrowsExceptionLogsErrorMessageAndStackTraceIfNotDebugging()
    {
        $this->capturingLoggedErrors();
        $exception = new Exception(self::ERROR_MESSAGE);
        $this->controller->withExceptionThrownOnGet($exception);
        $this->controller->setDebug(false);

        $this->controller->execute($this->createJsonRequest('GET'));

        $this->assertEquals(1, count($this->captured->loggedErrors));
        $this->assertEquals($exception->getMessage(), $this->captured->loggedErrors[0]['message']);
        $this->assertEquals(
            ['exception' => $exception],
            $this->captured->loggedErrors[0]['context']
        );
    }
}
