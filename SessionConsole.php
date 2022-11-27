<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for session work in cli.
 */
class SessionConsole extends Session
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