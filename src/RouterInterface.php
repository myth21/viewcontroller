<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Exception;

/**
 * Describe router interface.
 */
interface RouterInterface
{
    /**
     * Reversed routing.
     * Generate the URL for a named route. Replace regexes with supplied parameters.
     *
     * @param string $routeName The name of the route.
     * @param array @params Associative array of parameters to replace placeholders with.
     *
     * @throws Exception
     * @return string The URL of the route with named parameters in place.
     */
    public function generate(string $routeName, array $params = []): string;

    /**
     * Map a route to a target.
     *
     * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string|null $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    public function map(string $method, string $route, $target, string $name = null): void;

    /**
     * Match a given Request Url against stored routes.
     *
     * @param string|null $requestUrl
     * @param string|null $requestMethod
     *
     * @return array|bool Array with route information on success, false on failure (no match).
     */
    public function match(string $requestUrl = null, string $requestMethod = null);
}