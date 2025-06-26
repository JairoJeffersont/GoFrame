<?php

namespace GoFrame\Core\Helpers;

/**
 * Class Validation
 *
 * Provides utility methods for validating input data against a set of expected fields and rules.
 *
 * Example:
 * ```php
 * $validator = new Validation();
 * $input = ['name' => 'John', 'email' => 'john@example.com'];
 * $rules = [
 *     'name' => ['required' => true],
 *     'email' => ['required' => true],
 *     'age' => ['required' => true]
 * ];
 * $errors = $validator->validateFields($input, $rules);
 *
 * // Result:
 * // $errors = [
 * //     'missing_fields' => ['age']
 * // ]
 * ```
 *
 * @package GoFrame\Core\Helpers
 */
class Validation {
    /**
     * Validates an array of input fields against a set of expected fields and rules.
     *
     * It checks:
     * - Which required fields are missing from the input array.
     * - Which fields are present in the input array but not allowed by the expected rules.
     *
     * @param array $array_to_verify The input data array to be validated.
     * @param array $array_to_compare The expected fields and rules (e.g., ['field' => ['required' => true]]).
     *
     * @return array An associative array of validation errors. Possible keys:
     *               - 'missing_fields' => array of required fields not found in the input
     *               - 'incorrect_fields' => array of input fields not defined in the rules
     */
    public function validateFields($array_to_verify, $array_to_compare) {
        $errors = [];

        $missing_fields = [];

        foreach ($array_to_compare as $field => $rules) {
            if (
                !empty($rules['required']) &&
                !array_key_exists($field, $array_to_verify) &&
                (empty($rules['primary']) || $rules['primary'] === false)
            ) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            $errors['missing_fields'] = $missing_fields;
        }

        $forbidden_fields = [];

        foreach ($array_to_verify as $field => $value) {
            if (!array_key_exists($field, $array_to_compare)) {
                $forbidden_fields[] = $field;
            }
        }

        if (!empty($forbidden_fields)) {
            $errors['incorrect_fields'] = $forbidden_fields;
        }

        return $errors;
    }
}
