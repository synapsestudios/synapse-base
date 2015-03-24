Upgrade Guide
=============

Pre-2.0.0 -> 2.0.0
------------------

## Bootstrap changes

### Remove Routes definition

```PHP
// Pre-2.0.0
$defaultRoutes   = new Synapse\Application\Routes;
$defaultServices = new Synapse\Application\Services;

$defaultRoutes->define($app);
$defaultServices->register($app);

// 2.0.0
$defaultServices = new Synapse\Application\Services;
$defaultServices->register($app);
```

## Mapper changes

### Update overridden `insert` and `update` methods

Internally, `insert` and `update` call `insertRow` and `updateRow`, respectively, in order to perfom the actual database queries. Children who overrode `insert` or `update` previously may have called `insertRow` and `updateRow` internally. However, the logic that sets magic created/updated timestamp columns was moved from `insertRow` into `insert`. (Same for `updateRow` and `update`.)

```PHP
// Pre-2.0.0

use Synapse\Mapper;
use Synapse\Entity\AbstractEntity;

class FooMapper extends Mapper\AbstractMapper {
    use InserterTrait;

    public function insert(AbstractEntity $entity)
    {
        $values = $entity->getArrayCopy();

        // Custom logic to transform $values

        return $this->insertRow($entity, $values);
    }
}
```

From 2.0.0 on, overridden `insert` and `update` methods should call the parent `insert` and `update` methods instead of `insertRow` and `updateRow`.

```PHP
// 2.0.0

use Synapse\Mapper;
use Synapse\Entity\AbstractEntity;

class FooMapper extends Mapper\AbstractMapper {
    use InserterTrait {
        insert as parentInsert;
    };

    public function insert(AbstractEntity $entity)
    {
        // Custom logic to transform $entity

        return $this->parentInsert($entity);
    }
}
```

## Test changes

A new TestCase class has been added, which includes a more concise way of
specifying mocks. Many existing tests will be unaffected, but starting now test cases
should use the `setMocks()` method and extend `Synapse\TestHelper\TestCase`
instead of `PHPUnit_Framework_TestCase`.

For existing tests, the following changes may be necessary:

### Use the SecurityContextMockInjector trait instead of AbstractSecurityAwareTestCase

AbstractSecurityAwareTestCase has been removed.

### Update mocks

Mocks are now stored in the `$mocks` property on the `TestCase` class (which the other
custom `TestCase` classes now extend). Any test cases that used the mocks set in these test cases must be updated.
Changes include:

#### CommandTestCase

- `$this->mockOutput` is now `$this->mocks['output']`;
- `$this->mockInput` is now `$this->mocks['input']`;

#### MapperTestCase

- `$this->mockAdapter` is now `$this->mocks['adapter']`;
- `$this->mockDriver` is now `$this->mocks['driver']`;
- `$this->mockConnection` is now `$this->mocks['connection']`;
- `$this->mockSqlFactory` is now `$this->mocks['sqlFactory']`;

#### ValidatorConstraintTestCase

- `$this->mockExecutionContext` is now `$this->mocks['executionContext']`

#### Test cases using SecurityContextMockInjector

- `$this->mockSecurityContext` is now `$this->mocks['securityContext']`

#### Test cases using TransactionMockInjector

- `$this->mockTransaction` is now `$this->mocks['transaction']`

#### Update uses of getDefaultLoggedInUserEntity

This method will no longer automatically be called if the default user entity has
not been set. An easy way to update existing tests would be to add
`$this->setLoggedInUserEntity($this->getDefaultLoggedInUserEntity())` to `setUp`.
