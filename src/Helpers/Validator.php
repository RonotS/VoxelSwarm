<?php

declare(strict_types=1);

namespace Swarm\Helpers;

/**
 * Validator — Input validation helpers.
 *
 * Returns an array of errors. Empty array = valid.
 */
class Validator
{
    /**
     * Validate data against rules.
     *
     * @param array $data  The input data (e.g., $_POST)
     * @param array $rules Keyed by field name, value is a pipe-separated string or array of rules
     * @return array<string, string> Field => error message. Empty if valid.
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value    = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = self::checkRule($field, $value, $rule, $params, $data);
                if ($error) {
                    $errors[$field] = $error;
                    break; // One error per field
                }
            }
        }

        return $errors;
    }

    private static function checkRule(string $field, mixed $value, string $rule, array $params, array $data): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        return match ($rule) {
            'required' => (is_null($value) || (is_string($value) && trim($value) === ''))
                ? "{$label} is required."
                : null,

            'string' => (!is_string($value) && !is_null($value))
                ? "{$label} must be text."
                : null,

            'email' => (is_string($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
                ? "Please enter a valid email address."
                : null,

            'min' => (is_string($value) && mb_strlen($value) < (int) ($params[0] ?? 0))
                ? "{$label} must be at least {$params[0]} characters."
                : null,

            'max' => (is_string($value) && mb_strlen($value) > (int) ($params[0] ?? PHP_INT_MAX))
                ? "{$label} must be no more than {$params[0]} characters."
                : null,

            default => null,
        };
    }
}
