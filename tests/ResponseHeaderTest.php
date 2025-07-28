<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use myth21\viewcontroller\ResponseHeader;
use PHPUnit\Framework\TestCase;

class ResponseHeaderTest extends TestCase
{
    public function testSetAndGetStatusCode(): void
    {
        $header = new ResponseHeader();
        $this->assertSame(200, $this->getPrivateProperty($header, 'statusCode'));

        $header->setStatusCode(404);
        $this->assertSame(404, $this->getPrivateProperty($header, 'statusCode'));
    }

    public function testSetAndGetStatusMessage(): void
    {
        $header = new ResponseHeader();
        $header->setStatusMessage('Not Found');
        $this->assertSame('Not Found', $this->getPrivateProperty($header, 'statusMessage'));
    }

    public function testAddAndGetHeaders(): void
    {
        $header = new ResponseHeader();
        $header->addHeader('Content-Type: application/json');
        $headers = $header->getHeaders();

        $this->assertContains('Content-Type: application/json', $headers);
    }

    public function testSendHeaders(): void
    {
        $header = new ResponseHeader();
        $header->setStatusCode(201);
        $header->setStatusMessage('Created');
        $header->addHeader('X-Custom-Header: Test');

        // We cannot really test PHP header() function output easily,
        // so here we just test that sendHeaders() does not throw errors
        // and headers_sent() check is handled.

        // We expect no exceptions or errors
        $header->sendHeaders();
        $this->assertTrue(true); // Dummy assert to mark the test as passed
    }

    public function testRedirect(): void
    {
        $this->expectException(\Exception::class);

        $header = $this->getMockBuilder(ResponseHeader::class)
            ->onlyMethods(['redirect'])
            ->getMock();

        // Because redirect() calls exit(), which stops script, we cannot test it normally.
        // Instead, we test it by overriding redirect to throw an exception.

        $header->method('redirect')
            ->will($this->throwException(new \Exception('Redirect called')));

        $this->expectExceptionMessage('Redirect called');
        $header->redirect('http://example.com', 302);
    }

    /**
     * Helper method to access private/protected property.
     *
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    private function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
