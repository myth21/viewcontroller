<?php

declare(strict_types=1);

$routes = [
    [
        'urlPattern' => '/',
        'method' => 'HEAD|GET|POST',
        'func' => static function () {
            return [
                'controller' => 'Index',
                'action' => 'index',
            ];
        },
        'name' => 'home',
    ],
    [
        'urlPattern' => '/Page/Index/',
        'method' => 'HEAD|GET',
        'func' => static function () {
            return [
                'controller' => 'Index',
                'action' => 'index',
            ];
        },
        'name' => 'index',
    ],
    [
        'urlPattern' => '/sitemap.xml',
        'method' => 'HEAD|GET',
        'func' => static function () {
            return [
                'controller' => 'Map',
                'action' => 'xml',
            ];
        },
        'name' => 'siteMapXml',
    ],
    [
        'urlPattern' => '/imagemap.xml',
        'method' => 'HEAD|GET',
        'func' => static function () {
            return [
                'controller' => 'Map',
                'action' => 'image',
            ];
        },
        'name' => 'imageMapXml',
    ],

    [
        'urlPattern' => '/ContactForm/send/',
        'method' => 'POST',
        'func' => static function () {
            return [
                'controller' => 'ContactForm',
                'action' => 'send',
            ];
        },
        'name' => 'contact-form-send',
    ],
    [
        'urlPattern' => '/Form/handle/',
        'method' => 'POST',
        'func' => static function () {
            return [
                'controller' => 'Form',
                'action' => 'handle',
            ];
        },
        'name' => 'form-handle',
    ],

    [
        'urlPattern' => '/file/[*:id_str]',
        'method' => 'HEAD|GET',
        'func' => static function ($id_str) {
            return [
                'controller' => 'File',
                'action' => 'view',
                'id_str' => $id_str
            ];
        },
        'name' => 'controller-slug-file',
    ],
    [
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
    ],

    [
        'urlPattern' => '/[a:controller]/list/',
        'method' => 'HEAD|GET',
        'func' => static function ($controller) {
            return [
                'controller' => $controller,
                'action' => 'list',
            ];
        },
        'name' => 'controller-list',
    ],
    [
        'urlPattern' => '/[a:controller]/',
        'method' => 'HEAD|GET',
        'func' => static function ($controller) {
            return [
                'controller' => $controller,
                'action' => 'index',
            ];
        },
        'name' => 'controller-index',
    ],
    [
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
    ],
    [
        // Exists because "default" name must be. Here match don't come by reason top rule /[a:controller]/[*:id_str]/
        'urlPattern' => '/[a:controller]/[a:action]/',
        'method' => 'HEAD|GET|POST',
        'func' => static function ($controller, $action) {
            return [
                'controller' => $controller,
                'action' => $action
            ];
        },
        'name' => 'default',
    ],
];

$routesLocal = [];
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'routes-local.php')) {
    $routesLocal = require 'routes-local.php';
}

return array_merge($routes, $routesLocal);
