Myth21 ViewController.
=
A simple MVC library written in PHP, jerboa style.
Provides a minimal framework to define controllers and actions to run.
Includes helper classes such as View for templating and PdoRecord for database record handling.

Requirements
-
- PHP 8.0 or higher
- PDO PHP extension enabled

Expected Project Structure Default (can be overridden)
-
This library expects the following directory and namespace layout based on your viewcontroller.php configuration:
```
project-root/
│
├── app/
│   ├── controller/               ← Web controllers (e.g., IndexController)
│   │   └── IndexController.php
│   │
│   └── api/                      ← API services and exception controllers
│       ├── SomeApiService.php
│       └── ExceptionController.php
│
├── admin/
│   └── console/                  ← CLI (console) controllers
│       └── SomeConsoleCommand.php
│
├── config/
│   ├── viewcontroller.php        ← Configuration for the library
│   ├── routes.php                ← Optional routing rules
│   └── local_app.php             ← Local environment overrides
│
├── public/                       ← Public web root (optional)
│   └── index.php                 ← Entry point for HTTP requests
│
├── storage/
│   └── .error_log                ← Error logs written by the library
│
├── vendor/                       ← Composer dependencies
│
└── composer.json
```



Installation
-
via composer
```
composer require myth21/viewcontroller
```
Or download project and use how you want.

App constructor is waiting for array of params specified in AppParamInterface
-
```php
<?php
// For example, config/vendor/viewcontroller.php

return [
    AppParamInterface::DEFAULT_CONTROLLER_NAME => 'Index',
    AppParamInterface::DEFAULT_ACTION_NAME => 'index',
    // ...
];
```

Testing
-
```
docker build -t vc-test -f Dockerfile.test .
docker run --rm -v $(pwd):/app -w /app vc-test composer install
```

License
-
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

The software is provided "as is", without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. 
In no event shall the authors or copyright holders be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the software.

