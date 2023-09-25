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
     * @param string $key The key to validate and transform.
     * @param mixed $value The value to validate and transform.
     * @return object The transformed value.
     */
    public function applyRule(string $rule, string $key, $value)
    {

        if ($rule === 'json' && $this->isJson($value)) {
            $value = json_decode($value);
        }

        if ($rule === 'integer' && is_numeric($value)) {
            $value = (int) $value;
        }

        if ($rule === 'float' && is_numeric($value)) {
            $value = (float) $value;
        }

        if ($rule === 'string') {
            $value = (string) $value;
        }

        if ($rule === 'slug') {
            $value = $this->textToSlug($value, $this->data_rule->getAttribute('slug'));
        }

        if ($rule === 'list' && $this->isJson($value)) {
            $value = join(',', (array) json_decode($value));
        }

        if ($rule === 'date') {
            $date = \DateTime::createFromFormat($this->data_rule->getAttribute('from_format'), $value);
            if ($date) {
                $value = $date->format($this->data_rule->getAttribute('to_format'));
            }
        }

        if ($rule === 'strip_tags' && is_string($value)) {
            $value = strip_tags($value,$this->data_rule->getAttribute('allowed_tags'));
        }

        if ($rule === 'replace_value') {
            $value = $this->data_rule->getAttribute('replace_new_value') ;
        }

        if ($rule === 'replace_value_by_new') {
            $value = $this->data_rule->getAttribute('replace_value_default') === $value ?  $this->data_rule->getAttribute('replace_value_new') : $value;
        }

        if ($rule === 'replace_text') {
            $value = str_replace(
                $this->data_rule->getAttribute('replace_text_default'),
                $this->data_rule->getAttribute('replace_text_new'),
                $value
            );
        }

        $removed = null;

        if ($rule === 'rename') {
            $removed = $key;
            $key = $this->data_rule->getAttribute('new_key_name_' . $key);
        }

        return (object) [
            'key' =>  $key,
            'value' => $value,
            'remove' => $removed
        ];
    }

    /**
     * Optimize the data based on defined rules.
     *
     * @param callable $rules A callback function to define rules using DataRules.
     * @return array The optimized data.
     */
    public function optimize($rules): array
    {
        $data = [];
        call_user_func($rules, $this->data_rule);
        $this->data_rule->getRules();

        if ((new DataValidator($this->data))->isArrayOfAssoc()) {
            foreach ($this->data as &$item) {
                foreach ($item as $key => $value) {
                    if ($this->data_rule->hasRule($key)) {

                        $result =  $this->applyRule($this->data_rule->getRule($key), $key, $value);

                        $value = $result->value;
                        $key =  $result->key;
                    }
                    $item = array_merge($item, [$key => $value]);
                    if(isset($result->remove)){
                        unset( $item[$result->remove]);
                    }
                }
                $data[] = $item;
            }
        }

        return $data;
    }
}
