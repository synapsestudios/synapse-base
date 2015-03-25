<?php
namespace Test\Synapse\Migration;

use Synapse\Migration\CreateMigrationCommand;

class CreateMigrationCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateClassNameGeneratesCorrectName() {
        $time = '19990101121256';
        $description = "gordon ipsum solar robot";

        // monkey code the method to be accessible
        $method = new \ReflectionMethod('Synapse\Migration\CreateMigrationCommand', 'generateClassName');
        $method->setAccessible(true);

        $command = new CreateMigrationCommand(new \Synapse\View\Migration\Create(new \Mustache_Engine()));

        $resultClassName = $method->invoke($command, $time, $description);

        $this->assertEquals('Migration'.$time.'GordonIpsumSolarRobot', $resultClassName);
    }
}
