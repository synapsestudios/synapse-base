<?php

namespace Test\Synapse\Controller;

use Exception;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Synapse\Controller\AbstractRestController;

// Add this ultra-simple stub for the purposes of testing AbstractRestController
class RestController extends AbstractRestController
{
    /**
     * @var Exception
     */
    protected $exceptionThrownOnGet = null;

    public function get()
    {
        if ($this->exceptionThrownOnGet) {
            throw $this->exceptionThrownOnGet;
        }

        return new Response('test');
    }

    public function put()
    {
        return ['test' => 'test'];
    }

    public function delete(Request $request)
    {
        $content = $this->getContentAsArray($request);

        return $content;
    }

    public function withExceptionThrownOnGet(Exception $exception)
    {
        $this->exceptionThrownOnGet = $exception;
    }
}
