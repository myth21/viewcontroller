<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests\web;

use myth21\viewcontroller\AltoRouter;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

use const GET_PARAM_ACTION;
use const GET_PARAM_CONTROLLER;
use const GET_PARAM_ID_STR;
use const REQUEST_ACTION_NAME;
use const REQUEST_CONTROLLER_NAME;
use const REQUEST_GET_PARAMS;
use const ROUTE_CONTROLLER_INDEX;
use const ROUTE_CONTROLLER_LIST;
use const ROUTE_CONTROLLER_PAGE_SLUG;
use const ROUTE_HOME;

class WebClientTest extends TestCase
{
    use RequesterTrait;

    protected static array $params;

    public static function setUpBeforeClass(): void
    {
        require 'web-constants.php';
    }

    private function getMappedRouter(array $route): AltoRouter
    {
        $router = new AltoRouter();
        $router->map($route['method'], $route['urlPattern'], $route['func'], $route['name']);
        return $router;
    }

    public function testIndex(): void
    {
        $route = ROUTE_HOME;

        $urlPath = $this->getMappedRouter($route)->generate($route['name']);

        $responseData = $this->doRequest($urlPath);

        $this->assertEquals('Index', $responseData[REQUEST_CONTROLLER_NAME]);
        $this->assertEquals('index', $responseData[REQUEST_ACTION_NAME]);
    }

    public function testControllerPageSlug(): void
    {
        $route = ROUTE_CONTROLLER_PAGE_SLUG;

        $idStr = 'MyPageId';

        $urlPath = $this->getMappedRouter($route)->generate($route['name'], [
            GET_PARAM_ID_STR => $idStr
        ]);

        $responseData = $this->doRequest($urlPath);

        $funcParams = (new ReflectionFunction($route['func']))->getParameters();

        // Route params are assigned for get params
        $this->assertEquals('Page', $responseData[REQUEST_GET_PARAMS][GET_PARAM_CONTROLLER]);
        $this->assertEquals('view', $responseData[REQUEST_GET_PARAMS][GET_PARAM_ACTION]);
        $this->assertEquals($idStr, $responseData[REQUEST_GET_PARAMS][$funcParams[0]->name]);

        $this->assertEquals('Page', $responseData[REQUEST_CONTROLLER_NAME]);
        $this->assertEquals('view', $responseData[REQUEST_ACTION_NAME]);
    }

    public function testControllerList(): void
    {
        $route = ROUTE_CONTROLLER_LIST;

        $controller = 'Product';

        $urlPath = $this->getMappedRouter($route)->generate($route['name'], [
            GET_PARAM_CONTROLLER => $controller
        ]);

        $responseData = $this->doRequest($urlPath);

        // Route params are assigned for get params
        $this->assertEquals($controller, $responseData[REQUEST_GET_PARAMS][GET_PARAM_CONTROLLER]);
        $this->assertEquals('list', $responseData[REQUEST_GET_PARAMS][GET_PARAM_ACTION]);

        $this->assertEquals($controller, $responseData[REQUEST_CONTROLLER_NAME]);
        $this->assertEquals('list', $responseData[REQUEST_ACTION_NAME]);
    }

    public function testControllerIndex(): void
    {
        $route = ROUTE_CONTROLLER_INDEX;

        $controller = 'Product';

        $urlPath = $this->getMappedRouter($route)->generate($route['name'], [
            GET_PARAM_CONTROLLER => $controller
        ]);

        $responseData = $this->doRequest($urlPath);

        // Route params are assigned for get params
        $this->assertEquals($controller, $responseData[REQUEST_GET_PARAMS][GET_PARAM_CONTROLLER]);
        $this->assertEquals('index', $responseData[REQUEST_GET_PARAMS][GET_PARAM_ACTION]);

        $this->assertEquals($controller, $responseData[REQUEST_CONTROLLER_NAME]);
        $this->assertEquals('index', $responseData[REQUEST_ACTION_NAME]);
    }

    public function testControllerSlug(): void
    {
        $route = ROUTE_CONTROLLER_SLUG;

        $controller = 'Product';
        $idStr = 'a-product-id-str';

        $urlPath = $this->getMappedRouter($route)->generate($route['name'], [
            GET_PARAM_CONTROLLER => $controller,
            GET_PARAM_ID_STR => $idStr
        ]);

        $responseData = $this->doRequest($urlPath);

        $funcParams = (new ReflectionFunction($route['func']))->getParameters();

        // Route params are assigned for get params
        $this->assertEquals($controller, $responseData[REQUEST_GET_PARAMS][GET_PARAM_CONTROLLER]);
        $this->assertEquals('view', $responseData[REQUEST_GET_PARAMS][GET_PARAM_ACTION]);
        $this->assertEquals($idStr, $responseData[REQUEST_GET_PARAMS][$funcParams[1]->name]);

        $this->assertEquals($controller, $responseData[REQUEST_CONTROLLER_NAME]);
        $this->assertEquals('view', $responseData[REQUEST_ACTION_NAME]);
    }
}