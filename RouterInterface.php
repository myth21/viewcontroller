<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class Router
 * @package myth21\viewcontroller
 */
interface RouterInterface
{
    public function generate(string $routeName, array $params = []): string;

    public function map(string $method, string $route, $target, string $name = null): void;

    /**
     * @return array|bool Array with route information on success, false on failure (no match).
     */
    public function match();
}