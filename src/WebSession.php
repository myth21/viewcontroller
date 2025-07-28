<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use RuntimeException;

/**
 * Responsible for session work in web environment.
 */
class WebSession extends AbstractSession
{
    public function __construct(array &$session = null)
    {
        if ($session === null) {
            if (headers_sent()) {
                throw new RuntimeException('Headers have already been sent');
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $session = &$_SESSION;
        }

        $this->data =& $session;
    }
}