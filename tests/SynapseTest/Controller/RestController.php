<?php

namespace SynapseTest\Controller;

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
}
