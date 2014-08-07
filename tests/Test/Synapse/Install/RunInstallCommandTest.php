<?php

namespace Test\Synapse\Install;

use PHPUnit_Framework_TestCase;
use Synapse\Install\RunInstallCommand;

class RunInstallCommandTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->command = new RunInstallCommand('install:run');
    }

    public function testExecuteDoesNotDropProductionTables()
    {
        // Mock input interface
        $inputStub = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $inputStub->expects($this->any())
            ->method('getOption')
            ->with($this->equalTo('drop-tables'))
            ->willReturn(true);

        // Mock output interface
        $outputStub = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        // Mock database adapter
        $dbMock = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();
        $tableDropped = false;
        $dbMock->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function($query) use ($tableDropped) {
                if ($query === 'SHOW TABLES') {
                    $tableArray[0][0] = 0;
                    return $tableArray;
                }
                if (preg_match('/DROP TABLE/',$query)) {
                    $tableDropped = true;
                }
            }));
        $this->command->setDatabaseAdapter($dbMock);

        $result = $this->command->execute($inputStub, $outputStub);
        $this->assertEquals(false, $tableDropped);
    }
}
