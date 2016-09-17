## Testing PHP ##

## Guidelines ##
* Tests should be written for all endpoints. This includes code for controllers, services, entities, and mappers, but not service providers.
* Tests should not use the database.
* Tests should not use external services.

## Running Tests ##
* Run tests from the command line by running `vendor/bin/phpunit` from the project's root directory.
* Use the `--filter` argument to restrict only to the tests you are currently working on.
* Access code coverage by opening `project_path/phpunit-coverage/index.html` in your browser.

## Getting Started ##
* Tests go in `project_path/tests/Test/Path/To/Tested/FileTest`.
* Test classes should extend `Synapse\TestHelper\TestCase`, or a child class of `TestCase` located in `Synapse\TestHelper`.
* [PHPUnit Manual](http://phpunit.de/manual/4.0/en/index.html)

## Conventions
There are many ways to write unit tests, but here are the conventions we have developed.
You may encounter code in our projects that does not follow these conventions, but that is
because we've been coming up with these conventions as we go. If you are working with
tests that don't follow these conventions, feel free to update them.

### General

Since PHPUnit classes are self-contained, there is effectively no difference between
public, private, and protected methods. For that reason, we make everything public.

We also use constants a lot. If you find yourself creating the same variable in multiple
tests, consider making it a class constant of the test case class.

### Setup

Use the `setUp` method to set up mocks and instantiate the object under test. (PHPUnit automatically runs this before every test.)

#### Setting up mocks

The `TestCase` class includes a `setMocks` method that accepts an array that is a map of `alias` to `className`, as such:

```PHP
$this->setMocks([
    'jobMapper'       => 'Application\Job\JobMapper',
    'intercomService' => 'Application\Intercom\IntercomService',
]);
```

In the example above, the mock `jobMapper` is accessible from the `$this->mocks` array using its alias: `$this->mocks['jobMapper']`

Mocks should be created in `setUp`. Typically expectations are not set on the
mock at this time, although they can be if the same mocking rules will apply for every single test in the class.

#### Instantiating the object under test

If the object under test only takes mocks in its constructor (or if it takes no args at all), you
should instantiate it in setUp, assigning it to a public instance property. We typically use a
generic name that describes the type of thing it is e.g. controller or mapper.

If the object will take unmocked arguments that will differ between tests, you can either instantiate
it inline for each test or create a helper method that you call for each test.

#### Creating 'captured' object

The setUp method is also where we set up the $captured property, which is used to capture
method arguments and return values so that they can be used in assertions. More on this later.

#### Example

```php

namespace Test\Application\Breed;

use Synapse\TestHelper\ControllerTestCase;
use Application\Breed\BreedController;
use stdClass;

class BreedControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        $this->captured = new stdClass();

        $this->setMocks([
            'breedService' => 'Application\Breed\BreedService',
            'validator'    => 'Application\Breed\FuzzyBreedSearchValidator',
        ]);

        $this->controller = new BreedController(
            $this->mocks['breedService'],
            $this->mocks['validator']
        );
    }

    // ...
}
```

### Defining Mock Behavior

While mocks are created and injected in setUp, their behavior is defined within individual tests.
Almost all mocking behavior will be defined in methods separate from the test method in order to
maintain readability. Extracting mock behavior keeps tests short and allows them to be
written in a way that reads like documentation.

Methods that define mocking behavior come in three types:

1. Those that specify simply that a method will (or will not) be called. These are prefixed with `expecting`.

```php
    public function expectingFileDeleted()
    {
        $this->mocks['fileMapper']->expects($this->once())
            ->method('delete')
            ->with($this->isInstanceOf('Application\File\FileEntity'));
    }
```

2. Those that specify a return value. These are prefixed with `with`.

```php
    public function withKennelNotFound()
    {
        $this->mocks['kennelMapper']->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(false));
    }
```

Note the use of `$this->any()`. This is typical of `with` methods.

3. Those that capture arguments and/or return values. These are prefixed with `capturing`.

```php
    public function capturingCreatedDomainNames()
    {
        $this->captured->createdDomainNames = [];

        $this->mocks['cloudSearchService']->expects($this->any())
            ->method('createDomain')
            ->will($this->returnCallback(function ($domainName) {
                $this->captured->createdDomainNames[] = $domainName;
            }));
    }
```

These three types of methods aren't entirely separate. Most commonly, a `with` method will also capture
values.

As for the rest of the name that follows the prefix, we prefer to use names that
don't mention implementation details. For example `withProfileCreated` is preferable
to `withDataPassedToCreateMethodOfProfileService`.

### [Testing Mappers](php/mappers.md)

### [Testing Controllers](php/controllers.md)

### Testing Routes

Route definitions can be surprisingly fragile, so testing them is a good idea. In addition to verifying the existence of a route and its URI, you can test whether authentication is required and which roles are
able to access the route. See [UserServiceProviderTest](https://github.com/synapsestudios/synapse-base/blob/master/tests/Test/Synapse/User/UserServiceProviderTest.php)
for examples.
