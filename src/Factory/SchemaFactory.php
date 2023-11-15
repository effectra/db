<?php

declare(strict_types=1);

namespace Effectra\Database\Factory;

use Effectra\Database\Contracts\SchemaInterface;
use Effectra\Database\Schema;

/**
 * Class SchemaFactory
 *
 * This factory class is responsible for creating instances of the Schema class.
 * 
 * @package Effectra\Database
 */
class SchemaFactory
{
    /**
     * Create a new Schema instance based on the provided structure.
     *
     * @param array $structure The structure array to initialize the Schema instance.
     *
     * @return SchemaInterface The newly created Schema instance.
     */
    public static function create(array $structure): SchemaInterface
    {
        return new Schema(...$structure);
    }
}
