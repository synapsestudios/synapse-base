# Mapper

## What Is It?

Mappers are responsible for mapping data in the database to [Entities](entity.md). As such, all interaction with the database should occur in a mapper.

## The DataMapper Pattern

The Mapper pattern is cataloged in Martin Fowler's *Patterns of Enterprise Application Architecture*. (He refers to the pattern as DataMapper; [see the online summary](http://martinfowler.com/eaaCatalog/dataMapper.html).) The DataMapper pattern is one solution to the [object/relational mapping problem](http://en.wikipedia.org/wiki/Object-relational_impedance_mismatch).

Synapse's DataMapper solution differs from standard ORM modules, which typically implement DataMapper in an "automagic" manner. The developer asks the ORM to get or set some data and the ORM determines which database operations to perform. This often results in unoptimized database operations. The developer is not empowered to optimize the queries.

On the other hand, Synapse's DataMapper solution has a good balance of convenience and performance. The base mapper class and traits contain "convenience" methods that can be used to perform the most common, simple types of queries without having to build the query by hand. In cases where a complex query is needed, the developer is required to implement it (using the [Zend Db](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html) module). This means that the developer is simultaneously (1) free to build the custom query as needed and (2) responsible for determining the most performant solution.

> With great power comes great responsibility
>
> -- Benjamin Parker


## Classes and Traits

#### [Synapse\Mapper\AbstractMapper](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Mapper/AbstractMapper.php)

Contains all base mapper functionality.

##### Methods

  - `AbstractMapper::getSqlObject()`
    - Get a `Zend\Db\Sql\Sql` object. (Instantiated to use the `tableName` of the mapper.)
  - `AbstractMapper::executeAndGetResultsAsEntity(PreparableSqlInterface $query)`
  - `AbstractMapper::executeAndGetResultsAsEntityIterator(PreparableSqlInterface $query)`

Execute the given query and return the result(s) as an entity or EntityIterator, respectively.

#### [Synapse\Mapper\FinderTrait](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Mapper/FinderTrait.php)

Contains methods that perform `SELECT` operations.

##### Methods

  - `FinderTrait::findBy(array $wheres, array $options = [])`
  - `FinderTrait::findById($id)`
  - `FinderTrait::findAllBy(array $wheres, array $options = [])`
  - `FinderTrait::findAll(array $options = [])`

##### Arguments

###### `$wheres`

Array of where conditions. Each where condition should be in one of the following formats:

  - `['column' => 'value']`
  - `['column', 'operator', 'value']`

Examples:

  - `['id' => 6]`
  - `['fans', '>', 400]`
  - `['name', 'LIKE', 'Pat']`
  - `['archived', '!=', true]`
  - `['color', 'IN', ['red', 'white', 'blue']]`

All available operators:
  - `=`
  - `!=`
  - `>`
  - `<`
  - `>=`
  - `<=`
  - `LIKE`
  - `NOT LIKE`
  - `IN`

