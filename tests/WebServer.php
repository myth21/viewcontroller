<?php

declare(strict_types=1);

use myth21\viewcontroller\AppWeb;

$pathToVendorAutoload = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require $pathToVendorAutoload;

$vendorParams = require 'params.php';
$app = new AppWeb($vendorParams);
$app->defineRequestParams();

$response['RequestGetParams'] = $app->getRequestGetParams();
$response['RequestControllerName'] = $app->getRequestControllerName();
$response['RequestActionName'] = $app->getRequestActionName();
$response['RequestMethod'] = $app->getRequestMethod();
$response['RequestUri'] = $app->getRequestUri();
$response['RequestUriPath'] = $app->getRequestUriPath();
$response['RequestApiName'] = $app->getRequestApiName();
$response['Api'] = $app->getApi();

echo serialize($response);