<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use myth21\viewcontroller\ConsoleApp;

class ConsoleAppTest extends TestCase
{
    public function testDefineRequestParamsParsesKeyValuePairs(): void
    {
        $_SERVER['argv'] = ['script.php', 'foo=bar', 'name=ivan'];

        $app = new ConsoleApp([]);
        $app->defineRequestParams();

        $expected = [
            'foo' => 'bar',
            'name' => 'ivan'
        ];

        $this->assertEquals($expected, $app->getRequestGetParams());
    }

    public function testDefineRequestParamsSkipsInvalidArguments(): void
    {
        $_SERVER['argv'] = ['script.php', 'invalid', 'key=value', 'wrong='];

        $app = new ConsoleApp([]);
        $app->defineRequestParams();

        $expected = [
            'key' => 'value',
            'wrong' => ''
        ];

        $this->assertEquals($expected, $app->getRequestGetParams());
    }
}
