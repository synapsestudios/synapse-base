<?php

namespace SynapseTest\Config;

use PHPUnit_Framework_TestCase;
use Synapse\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = new Config\Config;
    }

    public function testAttachReader()
    {
        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $this->assertEquals(1, count($this->config->getReaders()));
    }

    public function testConfigWithSingleReader()
    {
        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $config = $this->config->load('config');

        $this->assertEquals('notOverridden', $config['someNotOverriddenKey']);
        $this->assertEquals('notOverridden', $config['someOverriddenKey']);
    }

    public function testConfigWithOverrideReader()
    {
        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $this->config->attach(new Config\FileReader(__DIR__.'/data/override'));

        $config = $this->config->load('config');

        $this->assertEquals('notOverridden', $config['someNotOverriddenKey']);
        $this->assertEquals('overridden', $config['someOverriddenKey']);

        // Test cache works
        $config = $this->config->load('config');

        $this->assertEquals('notOverridden', $config['someNotOverriddenKey']);
        $this->assertEquals('overridden', $config['someOverriddenKey']);
    }

    public function testConfigOverrideOrder()
    {
        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $this->config->attach(new Config\FileReader(__DIR__.'/data/override'), false);

        $config = $this->config->load('config');

        $this->assertEquals('notOverridden', $config['someNotOverriddenKey']);
        $this->assertEquals('notOverridden', $config['someOverriddenKey']);
    }

    public function testDetachingReaderClearsOverrides()
    {
        $overrideReader = new Config\FileReader(__DIR__.'/data/override');

        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $this->config->attach($overrideReader);

        $config = $this->config->load('config');

        $this->assertEquals('notOverridden', $config['someNotOverriddenKey']);
        $this->assertEquals('overridden', $config['someOverriddenKey']);

        $this->config->detach($overrideReader);

        $config = $this->config->load('config');

        $this->assertEquals('notOverridden', $config['someNotOverriddenKey']);
        $this->assertEquals('notOverridden', $config['someOverriddenKey']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyNamespaceThrowsException()
    {
        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $this->config->load('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testIllegalNamespaceThrowsException()
    {
        $this->config->attach(new Config\FileReader(__DIR__.'/data'));
        $this->config->load(array('test'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoAttachedGroupsThrowsException()
    {
        $this->config->load('');
    }
}
