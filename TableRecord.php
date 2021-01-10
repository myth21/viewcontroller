<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Interface TableRecord
 * @package myth21\viewcontroller
 */
interface TableRecord
{
    public static function tableName();
    public static function availableAttributes();
    public function getPrimaryKey();
}