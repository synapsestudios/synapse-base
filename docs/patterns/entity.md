# Entity

## What Is It?

Entities are [DataObjects](data-object.md) that represent objects in the database. They add a small amount of extra functionality to DataObject that is mostly used internally by [Mappers](mapper.md).

Since entities typically are associated with a single database table, their shape usually mimics the table's shape exactly. (Having the same fields with identical names.) From time to time, however, entities add extra fields to account for joins or other injected data that is not from the primary database table to which they are associated.

## [Synapse\Entity\AbstractEntity](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Entity/AbstractEntity.php)

### Methods

#### getColumns()

Get all columns of this entity that correspond to database columns in the associated table. Returns all columns defined in the DataObject by default. If extra fields (not associated with the primary database table) are added to an entity, this method should be overridden to return only the fields in the table.

#### isNew()

Returns whether the entity has been persisted to the database. Returns `true` if the `id` field exists and is not a falsy value.

## [Synapse\Entity\EntityIterator](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Entity/EntityIterator.php)

EntityIterator implements [Countable](http://php.net/manual/en/class.countable.php) and [Iterator](http://php.net/manual/en/class.iterator.php). It has a few purposes:

- Encapsulate a collection of entities.
- Package pagination data along with the entities.
- Retrieve an array copy of all entities with a simple method call.
- Retrieve the count of entities with a simple method call.
- Allow iteration over the entities.

### Methods

#### __construct(array $entities = array())

Entities should be injected via the constructor when possible. (e.g. `new EntityIterator($entities)`)

#### setEntities(array $entities)

#### getEntities()

#### getPaginationData()

#### setPaginationData(PaginationData $paginationData)

This is used internally by mappers. If you need to work with `PaginationData`, see [FinderTrait](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Mapper/FinderTrait.php)::getPaginationData for an example.

#### getArrayCopy()

Return the array of entities in array copy format. (Return an array of array copies.)

#### count()

Returns the number of entities.

#### Iterator Methods

The following methods are implemented per the Iterator interface, and work as you would expect:

- current()
- key()
- next()
- rewind()
- valid()

## How To Use It

### Creating an Entity

1. If you're creating a new entity, it's generally because you're adding a table to the database. So step 1 is to create and run the migrations to add your table.
1. Create the entity class and define its shape in the `protected $object` property.
  - Remember, as with all DataObjects, the value of each element in this array will be the default value.
1. If a new database table is being created, a [Mapper](mapper.md) probably is too. Inject the entity into the mapper via `__construct` or using `setPrototype`.

### Proper Usage

Entities are often used in conjunction with mappers.

## Examples

#### BlogEntity.php:

```PHP
<?php

namespace Application\Blog;

use Synapse\Entity\AbstractEntity;

class BlogEntity extends AbstractEntity
{
    /**
     * {@inheritDoc}
     */
    protected $object = [
        'id'                 => null,
        'title'              => null,
        'date_published'     => null,
        'author_id'          => null,
    ];
}
```
