<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Describes app run.
 */
interface AppInterface
{
    /**
     * Run request processing logic.
     */
    public function run();
}