## Unit Testing ##

We unit test our code because:

1. It prevents regressions
1. It facilitates refactoring

To get the full benefit, testing first is encouraged. Your workflow should be:

1. Write a failing test
1. Write the minumum amount of code needed to get that test to pass
1. Refactor
1. Repeat

This applies to new features as well as bug fixes.

Testing first has many purported benefits, but perhaps the most important is that
it ensures that the code actually does end up getting tested. As is often the case
with procrastination, leaving the tests for later often results in them never
getting written.

### Additional Guidelines

* Tests should be short (10 or fewer lines is ideal, 20 is too long) and easy to read.  Extract descriptively named methods if necessary to make them shorter.
* Failing tests should never be merged to the develop or master branches.
* Whitespace should be used to demarcate the three sections of each test: setup, execution, assertion.

### Language Specific Guidelines and Conventions

* [PHP](php.md)

## External Resources

### [Twelve benefits of TDD](http://sd.jtimothyking.com/2006/07/11/twelve-benefits-of-writing-unit-tests-first/)

