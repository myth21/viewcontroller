<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Throwable;

class AppParamDefault
{
    /**
     * @return array<string, mixed>
     */
    public static function getParams(): array
    {
        return [
            // Web
            AppParamInterface::WEB_CONTROLLER_NAMESPACE => '\\app\\controller\\',
            AppParamInterface::DEFAULT_CONTROLLER_NAME => 'Index',
            AppParamInterface::DEFAULT_ACTION_NAME => 'index',

            // Console
            AppParamInterface::CONSOLE_CONTROLLER_NAMESPACE => '\\admin\\console\\',

            // API
            AppParamInterface::API_NAME_NAMESPACE => '\\app\\api\\',
            AppParamInterface::API_CONTROLLER_NAMESPACE => '\\controller\\',
            AppParamInterface::API_EXCEPTION_CONTROLLER_NAMESPACE => '\\app\\api\\',

            // Routing
            AppParamInterface::IS_CLEAN_URL => false,
            AppParamInterface::ROUTES => [],

            // Exception handling
            AppParamInterface::EXCEPTION_CONTROLLER_NAME => 'Exception',
            AppParamInterface::EXCEPTION_METHOD_NAME => 'handle',

            // Logger
            AppParamInterface::CALLABLE_LOGGER => static function (Throwable $e): void {
                error_log($e->getMessage());
            }
        ];
    }
}