<?php

namespace SynapseTest\Command\Install;

use PHPUnit_Framework_TestCase;
use Synapse\Command\Install\Generate;

class GenerateTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->command = new Generate;
    }

    public function testGetDumpStructureCommand()
    {
        $command = $this->command->getDumpStructureCommand(
            'database',
            'username',
            'passw\'ord',
            '/path/to/output/structure.sql'
        );

        $expected = 'mysqldump \'database\' -u \'username\' -p\'passw\'\\\'\'ord\''
            .' --no-data | sed "s/AUTO_INCREMENT=[0-9]*//" >'
            .' \'/path/to/output/structure.sql\'';

        $this->assertEquals($command, $expected);
    }

    public function testDumpDataCommand()
    {
        $command = $this->command->getDumpDataCommand(
            'database',
            'username',
            'passw\'ord',
            '/path/to/output/data.sql',
            array(
                'users',
                'user_roles',
                'esca\'ped',
            )
        );

        $expected = 'mysqldump \'database\' \'users\' \'user_roles\''
            .' \'esca\'\\\'\'ped\''
            .' -u \'username\' -p\'passw\'\\\'\'ord\''
            .' --no-create-info >'
            .' \'/path/to/output/data.sql\'';

        $this->assertEquals($command, $expected);
    }

    public function testDataPath()
    {
        $this->assertEquals(APPDIR.'/data/', $this->command->dataPath());
    }

    public function testGetUpgradeNamespace()
    {
        $this->assertEquals('Application\\Upgrades\\', $this->command->getUpgradeNamespace());
    }
}
