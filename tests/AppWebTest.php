<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use myth21\viewcontroller\AppWeb;
use PHPUnit\Framework\TestCase;

class AppWebTest extends TestCase
{
    public function testFirst(): void
    {
//        var_dump(PHP_SESSION_NONE);
//        var_dump(session_status());
//
//        exit();

        @session_start();
        $app = new AppWeb([]);

        echo PHP_EOL;
        print_r(get_class($app));
        echo PHP_EOL;

        $this->assertTrue(true);
    }
}