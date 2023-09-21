<?php

declare(strict_types=1);

namespace Effectra\Database\Data;

/**
 * DataRules class for defining validation rules and attributes for data.
 */
class DataRules
{
    /**
     * @var array $rules An array of rules .
     */
    protected array $rules = [];
    /**
     * @var array $attributes An array of attributes .
     */
    protected array $attributes = [];

    /**
     * Get the attribute name for a specified key.
     *
     * @param string $attribute The key for which to retrieve the attribute name.
     * @return string The attribute name.
     */
    public function getAttribute(string $attribute): string
    {
        return $this->attributes[$attribute];
    }

    /**
     * Get all defined attributes.
     *
     * @return array An array of attribute names.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Check if an attribute with the specified key exists.
     *
     * @param string $attribute The key to check.
     * @return bool True if the attribute exists, false otherwise.
     */
    public function hasAttribute(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * Set an attribute for a specific key.
     *
     * @param string $key The key for which to set the attribute.
     * @param string $attribute The attribute name to set.
     * @return self
     */
    public function setAttribute(string $key, string $attribute): self
    {
        $this->attributes[$key] = $attribute;
        return $this;
    }

    /**
     * Check if a rule with the specified key exists.
     *
     * @param string $key The key to check.
     * @return bool True if the rule exists, false otherwise.
     */
    public function hasRule(string $key): bool
    {
        return isset($this->rules[$key]);
    }

    /**
     * Get all defined rules.
     *
     * @return array An array of rules.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Get the rule for a specific key.
     *
     * @param string $key The key for which to retrieve the rule.
     * @return string The rule.
     */
    public function getRule(string $key): string
    {
        return $this->rules[$key];
    }

    /**
     * Set an array of rules.
     *
     * @param array $rules An array of rules to set.
     * @return self
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Set a rule for a specific key.
     *
     * @param string $key The key for which to set the rule.
     * @param string $rule The rule to set.
     * @return self
     */
    public function setRule(string $key, string $rule): self
    {
        $this->rules[$key] = $rule;
        return $this;
    }

    /**
     * Set a rule for a key to validate as JSON.
     *
     * @param string $key The key to validate as JSON.
     * @return self
     */
    public function json(string $key): self
    {
        return $this->setRule($key, 'json');
    }

    /**
     * Set a rule for a key to validate as an integer.
     *
     * @param string $key The key to validate as an integer.
     * @return self
     */
    public function integer(string $key): self
    {
        return $this->setRule($key, 'integer');
    }

    /**
     * Set a rule for a key to validate as a string.
     *
     * @param string $key The key to validate as a string.
     * @return self
     */
    public function string(string $key): self
    {
        return $this->setRule($key, 'string');
    }

    /**
     * Set a rule for a key to validate as a double.
     *
     * @param string $key The key to validate as a double.
     * @return self
     */
    public function double(string $key): self
    {
        return $this->setRule($key, 'double');
    }

    /**
     * Set a rule for a key to validate as a slug.
     *
     * @param string $key The key to validate as a slug.
     * @param string $delimiter The character used as a delimiter in the slug.
     * @return self
     */
    public function slug(string $key, string $delimiter = '-'): self
    {
        $this->setAttribute('slug', $delimiter);
        return $this->setRule($key, 'slug');
    }

    /**
     * Set a rule for a key to validate as a list.
     *
     * @param string $key The key to validate as a list.
     * @return self
     */
    public function list(string $key): self
    {
        return $this->setRule($key, 'list');
    }

    /**
     * Set a rule for a key to validate as a date.
     *
     * @param string $key The key to validate as a date.
     * @param string $to_format The desired date format.
     * @param string $from_format The source date format (default is 'Y-m-d H:i:s').
     * @return self
     */
    public function date(string $key, string $to_format, string $from_format = 'Y-m-d H:i:s'): self
    {
        $this->setAttribute('from_format', $from_format);
        $this->setAttribute('to_format', $to_format);
        return $this->setRule($key, 'date');
    }
}
