<?php

namespace Synapse\TestHelper;

/**
 * Use this trait to test classes that require access to the DB adapter
 */
trait DbAdapterTestCaseTrait
{
    public function setUpMockAdapter()
    {
        $this->mockAdapter = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockAdapter->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function ($sql, $mode) {
                $this->sqlStrings[] = $sql;

                if ($mode === 'prepare') {
                    return $this->getMockStatement();
                } else {
                    return $this->getMockResult();
                }
            }));

        $this->mockDriver = $this->getMockBuilder('Zend\Db\Adapter\Driver\Mysqli\Mysqli')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDriver->expects($this->any())
            ->method('createStatement')
            ->will($this->returnValue($this->getMockStatement()));

        $this->mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');

        $this->mockDriver->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->mockConnection));

        $this->mockConnection->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue(
                $this->getMock('mysqli')
            ));

        $this->mockAdapter->expects($this->any())
            ->method('getDriver')
            ->will($this->returnValue($this->mockDriver));

        $this->mockAdapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($this->getPlatform()));
    }
}
