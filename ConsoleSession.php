<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for session work in cli.
 */
class ConsoleSession extends AbstractSession
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $_SESSION = [];
        $this->data =& $_SESSION;
    }
}