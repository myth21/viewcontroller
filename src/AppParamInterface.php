<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Describes app params.
 */
interface AppParamInterface
{
    /**
     * https://en.wikipedia.org/wiki/Clean_URL.
     */
    public const IS_CLEAN_URL = 'isCleanUrlApply';

    /**
     * Namespace to find web controllers dir (PSR-4).
     */
    public const WEB_CONTROLLER_NAMESPACE = 'webControllerNameSpace';

    /**
     * Default controller name if controller is not defined via URL param.
     */
    public const DEFAULT_CONTROLLER_NAME = 'defaultControllerName';

    /**
     * Default action name if action is not defined via URL param.
     */
    public const DEFAULT_ACTION_NAME = 'defaultActionName';

    /**
     * Exception controller name to handle exceptions in your app.
     */
    public const EXCEPTION_CONTROLLER_NAME = 'exceptionControllerName';

    /**
     * Exception action name of the exception controller to handle exceptions.
     */
    public const EXCEPTION_METHOD_NAME = 'exceptionMethodName';

    /**
     * Namespace to find console controllers dir (PSR-4).
     */
    public const CONSOLE_CONTROLLER_NAMESPACE = 'consoleControllerNameSpace';

    /**
     * Namespace to find api dir (PSR-4).
     */
    public const API_NAME_NAMESPACE = 'apiNameSpace';

    /**
     * Namespace to find api controller dir (PSR-4).
     */
    public const API_CONTROLLER_NAMESPACE = 'apiControllerNameSpace';

    /**
     * Namespace to find api exception controller (PSR-4).
     */
    public const API_EXCEPTION_CONTROLLER_NAMESPACE = 'apiExceptionControllerNameSpace';

    /**
     * Array of routes.
     */
    public const ROUTES = 'routes';

    /**
     * Array of APIs.
     */
    public const API = 'api';
    public const VERSION = 'version';
    public const CONTROLLER = 'controller';
    public const ACTION = 'action';

    /**
     * Callable entity to write in log.
     */
    public const CALLABLE_LOGGER = 'callableLogger';
}