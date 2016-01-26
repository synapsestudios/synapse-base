CHANGELOG
=========
## [v4.1.1](https://github.com/synapsestudios/synapse-base/compare/v4.1.0...v4.1.1) (2016-01-26)

* Fixed mapper fetched results hydrated as dirty

## [v4.1.0](https://github.com/synapsestudios/synapse-base/compare/v4.0.6...v4.1.0) (2016-01-22)

* Add patch method to Updater to only save changed values

## [v4.0.6](https://github.com/synapsestudios/synapse-base/compare/v4.0.5...v4.0.6) (2015-08-26)

* Added Beta Environment

## [v4.0.5](https://github.com/synapsestudios/synapse-base/compare/v4.0.4...v4.0.5) (2015-08-26)

* Added missing use statement to RollbarHandler

## [v4.0.4](https://github.com/synapsestudios/synapse-base/compare/v4.0.3...v4.0.4) (2015-08-19)

* Send 'Unverified user' error message instead of 'Invalid credentials' when unverified user attempts to login

## [v4.0.3](https://github.com/synapsestudios/synapse-base/compare/v4.0.2...v4.0.3) (2015-07-16)

* Fixed Rollbar not flushing errors

## [v4.0.2](https://github.com/synapsestudios/synapse-base/compare/v4.0.1...v4.0.2) (2015-06-30)

* Fixed user verification bug

## [v4.0.1](https://github.com/synapsestudios/synapse-base/compare/v4.0.0...v4.0.1) (2015-06-30)

* Added High-Level Documentation for the repo
* Fixed required verification config setting not working on token endpoint

## [v4.0.0](https://github.com/synapsestudios/synapse-base/compare/v3.0.1...v4.0.0) (2015-06-09)

* Fixed Silex requests with expired access tokens redirected to /login
* UserEntity's `enabled` and `verified` values are set as booleans (`if ($user->getEnabled())`)
* Fixed ResetPasswordControllerTest::testPutDeletesToken relying on two entities being created within the same millisecond
* Implement WebTestCase for route testing #215
* Update phpUnit to 4.5

## [v3.0.1](https://github.com/synapsestudios/synapse-base/compare/v2.1.2...v3.0.0) (2015-05-05)

* Throws exception if email whitelist trap is misconfigured
* Passes CliCommandOption to AbstractCliCommand::getBaseCommand

## [v3.0.0](https://github.com/synapsestudios/synapse-base/compare/v2.1.2...v3.0.0) (2015-05-05)

* Adds a new config file, `login.php`, which allows for requiring a user to be verified before login

## [v2.1.2](https://github.com/synapsestudios/synapse-base/compare/v2.1.1...v2.1.2) (2015-04-25)

* Fixed user creation with an empty string for the email address.

## [v2.1.1](https://github.com/synapsestudios/synapse-base/compare/v2.1.0...v2.1.1) (2015-04-23)

* Setting role on user entity, not just database, in role service.

## [v2.1.0](https://github.com/synapsestudios/synapse-base/compare/v2.0.0...v2.1.0) (2015-04-16)

* Removed View for DB Upgrades.

## [v2.0.0](https://github.com/synapsestudios/synapse-base/compare/v1.4.1...v2.0.0) (2015-03-29)

* Implemented OAuth2 Spec More Accurately.
* Added base TestCase class.
* Facilitating testing of time-related logic with TimeAwareInterface.
* Fixed RowExists constraint giving ContextErrorException when passing field in the options.
* Fixed RowNotExists constraint returning message from RowExists if no message was supplied.
* Renamed Application\Routes to Application\ErrorHandlers.
* Focusing insertRow and updateRow methods.
* Changed user() to getUser() in SecurityAwareTrait.
* Removed PivotInserterTrait, PivotUpdaterTrait, and PivotDeleterTrait.

## [v1.4.2](https://github.com/synapsestudios/synapse-base/compare/v1.4.1...v1.4.2) (2015-04-15)

* Patched 1.4.x to fix RowExists for older projects.

## [v1.4.1](https://github.com/synapsestudios/synapse-base/compare/v1.4.0...v1.4.1) (2015-03-19)

* Fix bug with RowExists and other related constraints.

## [v1.4.0](https://github.com/synapsestudios/synapse-base/compare/v1.3.4...v1.4.0) (2015-03-16)

* Add test helper InjectMockTransactionTrait
* Have ControllerTestCase use InjectMockTransactionTrait
* Add output when starting a specific migration
* Add times for each migration and total time
* Change in migration class naming for easier sorting
* Add createdDatetimeColumn and updatedDatetimeColumn to AbstractMapper
* Deprecate createdTimestampColumn and updatedTimestampColumn
* Add starting message to migrations
* Rewrite UpdaterTrait and DeleterTrait to support tables whose primary key is not `id`
* Move nested transaction simulation to Transaction class
* Add bind() values to OAuth server routes
* Data Object: Add support for fluent interface in setAs* methods
* Allow RowExists, RowsExist, and RowNotExists constraints to match on custom field names
* Create SecurityContextMockInjector trait

## [v1.3.4](https://github.com/synapsestudios/synapse-base/compare/v1.3.3...v1.3.4) (2015-02-10)

* Allow 'order' option to be specified on `FinderTrait::findBy` just like `findAllBy`
* Add TransactionAwareInterface and TransactionAwareTrait
* Have AbstractController implement TransactionAwareInterface
* Auto-inject Transaction object into classes implementing TransactionAwareInterface

## [v0.2.6](https://github.com/synapsestudios/synapse-base/compare/v0.2.5...v0.2.6) - 2014-08-08

* Disallowed `--drop-tables` option in `install:run` command
* Added missing feature to update social login provider tokens upon login

## [v0.2.5](https://github.com/synapsestudios/synapse-base/compare/v0.2.4...v0.2.5) - 2014-08-06

* Added MapperTestCase::setMockResults()
* Removed MapperTestCase::setUpMockResultCallback()
* Minor refactoring


## [v0.2.4](https://github.com/synapsestudios/synapse-base/compare/v0.2.2...v0.2.4) - 2014-08-04

* Fixed bug in user endpoint that caused endpoint to return validation errors no matter what

## [v0.2.2](https://github.com/synapsestudios/synapse-base/compare/v0.2.1...v0.2.2) - 2014-07-25

* Added AbstractSecurityAwareTestCase to provide access to a mock security context and the currently logged in user to both ControllerTestCase and MapperTestCase

## [v0.2.1](https://github.com/synapsestudios/synapse-base/compare/v0.2.0...v0.2.1) - 2014-07-25

* Added changelog
