# Validator

## What is it?

A validator validates the correctness of the data provided, and returns a list of errors if validation fails.

## When and where to use it

Validation is appropriate in situations where input cannot be controlled. HTTP requests are uncontrolled input. The main use case for validators in an HTTP API environment is validation of an HTTP request body.

Whenever validating a request body, just perform the validation in the controller. Pass the `Request` object to `AbstractRestController::getContentAsArray` to retrieve the JSON request body as a PHP array. If there are validation errors, pass them to `AbstractController::createConstraintViolationResponse` to create an appropriate `422` response in a unified format.

## How to use it

We use the Symfony Validation component packaged with Silex. See the [Symfony Validation](http://symfony.com/doc/current/book/validation.html) documentation for an overview of validators and a list of pre-built validation constraints provided by Symfony Validation.

### Synapse\Validator\AbstractArrayValidator

This is a class which abstracts away the boilerplate code involved in validating an array with Symfony Validation.

#### AbstractArrayValidator::getConstraints(array $contextData, AbstractEntity $contextEntity = null)

In your validator, extend `AbstractArrayValidator` and define this method to return an array of validation constraints. The returned array should be keyed identically to the array that will be validated.

#### AbstractArrayValidator::validate(array $values, AbstractEntity $contextEntity = null)

To perform validation, pass this method the array being validated and an optional context entity if it is needed in `getConstraints`.

An array of validation constraint violations are returned. If there are no violations, an empty array is returned.

### Setting constraints on an array

Define a single constraint on an array element by setting that element to a new `Constraint` object in `getConstraints`. Define multiple constraints on an array element by setting that element to an array of `Constraint` objects.

Symfony's constraints are in `Symfony\Component\Validator\Constraints`. More constraints are available in `Synapse\Validator\Constraints`.

Some constraints require no variables or input such as `NotBlank`, which constrains the input to be a non-blank value. Instantiate a `NotBlank` constraint like this: `new Assert\NotBlank()`.

Other constraints accept required and/or optional arguments to be passed in to their constructor. For example, the `Choice` validator defines a list of valid choices:

```PHP
new Assert\Choice(['choices' => ['foo', 'bar', 'baz']])
```

The [`RowExists`](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Validator/Constraints/RowExists.php), [`RowsExist`](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Validator/Constraints/RowsExist.php), and [`RowNotExists`](https://github.com/synapsestudios/synapse-base/blob/master/src/Synapse/Validator/Constraints/RowNotExists.php) validators from [Synapse Base](https://github.com/synapsestudios/synapse-base) require an [`AbstractMapper`](mapper.md) to be passed as the first argument, with an optional `$options` array as the second. These constraints assert that the mapper finds a row that exists (or doesn't) with the `id` value in the given field. The `$options` array can contain a `field` element which specifies the field to search for, if not `id`.

In order to use more complex where clauses the set `filterCallback` on the `$options` array. The `filterCallback` function will be passed the value that is being validated.

## Examples

### BlogValidator
```PHP
<?php

namespace Application\Blog;

use Symfony\Component\Validator\Constraints as Assert;
use Synapse\Validator\Constraints as SynapseAssert;
use Synapse\Validator\AbstractArrayValidator;
use Synapse\Entity\AbstractEntity;
use Synapse\Stdlib\Arr;

class BlogValidator extends AbstractArrayValidator
{
    /**
     * @var BlogMapper
     */
    protected $mapper;

    /**
     * @var SlugMapper
     */
    protected $slugMapper;

    public function __construct(BlogMapper $mapper, SlugMapper $slugMapper)
    {
        $this->mapper = $mapper;
    }

    protected function getConstraints(array $contextData, AbstractEntity $contextEntity = null)
    {
        return [
            'title'    => [
                new Assert\NotBlank(),
                new SynapseAssert\RowNotExists($this->mapper, [
                    'field' => 'title'
                ]),
            ],
            'content'  => new Assert\NotBlank(),
            'category' => new Assert\NotBlank(),
            'slug'     => new SynapseAssert\RowNotExists($this->slugMapper, [
                // a slug can only be used once per category
                'filterCallback' => function ($value) use ($contextData) {
                    return [
                        'slug'     => $value,
                        'category' => Arr::get($contextData, 'category'),
                    ]
                }
            ]),
        ];
    }
}
```

### BlogController
```PHP
<?php

namespace Application\Blog;

use Synapse\Controller\AbstractRestController;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends AbstractRestController
{
    /**
     * @var BlogValidator
     */
    protected $validator;

    public function __construct(BlogValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Create a new blog
     *
     * @param  Request $request
     * @return Response
     */
    public function post(Request $request)
    {
        $data = $this->getContentAsArray($request);

        $errors = $this->validator->validate($data);

        if (count($errors) > 0) {
            return $this->createConstraintViolationResponse($errors);
        }

        // Other code which creates the blog
    }
}
```
