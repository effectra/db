<?php

declare(strict_types=1);

namespace Effectra\Database\Data;

use Effectra\Database\Exception\DataValidatorException;

/**
 * DataOptimizer class for optimizing and transforming data based on defined rules.
 */
class DataOptimizer
{
    /**
     * @var mixed $data The data to be optimized.
     */
    private $data;

    /**
     * @var array $errors The error messages.
     */
    private array $errors = [];

    /**
     * @var DataRules $data_rule The data validation rules.
     */
    private DataRules $data_rule;

    /**
     * Constructor for DataOptimizer.
     *
     * @param mixed $data The data to be optimized.
     *
     * @throws DataValidatorException If the data is not a non-empty array.
     */
    public function __construct($data)
    {
        if (is_object($data)) {
            $data = (array)$data;
        }

        $this->data = $data;

        $this->data_rule = new DataRules();
    }

    /**
     * Check if the string is valid JSON.
     *
     * @param string $value The string to check.
     * @return bool True if the string is valid JSON, false otherwise.
     */
    public function isJson(string $value): bool
    {
        json_decode($value);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Check if the data is a collection of multiple rows.
     *
     * @return bool True if the data is a collection, false otherwise.
     */
    public function isMultipleRows(): bool
    {
        return is_array($this->data) && count($this->data) > 1;
    }

    /**
     * Convert text to a slug.
     *
     * @param string $text The text to convert.
     * @param string $delimiter The character used as a delimiter in the slug.
     * @return string The generated slug.
     */
    public function textToSlug($text, $delimiter = '-')
    {
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $text = strtolower($text);
        $text = str_replace(' ', $delimiter, $text);

        return $text;
    }

    /**
     * Apply a validation rule to a value and transform it accordingly.
     *
     * @param string $rule The validation rule.
     * @param mixed $value The value to validate and transform.
     * @return mixed The transformed value.
     */
    public function applyRule(string $rule, $value)
    {
        if ($rule === 'json' && $this->isJson($value)) {
            return json_decode($value);
        }

        if ($rule === 'integer' && is_numeric($value)) {
            return (int) $value;
        }

        if ($rule === 'float' && is_numeric($value)) {
            return (float) $value;
        }

        if ($rule === 'string') {
            return (string) $value;
        }

        if ($rule === 'slug') {
            return $this->textToSlug($value, $this->data_rule->getAttribute('slug'));
        }

        if ($rule === 'list' && $this->isJson($value)) {
            return join(',', (array) json_decode($value));
        }

        if ($rule === 'date') {
            $date = \DateTime::createFromFormat($this->data_rule->getAttribute('from_format'), $value);
            if ($date) {
                return $date->format($this->data_rule->getAttribute('to_format'));
            }
        }

        return $value;
    }

    /**
     * Optimize the data based on defined rules.
     *
     * @param callable $rules A callback function to define rules using DataRules.
     * @return array The optimized data.
     */
    public function optimize($rules): array
    {
        call_user_func($rules, $this->data_rule);
        $this->data_rule->getRules();

        if ($this->isMultipleRows($this->data)) {
            foreach ($this->data as &$item) {
                foreach ($item as $key => $value) {
                    if ($this->data_rule->hasRule($key)) {
                        $item[$key] = $this->applyRule($this->data_rule->getRule($key), $value);
                    }
                }
            }
        }

        return $this->data;
    }
}
