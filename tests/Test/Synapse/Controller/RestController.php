<?php

namespace Test\Synapse\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Synapse\Controller\AbstractRestController;
use PHPUnit_Framework_TestCase;

// Add this ultra-simple stub for the purposes of testing AbstractRestController
class RestController extends AbstractRestController
{
    public function get()
    {
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
}
