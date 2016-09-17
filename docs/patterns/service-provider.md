# Service Provider

## What is it?

A Service Provider contains definitions of a collection of related services, routes and authorization rules.

## How to use it

See the [Silex documentation](http://silex.sensiolabs.org/doc/providers.html) for the details.

> Providers allow the developer to reuse parts of an application into another one.

Our primary use of Service Providers is to encapsulate service definitions for an entire module or concept in an application. Because they usually encapsulate application-specific services, they are usually not meant for reuse.

There is no hard and fast rule for which services should be organized together in a Service Provider. Just follow common sense. Each concept or entity in an application will generally have the same types of services.

To be added to the application, Service Providers should be registered in `Application\Services::register` like this:

```PHP
$app->register(new \Application\Path\To\SomeServiceProvider);
```

### The `ServiceProvider` Interface

#### `ServiceProvider::register`

This method is where you register all services, routes and access control rules.

#### `ServiceProvider::boot`

This method is where you perform tasks after registration of all services, but just before the app handles a request.

### Creating a service

Read the [Services section of the Silex documentation](http://silex.sensiolabs.org/doc/services.html).

The naming convention is `service-name` followed by a dot and the type of thing: `controller`, `validator`, etc. For example, `blog-moderation.controller` registers a controller called blog-moderation.

### Creating routes

Read the [Routing section of the Silex documentation](http://silex.sensiolabs.org/doc/usage.html#routing). Our practice is to use `$app->match` rather than `$app->get`, `$app->post`, etc.

Make sure to understand the following methods:
 - `method`
 - `bind`
 - `assert`
 - [`convert`](silex-helpers/converter.md)

### Creating authorization rules

You can refer to the [Symfony Security documentation](http://symfony.com/doc/current/book/security.html#how-security-works-authentication-and-authorization) which is a pretty heavy read.

#### Overview of Firewalls and Access Rules

Firewalls and Access Rules serve the same purpose: determining whether a given request is authorized or not.

Authorized requests are forwarded to the appropriate controller. Unauthorized requests get an appropriate response automatically, such as a `HTTP 401` if the user needs to log in.

In our setup:
 - *Firewalls* tell the app whether the user must be logged in.
 - *Access Rules* tell the app what roles a user must have.

Firewalls and Access Rules can be simple or complex. The simplest definition of either one involves nothing but a URI path regex. If a given HTTP request matches the Firewall or Access Rule, it will be caught and constrained by that Firewall/Access Rule.

Consider the following firewall:

```PHP
$firewalls = [
    'listings' => [
        'pattern' => '^/listings',
        'oauth'   => true,
    ]
];
```

This firewall enforces that all requests to a URI matching the regex `^/listings` (i.e. every URI starting with `/listings`) must be made by a user authenticated via OAuth. More on the meaning of the `oauth` element in the next section.

Consider the following Access Rule:

```PHP
$accessRules = [
    ['^/listings/admin', 'listings_admin']
];
```

This access rule enforces that all requests to a URI matching the regex `^/listings/admin` must be made by a user with the `listings_admin` role.

Sometimes a Firewall or an Access Rule requires more specific masking than just a URI regex. In this case, a [`RequestMatcher`](http://api.symfony.com/2.5/Symfony/Component/HttpFoundation/RequestMatcher.html) object can be used. The `RequestMatcher` can constrain the following:

 - The URI of the request
 - Host (domain)
 - HTTP Method
 - IP Address
 - URI Attributes

Constraints specified in a `RequestMatcher` should be passed to the constructor:

```PHP
$matcher = new RequestMatcher('^/listings', 'puppies.com', ['GET', 'POST'], ['listingId' => '30']);
```

The above `RequestMatcher` only constrains requests made to URIs starting with `/listings` from the domain `puppies.com` that are either `GET` or `POST` requests with a `listingId` attribute set to `'30'`.

Refer to the [`RequestMatcher`](http://api.symfony.com/2.5/Symfony/Component/HttpFoundation/RequestMatcher.html) docs.

Read on for details specific to Firewalls or Access Rules.

#### Firewalls

##### Defining Firewalls In A Service Provider

Routes and the firewalls that constrain them should be defined in the same Service Provider.

In the Silex Application, all firewalls must be defined in `$app['security.firewalls']`. In order to define new firewalls without overwriting the old ones, use `$app->extend`:

```PHP
$app->extend('security.firewalls', function ($firewalls, $app) {
    $newFirewalls = [...];

    return array_merge($newFirewalls, $firewalls);
});
```

The `$firewalls` array is formatted as such:

```PHP
$firewalls = [
    'name-of-firewall' => [
        'pattern'  => '...',
        'listener' => true,
    ]
]
```

The name of the firewall should be lowercase, hyphen-separated words. It is simply a description of the firewall and is not used anywhere else.

##### Listeners

Listeners are the modules that define the constraints enforced by the firewall. There are three listeners:

- `oauth`
- `anonymous`
- `oauth-optional`

The `oauth` listener enforces the user to be logged in via oauth.

The `anonymous` listener gives anyone access, even if they are not logged in.

The `oauth-optional` listener gives anyone access, even if they are not logged in. If they are logged in, it enables the controller to see their account information.

To add a listener to a firewall, simply add an element to the firewall whose key is the listener name and value is `true`.

##### RequestMatchers in Firewalls

To use a `RequestMatcher` in a Firewall, simply set the Firewall's `pattern` to a `RequestMatcher` instead of a regex:

```PHP
$createBlogMatcher = new RequestMatcher('^/blogs$', null, ['POST']);

$app['security.firewalls'] = [
    'create-blog' => [
        'pattern' => $createBlogMatcher,
        'oauth'   => true,
    ]
]
```

Note that the above RequestMatcher only constrains `POST` requests made to the exact URI `/blogs`.

Synapse Base sets a catch-all firewall that requires OAuth authentication by default. As a result, developers only must set firewalls for public endpoints. The assumption is made that a majority of endpoints for most projects will be private. Projects with mostly public endpoints may want to override the default firewall ([`base.api`](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Application/Services.php) to be `anonymous`.

See [Symfony Security / Firewalls](http://symfony.com/doc/current/book/security.html#firewalls-authentication) documentation.

#### Access Rules

##### Defining Access Rules In A Service Provider

Routes and the access rules that constrain them should be defined in the same Service Provider.

In the Silex Application, all access rules must be defined in `$app['security.access_rules']`. In order to define new rules without overwriting the old ones, use `$app->extend`:

```PHP
$app->extend('security.access_rules', function ($rules, $app) {
    $newRules = [...];

    return array_merge($newRules, $rules);
});
```

`security.access_rules` is just an array of access rules. Each access rule is an array one to three elements:

1. The URI regex or `RequestMatcher`.
1. The role (or array of roles) allowed to make such requests. (Roles are strings.)
1. Optionally, the channel to enforce: `http`, `https`, or `null`. (Rarely used.)

Example access rules:

```PHP
$editBlogMatcher = new RequestMatcher('^/blog/[0-9]+$', null, ['PUT']);

$rules = [
    [$editBlogMatcher, ['blog_author', 'admin'], 'https'],
    ['^/blog/[0-9]+/views$', ['admin']],
];
```

The first access rule above rejects all `PUT` requests to `/blog/<id>` unless the user making the request has one of the specified roles (`blog_author` or `admin`) and is making the request over HTTPS.

The second access rule above rejects all requests to `/blog/<id>/views` unless the user making the request is an `admin`.

See [Symfony Security / Access Controls](http://symfony.com/doc/current/book/security.html#access-controls-authorization) documentation.

## Example

This example contains blog moderation endpoints which should only be accessible by users with the blog moderator role. It assumes the name of this role is defined in a class constant, `ROLE_BLOG_MODERATOR`.

### BlogServiceProvider
```PHP
<?php

namespace Application\Blog;

use Silex\ServiceProviderInterface;
use Silex\Application;

class BlogServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['blog.controller'] = $app->share(function ($app) {
            new BlogController($app['blog.mapper']);
        });

        $app['blog-moderation.controller'] = $app->share(function ($app) {
            new BlogModerationController($app['blog.mapper']);
        });

        $this->defineRoutes($app);
        $this->createFirewalls($app);
        $this->createAccessRules($app);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // noop
    }

    /**
     * Define all blog-related routes
     *
     * @param  Application $app
     */
    protected function defineRoutes(Application $app)
    {
        $app->match('/blogs/{blogId}', 'blog.controller:rest')
            ->method('GET')
            ->assert('blogId', '\d+')
            ->bind('blog-entity');

        $app->match('/blogs/{blogId}/approval', 'blog-moderation.controller:rest')
            ->method('PUT')
            ->assert('blogId', '\d+')
            ->bind('blog-entity');

        $app->match('/blogs/{blogId}/rejection', 'blog-moderation.controller:rest')
            ->method('PUT')
            ->assert('blogId', '\d+')
            ->bind('blog-entity');
    }

    /**
     * Create blog-related firewalls
     *
     * @param  Application $app
     */
    protected function createFirewalls(Application $app)
    {
        $app->extend('security.firewalls', function ($firewalls, $app) {
            $getBlogs = new RequestMatcher('^/blogs(/[0-9]+)?', null, ['GET']);

            $blogFirewalls = [
                'get-blogs' => [
                    'pattern'   => $getBlogs,
                    'anonymous' => true,
                ],
            ];

            return array_merge($blogFirewalls, $firewalls);
        });
    }

    /**
     * Define all blog-related access rules
     *
     * @param  Application $app
     */
    protected function createAccessRules(Application $app)
    {
        $app->extend('security.access_rules', function ($rules, $app) {
            $moderateBlogs = new RequestMatcher('^/blogs/[0-9]+/(approval|rejection)$', null, ['PUT']);

            $newRules = [
                [$moderateBlogs, RoleService::ROLE_BLOG_MODERATOR]
            ];

            return array_merge($newRules, $rules);
        });
    }
}
```
