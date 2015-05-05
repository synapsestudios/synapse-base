Upgrade Guide
=============

Pre-3.0.0 -> 3.0.0
------------------

### New configuration file

Add `login.php` to your `config` directory. The default value is `false`.
```
<?php

/**
 * Settings
 * ========
 *
 * requireVerification Require the user to be verified before logging in
 */

return [
    'requireVerification' => true,
];
```

Pre-2.0.0 -> 2.0.0
------------------

## Bootstrap changes

### Remove Routes definition

```PHP
// bootstrap.php

// Pre-2.0.0
$defaultRoutes   = new Synapse\Application\Routes;
$defaultServices = new Synapse\Application\Services;

$defaultRoutes->define($app);
$defaultServices->register($app);

// 2.0.0
$defaultServices = new Synapse\Application\Services;
$defaultServices->register($app);
```

## Controller changes

### Use `$this->getUser()` instead of `$this->user()` to get the logged in user

The old method alias in `SecurityAwareTrait` has been removed.

## Mapper changes

### Update overridden `insert` and `update` methods

Prior to 2.0.0 it was valid to override `insertRow` and `updateRow`, or to call them in `insert` and `update`. Starting in 2.0.0, mappers should override `insert` and `update` only, and then call the "parent" `insert` and `update` methods rather than `insertRow` and `updateRow`.

#### Pre-2.0.0

There are 2 ways Mappers may have overridden `insert`/`update` before v2.0.0. Take note of the `return` line.

```PHP
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

```PHP
use Synapse\Mapper;
use Synapse\Entity\AbstractEntity;

class BarMapper extends Mapper\AbstractMapper {
    use InserterTrait {
        insertRow as parentInsertRow;
    };

    public function insertRow(AbstractEntity $entity)
    {
        $values = $entity->getArrayCopy();

        // Custom logic to transform $values

        return $this->parentInsertRow($entity, $values);
    }
}
```

#### Post-2.0.0

Again, take note of the `return` line.

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

Note: The logic that sets magic created/updated timestamp columns was moved from `insertRow` into `insert`. (Same for `updateRow` and `update`.)

### Stop using `PivotInserterTrait` and `PivotDeleterTrait`

These have been removed because the missing functionality that required them to exist has now been added to InserterTrait and DeleterTrait.

```PHP
// Pre-2.0.0
class UserRolePivotMapper extends Mapper\AbstractMapper
{
    use Mapper\PivotInserterTrait;
    use Mapper\PivotDeleterTrait;

    // ...
}
```

Just make sure to fill in `$this->primaryKey` and `$this->autoIncrementColumn` and your mapper will work as before.

```PHP
class UserRolePivotMapper extends Mapper\AbstractMapper
{
    use Mapper\InserterTrait;
    use Mapper\DeleterTrait;

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = ['role_id', 'user_id'];

    /**
     * {@inheritdoc}
     */
    protected $autoIncrementColumn = null;
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
