<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;
use Synapse\Mapper\SqlFactory;

class SqlFactoryTest extends MapperTestCase
{
    const TABLE = 'test_table';

    public function setUp()
    {
        parent::setUp();

        $this->factory = new SqlFactory();
    }

    public function testGetSqlObjectReturnsSqlObject()
    {
        $sqlObject = $this->factory->getSqlObject($this->mocks['adapter'], self::TABLE);

        $this->assertInstanceOf(
            'Zend\Db\Sql\Sql',
            $sqlObject
        );
    }

    public function testGetSqlObjectInjectsAdapterAndTable()
    {
        $sqlObject = $this->factory->getSqlObject($this->mocks['adapter'], self::TABLE);

        $this->assertSame($this->mocks['adapter'], $sqlObject->getAdapter());

        $this->assertSame(self::TABLE, $sqlObject->getTable());
    }
}
