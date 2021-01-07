<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Interface IPdoRecord
 * @package myth21\viewcontroller
 */
interface IPdoRecord
{
    public static function tableName();
    public static function availableAttributes();
    public function getPrimaryKey();
}