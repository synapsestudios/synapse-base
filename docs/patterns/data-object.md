# Data Object

## What Is It?

Data objects are essentially wrappers for associative arrays with a pre-defined shape.

## [Synapse\StdLib\DataObject](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/StdLib/DataObject.php)

### Methods

#### `__construct(array $data = [])`

Create the object and initialize with the provided `$data`.

#### Magic Getters and Setters

`DataObject` assumes that all fields are in lowercase underscore-separated format. (e.g. `field_name`)

`DataObject` includes magic getters and setters using PHP's magic `__call` method. They follow the format `getFieldName()` and `setFieldName($value)`. `DataObject` automatically converts the `UpperCamelCase` representation in the method name to `lower_underscore_case` for the internal representation.

#### `exchangeArray(array $data)`

Overwrites any values specified in `$data`.

#### `getArrayCopy`

Returns a copy of the array.

## How To Use It

### Creating a DataObject

1. Extend `DataObject`.
1. Set the protected `object` property.
  - `$object` should be an associative array with keys for each of the DataObject's properties.
  - The value of each key will be the default value.

### Proper Usage

Methods that accept a DataObject as an argument should typehint that argument wherever possible. This takes advantage of the primary strength of the DataObject: it is essentially an array with a guaranteed shape. Methods that typehint in this manner can hold more assumptions about the data being passed in.

### Overriding Getters and Setters

One benefit of using DataObjects is that the magic getters and setters can be overridden to provide filtering and formatting of data on input/output. Whenever an initial array is passed into a DataObject via constructor, the constructor uses the magic setters so that any functionality provided in overridden setters is performed.

(See `setFriendOfAFriend` in [Examples](#examples).)

### Convenience Methods for Preformatted Data

DataObjects can also be used to encapsulate code which transforms the data. For example, a DataObject which represents a search request may have a `getWheres` function that returns the array of `WHERE` conditions to be passed to an associated mapper.

(See `getWheres` in [Examples](#examples).)

## Examples

Imagine a social networking application. The following example files are related to a search request for a "friend". The DataObject represents the concept of a search query. It would likely be populated in the controller using the HTTP request input and used to extract the parameters of a SQL query to get the search results.

#### FriendSearchQuery.php:

```PHP
<?php

namespace Application\Search;

use Synapse\StdLib\DataObject;

/**
 * Represents a search request for a friend
 */
class FriendSearchQuery extends DataObject
{
    protected $object = [
        'name'               => null,
        'city'               => null,
        'occupation'         => null,
        'max_age'            => null,
        'min_age'            => null,
        'friend_of_a_friend' => null,
    ];

    public function setFriendOfAFriend($value)
    {
        if ($value !== null) {
            $value = (bool) $value;
        }

        $this->object['friend_of_a_friend'] = $value;

        return $this;
    }

    /**
     * Get the wheres in a format ready to be sent to the mapper
     *
     * @return array
     */
    public function getWheres()
    {
        $object = $this->object;
        $wheres = [];

        $name            = $object['name'];
        $city            = $object['city'];
        $occupation      = $object['occupation'];
        $minAge          = $object['min_age'];
        $maxAge          = $object['max_age'];
        $friendOfAFriend = $object['friend_of_a_friend'];

        if (! empty($name)) {
            $wheres[] = ['name', 'LIKE', $name];
        }

        if (! empty($city)) {
            $wheres[] = ['city', '=', $city];
        }

        if (! empty($occupation)) {
            $wheres[] = ['occupation', '=', $occupation];
        }

        if (! empty($minAge)) {
            $wheres[] = ['age', '>=', $minAge];
        }

        if (! empty($maxAge)) {
            $wheres[] = ['age', '<=', $maxAge];
        }

        if ($friendOfAFriend !== null) {
            $wheres[] = ['friend_of_a_friend', '=', $friendOfAFriend];
        }

        return $wheres;
    }
}
```

#### FriendSearchService.php:

```PHP
<?php

namespace Application\Search;

class FriendSearchService
{
    /**
     * @var UserMapper
     */
    protected $userMapper;

    /**
     * @param  UserMapper $mapper
     */
    public function __construct(UserMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Search for friends and return results
     *
     * @param  FriendSearchQuery $query Search query
     * @return EntityIterator           Collection of user entities
     */
    public function searchForFriends(FriendSearchQuery $query)
    {
        $wheres = $query->getWheres();

        $matches = $this->userMapper->findBy($wheres);

        return $matches;
    }
}
```
