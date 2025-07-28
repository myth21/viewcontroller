<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use BadMethodCallException;
use RuntimeException;
use Throwable;
use function array_key_exists;
use function array_key_first;
use function is_callable;
use function method_exists;
use function ucfirst;
use const PHP_SAPI;

/**
 * App must define request params, route, choose controller and action to run, pass self in app as dependency injection.
 * App must not know about db, view...
 */
abstract class AbstractApp implements AppInterface
{
    use UrlQueryManagerTrait;

    /**
     * RouterInterface responsible for routing.
     */
    private ?RouterInterface $router = null;

    /**
     * API entity (model) name is specified in config params.
     */
    private ?string $apiName = null;

    /**
     * Controller name without Controller word.
     */
    private ?string $controllerName = null;

    /**
     * Full controller class name for handling.
     */
    private ?string $controllerClassName = null;

    /**
     * Stack of throwable objects.
     */
    private array $throwableChain = [];

    /**
     * Request GET params.
     */
    protected array $requestGetParams = [];

    /**
     * Request POST params.
     */
    protected array $requestPostParams = [];

    /**
     * Route elements. It is used on clean url.
     */
    protected array $routes = [];

    /**
     * Namespace to handle request.
     */
    protected ?string $controllerNameSpace = null;

    /**
     * Controller to handle request.
     */
    protected object $controller;

    /**
     * Action of a controller to handle request.
     */
    protected ?string $actionName = null;

    /**
     * @var array<string, mixed>
     */
    protected array $params = [];

    /**
     * Defining request params for console or web application.
     */
    abstract public function defineRequestParams(): void;

    /**
     * Return controller name space according to app type (console|web) and PSR-4.
     */
    abstract protected function getControllerNameSpace(): string;

    /**
     * Output handled request result.
     * @param mixed $out
     */
    abstract protected function out(mixed $out): void;

    /**
     * Run a controller and return result of processing.
     */
    abstract protected function runController(): mixed;

    /**
     * Class constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = array_replace_recursive(AppParamDefault::getParams(), $params);
    }

    /**
     * Create concrete app object depending on the environment.
     *
     * @param array $params
     * @return ConsoleApp|WebApp
     */
    public static function factory(array $params): ConsoleApp|WebApp
    {
        $className = (PHP_SAPI === 'cli') ? ConsoleApp::class : WebApp::class;

        return new $className($params);
    }

    /**
     * Run request processing of the concrete app object.
     */
    public function run(): void
    {
        $this->defineRequestParams();

        try {
            $this->defineControllerNameSpace();

            $this->setControllerName($this->getRequestControllerName());
            $this->setActionName($this->getRequestActionName());

            $this->defineControllerClassName();

            $this->checkActionAvailableToRun();

            $out = $this->runController();

        } catch (Throwable $e) {

            $this->writeLog($e);

            // Clear previous buffer outputs.
            while (ob_get_level()) {
                ob_end_clean();
            }

            $this->addThrowable($e);

            if ($this->isRequestToApi()) {
                $this->defineControllerNameSpace($this->getParam(AppParamInterface::API_EXCEPTION_CONTROLLER_NAMESPACE));
            } else {
                $this->defineControllerNameSpace();
            }

            $this->setControllerName($this->getParam(AppParamInterface::EXCEPTION_CONTROLLER_NAME));
            $this->setActionName($this->getParam(AppParamInterface::EXCEPTION_METHOD_NAME));

            $this->defineControllerClassName();

            $out = $this->runController();
        }

        $this->out($out);
    }

    public function writeLog(Throwable $e): void
    {
        $throwableLogger = $this->getParam(AppParamInterface::CALLABLE_LOGGER);

        if (null === $throwableLogger) {
            return;
        }

        if (!is_callable($throwableLogger)) {
            throw new RuntimeException('callableLogger is not callable');
        }

        $throwableLogger($e);
    }

    /**
     * Define routeing map for router.
     * The App dependents on AltoRouter.
     *
     * @throws Throwable
     */
    public function createRoutes(): array
    {
        $this->router = new AltoRouter();

        $routes = $this->params[AppParamInterface::ROUTES] ?: [];

        foreach ($routes as $route) {
            $this->router->map($route['method'], $route['urlPattern'], $route['func'], $route['name']);
        }

        $match = $this->router->match();

        if (!$match) {
            throw new BadMethodCallException('Route not found', 404);
        }

        if (!is_array($match) && !is_callable($match['target'])) {
            throw new BadMethodCallException('Route "' . $match . '" not found', 404);
        }

        return call_user_func_array($match['target'], $match['params']);
    }

    /**
     * Return route elements.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Is API request according to the router.
     */
    public function isRequestToApi(): bool
    {
        return array_key_exists($this->getApiVersionKey(), $this->routes) && array_key_exists($this->getApiKey(), $this->routes);
    }

    /**
     * Define controller name space.
     */
    protected function defineControllerNameSpace(string $controllerNameSpace = null): void
    {
        if ($controllerNameSpace) {
            $this->controllerNameSpace = $controllerNameSpace;
            return;
        }

        if ($this->isRequestToApi()) {
            // API is created by url identification, not via media, vdn... headers.
            $apiNameSpace = $this->params[AppParamInterface::API_NAME_NAMESPACE];
            $apiVersion = $this->getRequestGetParam($this->getApiVersionKey());
            $apiEntityName = $this->routes[$this->getApiKey()];
            $apiControllerNameSpace = $this->params[AppParamInterface::API_CONTROLLER_NAMESPACE];
            $this->controllerNameSpace = $apiNameSpace .  $apiEntityName . '\\' . $apiVersion . $apiControllerNameSpace;
            return;
        }

        $this->controllerNameSpace = $this->getControllerNameSpace();
    }

