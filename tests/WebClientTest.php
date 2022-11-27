<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function print_r;
use function stream_context_create;
use function unserialize;

use const PHP_EOL;

class WebClientTest extends TestCase
{
    public function testIndex(): void
    {
        $url = 'http://localhost:8000/asd/';
        $streamContext = stream_context_create(['http' => ['method'=>'GET']]);
        $response = file_get_contents($url, $use_include_path = false, $streamContext);

        echo PHP_EOL;
        print_r(unserialize($response));
        echo PHP_EOL;

        $this->assertTrue(true);
    }
}