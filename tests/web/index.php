<?php

declare(strict_types=1);

use myth21\viewcontroller\WebApp;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$params = require 'web-constants.php';

$app = new WebApp([
    'isCleanUrlApply' => true, // https://en.wikipedia.org/wiki/Clean_URL
    'routes' => [
        ROUTE_HOME,
        ROUTE_CONTROLLER_PAGE_SLUG,
        ROUTE_CONTROLLER_LIST,
        ROUTE_CONTROLLER_INDEX,
        ROUTE_CONTROLLER_SLUG,
    ],
]);

//ob_start();
//echo PHP_EOL;
//print_r($vendorParams);
//echo PHP_EOL;
//$ob = ob_get_clean();
//error_log($ob, 0);

$app->defineRequestParams();

$response[REQUEST_GET_PARAMS] = $app->getRequestGetParams();
$response[REQUEST_CONTROLLER_NAME] = $app->getRequestControllerName();
$response[REQUEST_ACTION_NAME] = $app->getRequestActionName();

echo serialize($response);