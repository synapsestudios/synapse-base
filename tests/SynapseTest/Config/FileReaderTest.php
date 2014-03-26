<?php

namespace SynapseTest\Config;

use PHPUnit_Framework_TestCase;
use Synapse\Config;

class FileReaderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoNamespaceThrowsException()
    {
        $reader = new Config\FileReader(__DIR__.'/data');
        $reader->load('');
    }

    public function testMissingFileReturnsEmptyArray()
    {
        $reader = new Config\FileReader(__DIR__.'/data');
        $this->assertEmpty($reader->load('fileNotFound'));
    }
}
