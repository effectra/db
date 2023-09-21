<?php

declare(strict_types=1);

namespace Effectra\Database\Data;

use Effectra\Database\Exception\DataValidatorException;

/**
 * DataValidator class for validating and processing data.
 */
class DataValidator
{
    /**
     * @param mixed $data The data to be validated.
     */
    private $data;
    /**
     * @var array $errors The error messages
     */
    private array $errors = [];

    /**
     * Constructor for DataValidator.
     *
     * @param mixed $data The data to be validated.
     *
     * @throws DataValidatorException If the data is not a non-empty array.
     */
    public function __construct($data)
    {

        if (is_object($data)) {
            $data = (array)$data;
        }

        if (!is_array($data) || empty($data)) {
            throw new DataValidatorException('Data must be a non-empty array.');
        }

        $this->data = $data;
    }

    /**
     * Set an error message.
     *
     * @param string $error The error message to set.
     */
    public function setError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Check if the data is associative.
     *
     * @return bool True if the data is associative, false otherwise.
     */
    public function isAssoc(): bool
    {
        foreach ($this->data as $key => $value) {
            if ($key === '') {
                $this->setError('key must be a non-empty string');
                return false;
            }
            if (!is_string($key)) {
                $this->setError('key must be  a string');
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the data is an array of associative arrays.
     *
     * @return bool True if the data is an array of associative arrays, false otherwise.
     */
    public function isArrayOfAssoc(): bool
    {
        foreach ($this->data as $item) {
            if (!is_array($item)) {
                $this->setError('item must be array');
                return false;
            }
            if (empty($item)) {
                $this->setError('key must be a non-empty item');
                return false;
            }
            if (!$this->isAssocArray($item)) {
                $this->setError('item must be associative');
                return false;
            }
        }
        return true;
    }

    /**
     * Check if an array is associative.
     *
     * @param array $array The array to check.
     *
     * @return bool True if the array is associative, false otherwise.
     */
    private function isAssocArray(array $array): bool
    {
        foreach ($array as $key => $value) {
            if ($key === '') {
                $this->setError('key must be  a non-empty string');
                return false;
            }
            if (!is_string($key)) {
                $this->setError('key must be  a string');
                return false;
            }
        }
        return true;
    }

    /**
     * Validate the data and throw an exception if errors are present.
     *
     * @throws DataValidatorException If errors are present during validation.
     * @return void
     */
    public function validate():void
    {
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                throw new DataValidatorException("Error Processing Data. $error", 1);
            }
        }
    }
}