    protected function getControllerName(): string
    {
        return $this->controllerName . ucfirst($this->getControllerKey());
    }

    protected function getControllerClassName(): string
    {
        return $this->controllerNameSpace . $this->getControllerName();
    }

    /**
     * Define full class name of a controller for running.
     */
    protected function defineControllerClassName(): void
    {
        $controllerClassName = $this->getControllerClassName();

        $this->setControllerClassName($controllerClassName);
    }

    /**
     * Check available action for running in controller.
     *
     * @throws BadMethodCallException
     */
    protected function checkActionAvailableToRun(): void
    {
        // Using this function will use any registered autoloaders if the class has not already been known.
        // It uses psr-4.
        if (!method_exists($this->controllerClassName, $this->actionName)) {
            $message = 'Action ' . $this->controllerClassName . '::' . $this->actionName . ' is not available to run';
            throw new BadMethodCallException($message, 404);
        }
    }

    /**
     * Create controller object to handle request.
     */
    protected function createController(): object
    {
        $controller = new $this->controllerClassName($this);
        $this->controller = $controller;

        return $this->controller;
    }

    /**
     * Return router.
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Return is clean url applied.
     */
    public function isCleanUrlApply(): bool
    {
        return $this->params[AppParamInterface::IS_CLEAN_URL] ?? false;
    }

    /**
     * Set request param. It can be http or console param.
     *
     * @param string $key
     * @param string $value
     */
    protected function setRequestGetParam(string $key, string $value): void
    {
        $this->requestGetParams[$key] = $value;
    }


    /**
     * Set namespace class name of a controller (\app\controller\ControllerClass) to create object for processing request.
     *
     * @param string $name
     */
    private function setControllerClassName(string $name): void
    {
        $this->controllerClassName = $name;
    }

    /**
     * Set controller name (e.g. Article) to create object for processing request.
     *
     * @param string $name
     */
    private function setControllerName(string $name): void
    {
        $this->controllerName = $name;
    }

    /**
     * Return request controller name (e.g. Article).
     */
    public function getRequestControllerName(): string
    {
        return $this->requestGetParams[$this->getControllerKey()] ?? $this->getParam(AppParamInterface::DEFAULT_CONTROLLER_NAME);
    }

    /**
     * Set action name (e.g. view|list) of a controller.
     * @param string $name
     */
    private function setActionName(string $name): void
    {
        $this->actionName = $name;
    }

    /**
     * Return request action name (e.g. view|list) from request params.
     */
    public function getRequestActionName(): string
    {
        return $this->requestGetParams[$this->getActionKey()] ?? $this->getParam(AppParamInterface::DEFAULT_ACTION_NAME);
    }

    /**
     * Return config param by key.
     *
     * @param string $name
     * @return mixed
     */
    public function getParam(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Return config params.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Return request param by key.
     *
     * @param string $name
     * @return string|null
     */
    public function getRequestGetParam(string $name): null|string|array
    {
        return $this->requestGetParams[$name] ?? null;
    }

    /**
     * Return request get params.
     */
    public function getRequestGetParams(): array
    {
        return $this->requestGetParams;
    }

    /**
     * Return request POST param by key.
     */
    public function getRequestPostParam(string $name): ?string
    {
        return $this->requestPostParams[$name] ?? null;
    }

    /**
     * Return request POST params.
     */
    public function getRequestPostParams(): array
    {
        return $this->requestPostParams;
    }

    /**
     * Add throwable object in chain.
     *
     * @param Throwable $e
     */
    protected function addThrowable(Throwable $e): void
    {
        $this->throwableChain[] = $e;
    }

    /**
     * Return first throwable object from chain.
     */
    public function getFirstThrowable(): Throwable
    {
        if ($this->throwableChain === []) {
            throw new RuntimeException('Throwable chain is empty. No exceptions recorded.');
        }

        $key = array_key_first($this->throwableChain);

        return $this->throwableChain[$key];
    }

    /**
     * Return last throwable object from chain.
     */
    public function getLastThrowable(): Throwable
    {
        if ($this->throwableChain === []) {
            throw new RuntimeException('Throwable chain is empty. No exceptions recorded.');
        }

        $key = array_key_last($this->throwableChain);

        return $this->throwableChain[$key];
    }

    /**
     * Return throwable objects from chain.
     */
    public function getThrowableChain(): array
    {
        return $this->throwableChain;
    }

    /**
     * Return API.
     */
    public function getApi(): ?object
    {
        $api = $this->getParam($this->getApiKey());

        return $api[$this->apiName] ?? null;
    }

    /**
     * Set entity (model) name of an API.
     * @param string $name
     */
    protected function setApiName(string $name): void
    {
        $this->apiName = $name;
    }

    /**
     * Return API entity (model) name.
     */
    public function getRequestApiName(): ?string
    {
        return $this->requestGetParams[$this->getApiKey()] ?? null;
    }
}