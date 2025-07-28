<?php

declare(strict_types=1);

use myth21\viewcontroller\UrlQueryManagerTrait;
use PHPUnit\Framework\TestCase;

class UrlQueryManagerTest extends TestCase
{
    use UrlQueryManagerTrait;

    public function testCreateUrl(): void
    {
        $url = $this->createUrl();
        $this->assertEquals('', $url);

        $url = $this->createUrl('ControllerName');
        $this->assertEquals('?controller=ControllerName', $url);

        $url = $this->createUrl('ControllerName', 'ActionName');
        $this->assertEquals('?controller=ControllerName&action=ActionName', $url);

        $url = $this->createUrl('ControllerName', 'ActionName', [
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $this->assertEquals('?controller=ControllerName&action=ActionName&key1=value1&key2=value2', $url);
    }
}