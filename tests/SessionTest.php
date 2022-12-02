<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use myth21\viewcontroller\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testSetHasGetDelete(): void
    {
        $session = Session::factory();

        $session->set('key', 'value');

        $this->assertTrue($session->has('key'));

        $this->assertEquals('value', $session->get('key'));

        $session->delete('key');

        $this->assertFalse($session->has('key'));
    }
}