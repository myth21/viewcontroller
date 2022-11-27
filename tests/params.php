<?php

declare(strict_types=1);

// here must be params that take participate in kernel only
return [
    // common
    'isCleanUrlApply' => true, // https://en.wikipedia.org/wiki/Clean_URL

    // сделать по умолчанию и задокументировать, либо перенести в параметры приложения, это должно определяться там
    'webControllerNameSpace' => '\\app\\controller\\',

    'defaultControllerName' => 'Index',
    'defaultActionName' => 'index',

    'moduleNameSpace' => '\\module\\',
//    'apiNameSpace' => '\\api\\',
    'apiNameSpace' => '\\app\\api\\',
    'moduleControllerNameSpace' => '\\controller\\',
    'apiControllerNameSpace' => '\\controller\\',
    'apiExceptionControllerNameSpace' => '\\app\\api\\',

    'throwableLogFileName' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . '.throwable_log',
    // Exception handling classes.
    'exceptionControllerName' => 'Exception',
    'exceptionMethodName' => 'handle',

    // routes
    'routes' => require 'routes.php',

    //api
//    'api' => require 'api.php',

    // modules
//    'modules' => require 'modules.php',

    // console
    'consoleControllerNameSpace' => '\\admin\\console\\',

    // JSON-RPC (using on ajax requests and standard response)
    // result - The data returned by the invoked method. This element is formatted as a JSON-stat object. If an error occurred while invoking the method, this member must not exist.[4]
    // error - An error object if there was an error invoking the method, otherwise this member must not exist.[5] The object must contain members code (integer) and message (string).[6] An optional data member can contain further server-specific data. There are pre-defined error codes which follow those defined for XML-RPC.
    // id - The id of the request it is responding to.
];
