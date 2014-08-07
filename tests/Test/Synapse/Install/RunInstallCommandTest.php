<?php

namespace Test\Synapse\Install;

use PHPUnit_Framework_TestCase;
use Synapse\Install\RunInstallCommand;
use stdClass;

class RunInstallCommandTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->captured = new stdClass();

        $this->command = new RunInstallCommand('install:run');

        $this->setUpMockInputInterface();
        $this->setUpMockOutputInterface();
        $this->setUpMockDbInterface();

        $this->command->setDatabaseAdapter($this->mockDbInterface);
    }

    public function setUpMockInputInterface()
    {
        $this->mockInputInterface = $this->getMock('Symfony\Component\Console\Input\InputInterface');
    }

    public function setUpMockOutputInterface()
    {
        $this->mockOutputInterface = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
    }

    public function setUpMockDbInterface()
    {
        $this->mockDbInterface = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function withDropTablesOptionIncluded()
    {
        $this->mockInputInterface->expects($this->any())
            ->method('getOption')
            ->with($this->equalTo('drop-tables'))
            ->will($this->returnValue(true));
    }

    public function capturingTableDrop()
    {
        $this->captured->tableWasDropped = false;
        $this->mockDbInterface->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function ($query) {
                if ($query === 'SHOW TABLES') {
                    $tableArray[0][0] = 0;
                    return $tableArray;
                }
                if (preg_match('/DROP TABLE/',$query)) {
                    $this->captured->tableWasDropped = true;
                }
            }));
    }

    public function testExecuteDoesNotDropProductionTables()
    {
        $this->capturingTableDrop();
        $this->command->setAppEnv('production');
        $this->withDropTablesOptionIncluded();

        $result = $this->command->execute($this->mockInputInterface, $this->mockOutputInterface);

        $this->assertFalse($this->captured->tableWasDropped);
    }

    public function testExecuteDoesDropDevelopmentTables()
    {
        $this->capturingTableDrop();
        $this->command->setAppEnv('development');
        $this->withDropTablesOptionIncluded();

        $result = $this->command->execute($this->mockInputInterface, $this->mockOutputInterface);

        $this->assertTrue($this->captured->tableWasDropped);
    }
}
