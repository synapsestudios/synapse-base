### Testing a Mapper With PHPUnit

#### Set Up

1. Extend `Synapse\TestHelper\MapperTestCase`
1. In `setUp`:
 - Run `parent::setUp()` to initialize the mock SqlFactory and mock DbAdapter.
 - Inject `$this->mocks['sqlFactory']` into the mapper with `AbstractMapper::setSqlFactory`
 - Inject `$this->mocks['adapter']` into the mapper as the first argument of the constructor (`new Mapper($this->mocks['adapter']...)`)

#### Making Assertions

In a test, call a method on the mapper that runs a database query (or queries).

The mock adapter and SQL factory will capture the queries that would have run as strings.

`MapperTestCase::getSqlStrings` - Access captured queries as an array of strings.
`MapperTestCase::getSqlString($key = 0)` - Get a single captured query. Defaults to the first.
`MapperTestCase::assertRegExpOnSqlString($regexp, $sqlStringKey = 0)` - Assert that the given captured query matches the regex.

**Note:** Make sure to use delimiters on the outside of the regex (e.g. `/some[regex]/`)

```PHP
<?php

namespace Test\Application\Blog;

use Synapse\TestHelper\MapperTestCase;
use Application\Blog\BlogMapper;
use Application\Blog\BlogEntity;

class BlogMapperTest extends MapperTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mapper = new BlogMapper(
            $this->mocks['adapter'],
            new BlogEntity
        );

        $this->mapper->setSqlFactory($this->mocks['sqlFactory']);
    }

    public function testFindAllByTagJoinsPivotTableAndTagsTable()
    {
        $this->mapper->findAllByTag('foo');

        $pivotJoin = 'JOIN `pvt_blogs_tags` ON `pvt_blogs_tags`.`blog_id` = `blogs`.`id`';
        $tagsJoin  = 'JOIN `tags` ON `tags`.`id` = `pvt_blogs_tags`.`tag_id`';

        $regex = sprintf(
            '/%s %s/',
            $pivotJoin,
            $tagsJoin
        );

        $this->assertRegExpOnSqlString($regex);
    }

    public function testFindAllByTagHasWhereConditionForTagString()
    {
        $string = 'foo';

        $this->mapper->findAllByTag($string);

        $regex = sprintf('/WHERE `tags`.`label` = %s/', $string);

        $this->assertRegExpOnSqlString($regex);
    }
}
```

MapperTestCase also includes a method called setMockResults which allows you to
mock the results of a query.


```php
<?php

namespace Test\Synapse\Mapper;

use Synapse\TestHelper\MapperTestCase;

class FinderTraitTest extends MapperTestCase
{
    // ...

    public function getMockResultDataForSingleResult()
    {
        return [
            [
                'foo'    => 'a',
                'bar'    => 'b',
                'baz'    => 'c',
            ]
        ];
    }

    public function testFindByReturnsEntityOfResultData()
    {
        $mockResults = $this->getMockResultDataForSingleResult();
        $this->setMockResults($mockResults);

        $result = $this->mapper->findBy([]);

        $this->assertInstanceOf('Synapse\Entity\AbstractEntity', $result);
        $this->assertEquals(
            $mockResults,
            [$result->getArrayCopy()]
        );
    }

```
