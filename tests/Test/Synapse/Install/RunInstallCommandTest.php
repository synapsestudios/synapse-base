<?php

namespace Test\Synapse\Install;

use PHPUnit_Framework_TestCase;
use Synapse\Install\RunInstallCommand;
use stdClass;

class RunInstallCommandTest extends PHPUnit_Framework_TestCase
{
    const TABLE = 'table';
    const VIEW  = 'view';

    public function setUp()
    {
        $this->captured = new stdClass();
        $this->captured->droppedTables = [];
        $this->captured->droppedViews = [];

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

    public function captureDrops($query)
    {
        if (preg_match('/DROP TABLE (.+)/', $query, $matches)) {
            $this->captured->droppedTables[] = $matches[1];
        } else if (preg_match('/DROP VIEW (.+)/', $query, $matches)) {
            $this->captured->droppedViews[] = $matches[1];
        }
    }

    public function withTablesExistingButNotViews()
    {
        $this->mockDbInterface->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function ($query) {
                $this->captureDrops($query);

                if ($query === 'SHOW TABLES' || $query === 'SHOW FULL TABLES WHERE TABLE_TYPE LIKE "BASE_TABLE"') {
                    return [[self::TABLE]];
                }
                if ($query === 'SHOW FULL TABLES WHERE TABLE_TYPE LIKE "VIEW"') {
                    return [[]];
                }
            }));
    }

    public function withViewsExistingInAdditionToTables()
    {
        $this->mockDbInterface->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function ($query) {
                $this->captureDrops($query);

                if ($query === 'SHOW TABLES') {
                    return [[self::VIEW, self::TABLE]];
                } else if ($query === 'SHOW FULL TABLES WHERE TABLE_TYPE LIKE "BASE_TABLE"') {
                    return [[self::TABLE]];
                } else if ($query === 'SHOW FULL TABLES WHERE TABLE_TYPE LIKE "VIEW"') {
                    return [[self::VIEW]];
                }
            }));
    }

    public function testExecuteDoesNotDropProductionTables()
    {
        $this->command->setAppEnv('production');
        $this->withDropTablesOptionIncluded();
        $this->withTablesExistingButNotViews();

        $result = $this->command->execute($this->mockInputInterface, $this->mockOutputInterface);

        $this->assertEquals([], $this->captured->droppedTables);
    }

    public function testExecuteDoesNotDropProductionViews()
    {
        $this->command->setAppEnv('production');
        $this->withDropTablesOptionIncluded();
        $this->withViewsExistingInAdditionToTables();

        $result = $this->command->execute($this->mockInputInterface, $this->mockOutputInterface);

        $this->assertEquals([], $this->captured->droppedViews);
    }

    public function testExecuteDoesDropDevelopmentTables()
    {
        $this->command->setAppEnv('development');
        $this->withDropTablesOptionIncluded();
        $this->withTablesExistingButNotViews();

        $result = $this->command->execute($this->mockInputInterface, $this->mockOutputInterface);

        $this->assertEquals([self::TABLE], $this->captured->droppedTables);
    }

    public function testExecuteDoesDropDevelopmentViews()
    {
        $this->command->setAppEnv('development');
        $this->withDropTablesOptionIncluded();
        $this->withViewsExistingInAdditionToTables();

        $result = $this->command->execute($this->mockInputInterface, $this->mockOutputInterface);

        $this->assertEquals([self::VIEW], $this->captured->droppedViews);
    }
}
