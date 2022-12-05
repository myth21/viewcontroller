<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use RuntimeException;

/**
 * Responsible for session work in web environment.
 */
class WebSession extends AbstractSession
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (headers_sent()) {
            throw new RuntimeException('Headers have already been sent');
        }

        session_start();

        $this->data =& $_SESSION;
    }
}