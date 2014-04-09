<?php

namespace SynapseTest\Mapper;

use PHPUnit_Framework_TestCase;

class AbstractMapperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $mockDbStatement = $this->setUpMockDbStatement();
        $mockDbDriver    = $this->setUpMockDbDriver($mockDbStatement);
        $mockDbAdapter   = $this->setUpMockDbAdapter($mockDbStatement);

        $this->mockDbAdapter = $mockDbAdapter;
    }

    public function setUpMockDbStatement()
    {
        $dbStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');

        $dbStatement->expects($this->once())
            ->method('prepareStatementForSqlObject')
            ->will($this->returnValue(void));

        return $dbStatement;
    }

    public function setUpMockDbDriver($mockStatement)
    {
        $dbDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');

        $dbDriver->expects($this->once())
            ->method('createStatement')
            ->will($this->returnValue($mockStatement));

        return $dbDriver;
    }

    public function setUpMockDbAdapter($mockDbDriver)
    {
        $dbAdapter = $this->getMock('Zend\Db\Adapter\Adapter');

        $dbAdapter->expects($this->once())
            ->method('getDriver')
            ->will($this->returnValue($mockDbDriver));

        return $dbAdapter;
    }
}
