<?php

declare(strict_types=1);

use myth21\viewcontroller\AbstractSession;
use myth21\viewcontroller\WebSession;
use PHPUnit\Framework\TestCase;

class WebSessionTest extends TestCase
{
    public function testSetHasGetDelete(): void
    {
        $session = AbstractSession::factory();

        $session->set('key', 'value');
        $this->assertTrue($session->has('key'));
        $this->assertEquals('value', $session->get('key'));
        $session->delete('key');
        $this->assertFalse($session->has('key'));
    }

    private array $mockSession = [];

    public function testSetAndGet(): void
    {
        $session = new WebSession($this->mockSession);
        $session->set('user_id', 42);

        $this->assertSame(42, $session->get('user_id'));
        $this->assertTrue($session->has('user_id'));
    }

    public function testUnset(): void
    {
        $this->mockSession = ['key' => 'value'];
        $session = new WebSession($this->mockSession);

        $session->delete('key');

        $this->assertFalse($session->has('key'));
    }

    public function testClear(): void
    {
        $this->mockSession = ['one' => 1, 'two' => 2];
        $session = new WebSession($this->mockSession);

        $session->clear();

        $this->assertSame([], $this->mockSession);
        $this->assertFalse($session->has('one'));
    }

    public function testGetAll(): void
    {
        $this->mockSession = ['a' => 1, 'b' => 2];
        $session = new WebSession($this->mockSession);

        $this->assertSame(['a' => 1, 'b' => 2], $session->getData());
    }
}