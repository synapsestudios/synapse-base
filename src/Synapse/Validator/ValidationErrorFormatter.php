<?php

namespace Synapse\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ValidationErrorFormatter
{
    /**
     * Given a constraint violation list, convert it to an array to be returned
     * as JSON grouping the validation error messages neatly.
     *
     * Arrays of hashes can be validated with the Symfony Validation "Any" constraint.
     * Validation errors in that case look like the `array_of_hashes` property below.
     * In this example, the first two hashes had no validation errors so they are replaced
     * with `null`, while the third hash had validation errors for its `foo` and `bar` properties.
     *
     * Response format will look like this:
     * {
     *     "errors" : {
     *         "current_password" : [
     *             "This field is expected"
     *         ],
     *         "field_2" : [
     *             "This field cannot be the same as current_password",
     *             "This field must be less than 5 characters long"
     *         ],
     *         "array_of_hashes" : [
     *             null,
     *             null,
     *             {
     *                 "foo" : [
     *                     "This value is invalid"
     *                 ],
     *                 "bar" : [
     *                     "This field is not in the allowed range"
     *                 ]
     *             }
     *         ],
     *     }
     * }
     *
     * @param  ConstraintViolationListInterface $violationList List being formatted
     * @return array
     */
    public function groupViolationsByField(ConstraintViolationListInterface $violationList)
    {
        $errors = [];

        foreach ($violationList as $violation) {
            $errors = $this->addViolationToOutput($errors, $violation);
        }

        return $errors;
    }

    /**
     * Add a single constraint violation to a partially-built array of errors
     * in the format described in `groupViolationsByField`
     *
     * @param array                        $errors    Array of errors
     * @param ConstraintViolationInterface $violation Violation to add
     */
    protected function addViolationToOutput(array $errors, ConstraintViolationInterface $violation)
    {
        $path = $this->getPropertyPathAsArray($violation);

        // Drill into errors and find where the error message should be added, building nonexistent portions of the path
        $currentPointer = &$errors;

        while (count($path) > 0) {
            $currentField = array_shift($path);

            if (! array_key_exists($currentField, $currentPointer)) {
                $currentPointer[$currentField] = [];
            }

            if (is_numeric($currentField)) {
                for ($i = 0; $i < (int) $currentField; $i++) {
                    if (! array_key_exists($i, $currentPointer)) {
                        $currentPointer[(int) $i] = null;
                    }
                }

                // Sort the elements of this array so JsonResponse converts this to an array rather than an object
                ksort($currentPointer);
            }

            $currentPointer = &$currentPointer[$currentField];
        }

        // Container for current violation has been found; ensure it contains an array so multiple messages can be held
        if (! is_array($currentPointer)) {
            $currentPointer = [];
        }

        $currentPointer[] = $violation->getMessage();

        return $errors;
    }

    /**
     * Given a constraint violation, return its property path as an array
     *
     * Example:
     *     Property path of violation: "[foo][0][bar][baz]"
     *     Return value: array('foo', '0', 'bar', 'baz')
     *
     * @param  ConstraintViolationInterface $violation Violation whose path to return
     * @return array
     */
    protected function getPropertyPathAsArray(ConstraintViolationInterface $violation)
    {
        $path = $violation->getPropertyPath();

        // Remove outer brackets and explode fields into array
        $path = substr($path, 1, strlen($path) - 2);

        return explode('][', $path);
    }
}
