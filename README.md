# Synapse Base

[![Build Status](https://api.shippable.com/projects/540e72f23479c5ea8f9e4fc3/badge?branchName=master)](https://app.shippable.com/projects/540e72f23479c5ea8f9e4fc3/builds/latest)

## Overview

Synapse Base is a bootstrapping library for PHP applications build in the [Silex](http://silex.sensiolabs.org/) microframework. It's intended for REST APIs that serve and consume JSON.

This library provides an opinionated, secure starting point for your REST API using the following tools and libraries:

 - [Silex](https://github.com/silexphp/Silex)
 - bshaffer's [OAuth2 server](https://github.com/bshaffer/oauth2-server-php)
 - [Zend DB](https://github.com/zendframework/zend-db)
 - chrisboulton's [php-resque](https://github.com/chrisboulton/php-resque)
 - Mandrill [PHP SDK](https://bitbucket.org/mailchimp/mandrill-api-php.git) for emails
 - [Symfony](https://github.com/symfony/symfony)'s Console, Security, and Validator components
 - [Monolog](https://github.com/Seldaek/monolog) for logging
 - [And many others](https://github.com/synapsestudios/synapse-base/blob/master/composer.json)

## Setting up your Project

### Quick Start

For new projects, just clone the [API Template](https://github.com/synapsestudios/api-template) as a starting point and `composer install`.

### Expectations of the Library

#### Architectural Expectations

1. A MySQL server for app data
2. A Redis server for job queues

#### Project Codebase Expectations

(If you just use API Template, you don't need to know most of this.)

1. Some named constants are [expected to exist](https://github.com/synapsestudios/api-template/blob/master/public/index.php).
2. The `APP_ENV` environment variable should be set. (To `development`, `production`, or `staging`.)
3. Specific config files should be set up in `[APPDIR]/config/`. To set up environment-specific configuration overrides, put identical config files in `[APPDIR]/config/development/`, etc. [See API Template for examples.](https://github.com/synapsestudios/api-template/tree/master/config)

### Setting Up Required Config Files

The [default config files](https://github.com/synapsestudios/api-template/tree/master/config) contain sufficient documentation in DocBlocks. Just read those.

## Database Interaction

### Database Installs and Migrations

Use the [console](https://github.com/synapsestudios/api-template/blob/master/console) utility in API Template to perform DB installs and migrations.

 - Install a clean copy of the database: `./console install:run --drop-tables`
 - Create a migration: `./console migrations:create "Add inventory table"`
 - Install any migrations that haven't been applied: `./console migrations:run` (Or just `./console install:run`)
 - Generate a new database install file from the current state of the database: `./console install:generate`

When you create a new migration, it's created in `[APPDIR]/src/Application/Migrations/`. Use the Zend DB Adapter to perform queries [like this](https://github.com/synapsestudios/api-template/blob/a2163910a1033f3064d125bba366c2b556945aea/src/Application/Migrations/CreateUsertokentypesTable20140507193727.php).

**Note about Generating a Database Install File:** When you run `./console install:generate`, it generates 2 files -- (1) a `DbStructure.sql` file with the table structure based on the current snapshot of your database, and (2) a `DbData.sql` file with data from specific tables. Specify which tables in the [install config](https://github.com/synapsestudios/api-template/blob/master/config/install.php).

### How to Read/Write from the Database

Use Mappers [like this](https://github.com/synapsestudios/synapse-base/commit/f905195c81a8786d47c32a0dadb1bf61e9107ccb#diff-06e71af6dcfaea597c8b184610d4f2fd) to perform database queries. Database results are returned as [Entities](https://github.com/synapsestudios/synapse-base/blob/aceb9e1dec716597e576ee22859ddc20e6a04d7b/src/Synapse/User/UserEntity.php).

## Authentication / Login System

bshaffer's [OAuth2 server](https://github.com/bshaffer/oauth2-server-php) is used for authentication. The user `POST`s to `/oauth/token` with their email/password and receives an access token which can be used to make requests. (Per the OAuth2 specification.)

In order to secure endpoints, the [Symfony Security](http://symfony.com/doc/current/book/security.html) module is used. [Firewalls](https://github.com/synapsestudios/synapse-base/blob/b3e3db0d17237de20cfa5a365b5822d34013c1f9/src/Synapse/User/UserServiceProvider.php#L138) are used to constrain an endpoint to logged in users or to make it public. [Access Rules](https://github.com/synapsestudios/synapse-base/blob/b3e3db0d17237de20cfa5a365b5822d34013c1f9/src/Synapse/User/UserServiceProvider.php#L160) are used to make an endpoint accessible only to users with certain roles. Read the Symfony Security docs for more details.

**Notes:**

1. When you specify a listener in a firewall (`'anonymous' => true`, `'oauth-optional' => true`), the code that runs is in the [Listeners](https://github.com/synapsestudios/synapse-base/tree/master/src/Synapse/Security/Firewall). (These are added in the [OAuth2\SecurityServiceProvider](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/OAuth2/SecurityServiceProvider.php).)
2. There is a [catch-all firewall](https://github.com/synapsestudios/synapse-base/blob/5cdd7abf7caddae16bbcb8d1ec5aee71a8aa907e/src/Synapse/Application/Services.php#L109) that constrains all endpoints to be protected by OAuth (non-public) unless specified otherwise. [More details here.](https://github.com/synapsestudios/synapse-base/blob/5355dc2c53e6398bc1ef8e1c8f8390224580e267/src/Synapse/Application/Services.php#L68)

## Utility Classes

### [Array Helper](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Stdlib/Arr.php)

```PHP
$people = [
    ['name' => 'Linus',   'age' => 10],
    ['name' => 'Brendan', 'age' => 11],
    ['name' => 'Rasmus',  'age' => 12, 'colors' => ['Red', 'Green', 'Blue']],
];

Arr::get($people[0], 'name');     // Linus
Arr::get($people[3], 'name');     // null
Arr::pluck($people, 'name');      // ['Linux', 'Brendan', 'Rasmus'];
Arr::path($people, '2.colors.1'); // Green
```

### [DataObject](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Stdlib/DataObject.php)

Use these to encapsulate concepts and typehint them in your app.

```PHP
class Car extends DataObject
{
    protected $object = [
        'make'    => null,
        'model'   => null,
        'year'    => null,
        'totaled' => false,
    ];
}

$car = new Car(['make' => 'Toyota']);
$car->setModel('Corolla');
$car->getMake();    // Toyota
$car->getModel();   // Corolla
$car->getYear();    // null
$car->getTotaled(); // false

// These are helpful for typehinting purposes to make your code more robust
function purchaseCar(Car $car) {
    // Do stuff
}
```

### Test Helpers

Various [abstract PHPUnit test cases](https://github.com/synapsestudios/synapse-base/tree/master/src/Synapse/TestHelper) are available for controllers, mappers, etc., to make it easier to test them.
