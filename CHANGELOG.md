CHANGELOG
=========

Upcoming Release - up to 8cf951d
--------------------------------
* Add TransactionAwareInterface and TransactionAwareTrait
* Auto-inject Transaction object into classes implementing TransactionAwareInterface
* Have AbstractController implement TransactionAwareInterface
* Add test helper InjectMockTransactionTrait
* Have ControllerTestCase use InjectMockTransactionTrait
* Add output when starting a specific migration
* Add times for each migration and total time

0.2.6 (2014-08-08)
-----------------

* Disallowed `--drop-tables` option in `install:run` command
* Added missing feature to update social login provider tokens upon login

0.2.5 (2014-08-06)
------------------

* Added MapperTestCase::setMockResults()
* Removed MapperTestCase::setUpMockResultCallback()
* Minor refactoring


0.2.4 (2014-08-04)
------------------

* Fixed bug in user endpoint that caused endpoint to return validation errors no matter what

0.2.2 (2014-07-25)
------------------

* Added AbstractSecurityAwareTestCase to provide access to a mock security context and the currently logged in user to both ControllerTestCase and MapperTestCase

0.2.1 (2014-07-25)
------------------

* Added changelog
