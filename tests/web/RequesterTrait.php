<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests\web;

use function file_get_contents;
use function stream_context_create;
use function unserialize;

use const HTTP_HOST;

trait RequesterTrait
{
    public function doRequest(string $urlPath)//: array
    {
        $url = HTTP_HOST . $urlPath;
        $streamContext = stream_context_create(['http' => ['method' => 'GET']]);
        $response = file_get_contents($url, $use_include_path = false, $streamContext);
        return unserialize($response, ['allowed_classes' => false]);
    }
}