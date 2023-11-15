<?php

declare(strict_types=1);

namespace Effectra\Database;

/**
 * Class Utils
 *
 * This class provides utility functions for common tasks.
 * 
 * @package Effectra\Database
 */
class Utils
{
    /**
     * Convert a snake_case string to CamelCase.
     *
     * @param string $string The snake_case string.
     *
     * @return string The CamelCase string.
     */
    public static function snakeToCamel(string $string): string
    {
        $words = explode('_', $string);
        $camelWords = array_map('ucfirst', $words);
        return lcfirst(implode('', $camelWords));
    }

    /**
     * Convert a CamelCase string to snake_case.
     *
     * @param string $inputString The CamelCase string.
     *
     * @return string The snake_case string.
     */
    public static function camelToUnderscore(string $inputString): string
    {
        $outputString = preg_replace_callback('/([a-z])([A-Z])/', function ($matches) {
            return $matches[1] . '_' . strtolower($matches[2]);
        }, $inputString);

        return $outputString;
    }

    /**
     * Get the current date and time in the 'Y-m-d H:i:s' format.
     *
     * @param int|null $timestamp The timestamp to convert. If null, the current timestamp is used.
     *
     * @return string The formatted date and time string.
     */
    public static function now(?int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}
