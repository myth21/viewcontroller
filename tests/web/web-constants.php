<?php

declare(strict_types=1);

defined('HTTP_HOST') or define('HTTP_HOST', 'http://localhost:8000');
defined('REQUEST_GET_PARAMS') or define('REQUEST_GET_PARAMS', 'requestGetParams');
defined('REQUEST_CONTROLLER_NAME') or define('REQUEST_CONTROLLER_NAME', 'requestControllerName');
defined('REQUEST_ACTION_NAME') or define('REQUEST_ACTION_NAME', 'requestActionName');

defined('GET_PARAM_CONTROLLER') or define('GET_PARAM_CONTROLLER', 'controller');
defined('GET_PARAM_ACTION') or define('GET_PARAM_ACTION', 'action');
defined('GET_PARAM_ID_STR') or define('GET_PARAM_ID_STR', 'id_str');

defined('ROUTE_HOME') or define('ROUTE_HOME', [
    'urlPattern' => '/',
    'method' => 'HEAD|GET|POST',
    'func' => static function () {
        return [
            'controller' => 'Index',
            'action' => 'index',
        ];
    },
    'name' => 'home',
]);
defined('ROUTE_CONTROLLER_PAGE_SLUG') or define('ROUTE_CONTROLLER_PAGE_SLUG', [
    'urlPattern' => '/Page/[*:id_str]/',
    'method' => 'HEAD|GET',
    'func' => static function ($id_str) {
        return [
            'controller' => 'Page',
            'action' => 'view',
            'id_str' => $id_str
        ];
    },
    'name' => 'controller-page-slug',
]);
defined('ROUTE_CONTROLLER_LIST') or define('ROUTE_CONTROLLER_LIST', [
    'urlPattern' => '/[a:controller]/list/',
    'method' => 'HEAD|GET',
    'func' => static function ($controller) {
        return [
            'controller' => $controller,
            'action' => 'list',
        ];
    },
    'name' => 'controller-list',
]);
defined('ROUTE_CONTROLLER_INDEX') or define('ROUTE_CONTROLLER_INDEX', [
    'urlPattern' => '/[a:controller]/',
    'method' => 'HEAD|GET',
    'func' => static function ($controller) {
        return [
            'controller' => $controller,
            'action' => 'index',
        ];
    },
    'name' => 'controller-index',
]);
defined('ROUTE_CONTROLLER_SLUG') or define('ROUTE_CONTROLLER_SLUG', [
    'urlPattern' => '/[a:controller]/[*:id_str]/', // this with "-"
    'method' => 'HEAD|GET',
    'func' => static function ($controller, $id_str) {
        return [
            'controller' => $controller,
            'action' => 'view',
            'id_str' => $id_str
        ];
    },
    'name' => 'controller-slug',
]);