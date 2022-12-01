<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Interface TableRecord
 */
interface TableRecord
{
    /**
     * Return specified table name of entity or default on base the entity name (Order::class return `order` table name).
     * Using on late static bindings.
     *
     * @return string
     */
    public static function getTableName();

    /**
     * Return array of available for init attributes.
     * Using on late static bindings.
     *
     * @return array
     */
    public static function getAvailableAttributes();

    /**
     * Return primary key of model to find unambiguous table row.
     *
     * @return int|float|string (real for sqlite)
     */
    public function getPrimaryKey();
}