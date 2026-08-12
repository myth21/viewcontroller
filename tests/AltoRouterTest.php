<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use myth21\viewcontroller\AltoRouter;
use PHPUnit\Framework\TestCase;

class AltoRouterTest extends TestCase
{
    private function getRouter(string $methods, string $urlPattern = '/api/v1/tag/'): AltoRouter
    {
        $router = new AltoRouter();
        $router->map($methods, $urlPattern, static fn (): string => 'target', 'route-name');

        return $router;
    }

    public function testMatchesMappedMethod(): void
    {
        $match = $this->getRouter('GET')->match('/api/v1/tag/', 'GET');

        $this->assertIsArray($match);
        $this->assertSame('route-name', $match['name']);
    }

    public function testMatchesEveryMethodOfPipeSeparatedList(): void
    {
        foreach (['HEAD', 'GET'] as $requestMethod) {
            $this->assertIsArray($this->getRouter('HEAD|GET')->match('/api/v1/tag/', $requestMethod));
        }
    }

    public function testDoesNotMatchMethodNotMapped(): void
    {
        $this->assertFalse($this->getRouter('GET')->match('/api/v1/tag/', 'DELETE'));
    }

    /**
     * The method used to be looked for as a substring of the mapped list, so `E` reached a DELETE
     * route (and deleted the record), `T` reached a PATCH one, and `POS` a POST one.
     */
    public function testDoesNotMatchMethodThatIsOnlyASubstring(): void
    {
        $this->assertFalse($this->getRouter('DELETE')->match('/api/v1/tag/', 'E'));
        $this->assertFalse($this->getRouter('PATCH')->match('/api/v1/tag/', 'T'));
        $this->assertFalse($this->getRouter('POST')->match('/api/v1/tag/', 'POS'));
        $this->assertFalse($this->getRouter('HEAD|GET')->match('/api/v1/tag/', 'A'));
    }

    /**
     * Method names are case-sensitive, RFC 9110 3.2.1.
     */
    public function testDoesNotMatchLowerCaseRequestMethod(): void
    {
        $this->assertFalse($this->getRouter('GET')->match('/api/v1/tag/', 'get'));
    }

    /**
     * The mapped side is normalised, so a route written in lower case in a config keeps working.
     */
    public function testMatchesMethodMappedInLowerCase(): void
    {
        $this->assertIsArray($this->getRouter('get')->match('/api/v1/tag/', 'GET'));
        $this->assertIsArray($this->getRouter('head|get')->match('/api/v1/tag/', 'HEAD'));
    }

    public function testMatchesNamedParams(): void
    {
        $match = $this->getRouter('DELETE', '/api/v1/tag/[i:id]/')->match('/api/v1/tag/42/', 'DELETE');

        $this->assertIsArray($match);
        $this->assertSame(['id' => '42'], $match['params']);
    }
}
