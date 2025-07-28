<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Describe interface for working PDO wrapper.
 */
interface PdoRecordInterface
{
    /**
     * Return specified table name of entity or default on base the entity name (Order::class return `order` table name).
     * Using on late static bindings.
     */
    public static function getTableName(): string;

    /**
     * Return array of available for init attributes.
     * Using on late static bindings.
     */
    public static function getAvailableAttributes(): array;

    /**
     * Return primary key of model to find unambiguous table row.
     */
    public function getPrimaryKey(): float|int|string|null;
}