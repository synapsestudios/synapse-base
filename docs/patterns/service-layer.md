# Service Layer

## What is it?

Good software is written in layers. The service layer is a layer of classes that house "domain logic". (Sometimes referred to as business logic; the custom logic that is the reason your application exists.)

The purpose of the service layer is to maintain [separation of concerns](http://en.wikipedia.org/wiki/Separation_of_concerns) by providing a logical place to encapsulate said domain logic.

### In Depth

To call this pattern Service Layer is to imply that there are other layers.

Martin Fowler talks about the Service Layer in [Patterns of Enterprise Application Architecture](http://martinfowler.com/books/eaa.html). The book talks about the three principal layers that exist in software:

1. **Presentation** (e.g. HTML, CSS, JavaScript front-end)
1. **Domain** (e.g. PHP back-end with business rules)
1. **Data Source** (e.g. MySQL)

The domain layer contains the business logic of the application and related implementation details. Developers who don't know any better often stuff this code in the controller or the model of the back-end MVC framework. This is bad for a plethora of reasons. Ultimately, the code becomes a nightmare to maintain.

The **Service Layer** is an entire layer within the **Domain** layer. It is a set of classes which encapsulate business logic in a modular fashion.

Further reading: ["Fat model, skinny controller" is a load of rubbish](http://joncairns.com/2013/04/fat-model-skinny-controller-is-a-load-of-rubbish/) by Jon Cairns

## How to use it

### Services can do anything

A service can exist for any purpose. Synapse Base has a number of services for various purposes. The [`EmailService`](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Email/EmailService.php) is used to create emails and enqueue jobs to send them. The [`SocialLoginService`](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/SocialLogin/SocialLoginService.php) handles account registration from social sites and other related tasks.

It is the responsibility of the developer to determine the architecture of a service layer. It is often the case that one service will depend upon one or many other services. If a service begins to feel bloated, the [Single Responsibility Principle](http://en.wikipedia.org/wiki/Single_responsibility_principle) can be used as a litmus test to determine whether additional services should be extracted out of it. It's always better to have many services with narrowly defined purposes than to have too few services with overly-broad purposes.

### CRUD Facade

The service layer can take a lot of shapes. At the time of writing, a majority of services in our web apps are essentially facades for CRUD (Create, Read, Update, Delete) operations.

Most services in our apps tend to be entity-centric. In other words, if an application has a concept of a Listing (like a "craigslist" listing), there will be a ListingEntity, a ListingMapper, a ListingController and a **ListingService**. The ListingService's purpose would be to encapsulate business logic involved in creating, deleting and updating Listings.

By abstracting the CRUD operations into a single service, logic in an encapsulated service provides a hook for any related functionality that needs to occur upon a CRUD function. We typically allow controllers to use Mappers directly for simple "find" operations since they are read only. Advanced "find" operations (such as a `search` method) belong in the service.

## Example

Consider a blogging platform designed specifically for businesses. There are two ways of creating an account:

1. Anyone can visit the website and create an account for themselves.
1. A user who owns a "business blog" can bulk-invite a list of users from their business. Each of these users are then authorized to blog on behalf of the business.

There are two sections of code that must perform user account creation:

1. The normal "create account" code.
1. The "bulk invite" code.

As part of the **Service Layer**, an `AccountService` exists which contains a method, `createAccount`. Both sections of code ("create account" and "bulk invite") use the `AccountService::createAccount` method to perform the account creation.

As a result:

- Account creation code is not duplicated
- Account creation code is encapsulated in its own class, separate from the controller logic, view logic, and other unrelated business logic.
- The project is much more maintainable.
  - Example: Whenever a new requirement is added to the project (e.g. send a "welcome" email to all new users when their account is created) there is a logical place to add this feature.

### UserService.php

```PHP
<?php

namespace Application\User;

use Application\Email\WelcomeEmailSender;

class UserService
{
    /**
     * @var WelcomeEmailSender
     */
    protected $welcomeEmailSender;

    /**
     * @param  WelcomeEmailSender $welcomeEmailSender
     */
    public function __construct(WelcomeEmailSender $welcomeEmailSender)
    {
        $this->welcomeEmailSender = $welcomeEmailSender;
    }

    /**
     * Create a user and send them a welcome email
     *
     * @param  array $data Account data
     * @return UserEntity
     */
    public function create(array $data)
    {
        // Create an account...

        // Send a welcome email
        $this->welcomeEmailSender->sendWelcomeEmailForUser($user);

        return $user;
    }
}
```

### WelcomeEmailSender.php

```PHP
<?php

namespace Application\Email;

class WelcomeEmailSender
{
    public function sendWelcomeEmailForUser(UserEntity $user)
    {
        // Code that sends the email...
    }
}
```

### UserController.php

```PHP
<?php

namespace Application\User;

use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for user endpoint
 *
 * Notice that this controller doesn't have to perform any business logic involved in creating the user;
 * it just calls UserService::create.
 */
class UserController
{
    /**
     * @var UserService
     */
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Create a user
     *
     * @param  Request $request
     * @return Response
     */
    public function post(Request $request)
    {
        $data = $this->getContentAsArray($request);

        $user = $this->service->create($data);

        return $this->createEntityResponse($user, 201);
    }
}
```