Other operators like `BETWEEN`, `DISTINCT` and `HAVING` will require a custom query using [Zend\Db](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html). (See [`AbstractMapper::getSqlObject`](#methods) which returns a Zend\Db\Sql\Sql object.)

###### `$options`

Array of options for this request. May include `order`, `page`, or `resultsPerPage`.

`order`:

  - A single **order** or an array of **order** arrays.
  - An **order** in this case is an array in which the first element is the column and the second is the direction.
  - Valid directions are `ASC` and `DESC`.
  - Examples:
    - `['name', 'DESC']`
    - `[['name', 'ASC'], ['start_date', 'DESC']]`
  - **Note:** `order` must be set if `page` given.

`page`:

  - The page of results to display, represented as an integer.

`resultsPerPage`:

  - The number of results per page, represented as an integer.

#### [Synapse\Mapper\InserterTrait](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Mapper/InserterTrait.php)

Contains methods that perform `INSERT` operations.

##### Methods

  - `InserterTrait::insert(AbstractEntity $entity)`

#### [Synapse\Mapper\UpdaterTrait](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Mapper/UpdaterTrait.php)

Contains methods that perform `UPDATE` operations.

##### Methods

  - `UpdaterTrait::update(AbstractEntity $entity)`

**Note**: The `UPDATE` is performed by the `id` field of the entity:

```SQL
UPDATE table SET ... WHERE `id` = [ID of entity]
```

#### [Synapse\Mapper\DeleterTrait](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Mapper/DeleterTrait.php)

Contains methods that perform `DELETE` operations.

##### Methods

  - `DeleterTrait::delete(AbstractEntity $entity)`
  - `DeleterTrait::deleteWhere(array $wheres)`

##### Arguments

###### `$wheres`

[See `$wheres` under FinderTrait.](#wheres)

## How To Use It

### Creating a Mapper

1. Extend the abstract mapper.
1. Set the `tableName` property.
1. `use` the appropriate traits according to the mapper's needs.

### Proper Usage

Avoid using a mapper directly from controllers, unless you're simply doing a find operation. Creating, updating, and deleting entities should be proxied through a [Service](service-layer.md). This provides a natural hook for extra logic that needs to occur upon creation/deletion/update.

### [Testing a Mapper With PHPUnit](../unit-testing/php/mappers.md)

## Examples

#### BlogMapper.php:

```PHP
<?php

namespace Application\Blog;

use Synapse\Mapper;

class BlogMapper extends Mapper\AbstractMapper
{
    use Mapper\FinderTrait;
    use Mapper\InserterTrait;
    use Mapper\UpdaterTrait;
    use Mapper\DeleterTrait;

    /**
     * {@inheritDoc}
     */
    protected $tableName = 'blogs';

    /**
     * (Example of a custom method that requires the developer to build the query by hand.)
     *
     * Return blogs that are tagged with the given tag.
     *
     * @param  string $tag
     * @return EntityIterator
     */
    public function findAllByTag($tag)
    {
        $query = $this->getSqlObject()->select();

        $query->join(
            'pvt_blogs_tags',
            'pvt_blogs_tags.blog_id = blogs.id'
        );

        $query->join(
            'tags',
            'tags.id = pvt_blogs_tags.tag_id'
        );

        $query->where(['tags.label' => $tag);

        return $this->executeAndGetResultsAsEntityIterator($query);
    }
}
```

#### BlogService.php:

```PHP
<?php

namespace Application\Blog;

/**
 * Service that is foundationally a proxy for the BlogMapper, but also acts as a place to do other tasks / cleanup
 * whenever CRUD operations are performed.
 */
class BlogService
{
    /**
     * @var BlogMapper
     */
    protected $mapper;

    /**
     * @param  BlogMapper $mapper
     */
    public function __construct(BlogMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Find all blogs that match the given title search
     *
     * @param  string  $titleSearchTerm The search term
     * @param  integer $page            The search result page number
     * @return EntityIterator
     */
    public function findByTitleSearch($titleSearchTerm, $page = 1)
    {
        $wheres = [
            ['title', 'LIKE', $titleSearchTerm]
        ];

        $options = [
            'order'          => ['date_published', 'DESC'],
            'page'           => $page,
            'resultsPerPage' => 10,
        ];

        return $this->mapper->findAllBy($wheres, $options);
    }

    /**
     * Find all that are tagged with the given tag
     *
     * @param  string $tag
     * @return EntityIterator
     */
    public function findAllByTag($tag)
    {
        return $this->mapper->findAllByTag($tag);
    }

    /**
     * Create a blog
     *
     * @param  array $data
     * @return BlogEntity
     */
    public function create(array $data)
    {
        /**
         * Perform various actions related to blog creation
         */

        // Create and persist entity to database
        $entity = $this->mapper->getPrototype();

        $entity->exchangeArray($data);

        return $this->mapper->insert($entity);
    }

    /**
     * Edit a blog, setting the updated timestamp to the current time
     *
     * @param  BlogEntity $entity
     * @return BlogEntity Edited entity
     */
    public function edit(BlogEntity $entity)
    {
        $entity->setUpdated(time());

        return $this->mapper->update($entity);
    }

    /**
     * Delete a blog
     *
     * @param  BlogEntity $entity
     * @return Result
     */
    public function delete(BlogEntity $entity)
    {
        return $this->mapper->delete($entity);
    }

    /**
     * Delete all archived blogs
     *
     * @return Result
     */
    public function deleteArchivedBlogs()
    {
        return $this->mapper->deleteWhere(['archived' => true]);
    }
}
```
