A simple example of an MVC app written PHP
=
The app defines controller and action to run.
Default params are used to run the app.

Requirements
-
* PDO extension
* PHP version 7.4 minimum

Installation
-
via composer
```
composer require myth21/viewcontroller
```
Or download project and use how you want.

App waiting for params
-
```php
<?php

declare(strict_types=1);

return [
    // common
    'isCleanUrlApply' => true, // https://en.wikipedia.org/wiki/Clean_URL
    'webControllerNameSpace' => '\\app\\controller\\',
    'defaultControllerName' => 'Index',
    'defaultActionName' => 'index',
    'exceptionControllerName' => 'Exception', // optional
    'exceptionMethodName' => 'handle', // optional
    'moduleNameSpace' => '\\module\\', // optional
    'moduleControllerNameSpace' => '\\controller\\', // optional
    // view
    'defaultViewDirName' => 'view',
    'defaultTemplateDirName' => 'default',
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
        '/module/[a:module]/' => [ // optional
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
    // modules (optional)
    'modules' => [
        'rest' => new class {}
    ],

    // console (optional)
    'consoleControllerNameSpace' => '\\admin\\console\\',
    'migrationNameSpace' => '\\admin\\console\\migration\\',
    'migrationDirName' =>  'migration',
];
```

License
-
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

The software is provided "as is", without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. 
In no event shall the authors or copyright holders be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the software.

