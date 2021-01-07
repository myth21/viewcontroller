<?php

declare(strict_types=1);

return [
    // params
    'isCleanUrlApply' => true, // https://en.wikipedia.org/wiki/Clean_URL
    'webControllerNameSpace' => '\\app\\controller\\',
    'defaultControllerName' => 'Index',
    'defaultActionName' => 'index',
    'exceptionControllerName' => 'Exception',
    'exceptionMethodName' => 'handle',
    'moduleNameSpace' => '\\module\\',
    'moduleControllerNameSpace' => '\\controller\\',
    // view
    'defaultViewDirName' => __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR,
    'defaultTemplateDirName' => 'default' . DIRECTORY_SEPARATOR,
    'defaultTemplateFileName' => 'template',
    // routes
    'routes' => [
        '/' => [
            'name' => 'home',
            'method' => 'GET|POST',
            'func' => function() {
                return [
                    'controller' => 'Home',
                    'action' => 'index',
                ];
            },
        ],
        '/module/[a:module]/' => [
            'name' => 'module',
            'method' => 'GET|POST|PUT|DELETE',
            'func' => function ($module) {
                return [
                    'module' => $module,
                    'controller' => 'Index',
                    'action' => 'index',
                ];
            }
        ],
        '/[a:controller]/[a:action]/' => [
            'name' => 'default',
            'method' => 'GET|POST',
            'func' => function ($controller, $action) {
                return [
                    'controller' => $controller,
                    'action' => $action
                ];
            },
        ],
    ],
    // modules
    'modules' => [
        'rest' => new class {}
    ],

    // console
    'migrationDirName' => __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'migration',
    'migrationNameSpace' => '\\admin\\console\\migration\\',
    'consoleControllerNameSpace' => '\\admin\\console\\',
];
