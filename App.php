<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Throwable;
use BadMethodCallException;

use function method_exists;

/**
 * App must define request params, route, choose controller and action to run, pass self in app as dependency injection.
 * App must not know about db, view...
 *
 * @property Throwable[] $throwableChain
 * @property array $params Config params.
 */
abstract class App implements Engine
{
    use UrlQueryManager;

    /**
     * Config params.
     */
//    private array $params;

    /**
     * RouterInterface responsible for routing.
     */
    private ?RouterInterface $router;

    /**
     * Module entity (model) name is specified in config params.
     */
    private ?string $moduleName;

    /**
     * API entity (model) name is specified in config params.
     */
    private ?string $apiName;

    /**
     * Controller name without Controller word.
     */
    private ?string $controllerName;

    /**
     * Full controller class name for handling.
     */
    private ?string $controllerClassName;

    /**
     * Stack of throwable objects.
     */
    private array $throwableChain;

    /**
     * Request params from GET, POST and others.
     */
    protected array $requestParams = [];
    protected array $requestGetParams = [];
    protected array $requestPostParams = [];

    /**
     * Controller to handle request.
     */
    protected object $controller;

    /**
     * Action of controller to handle request.
     */
    protected string $actionName;

    /**
     * Init request params for App.
     */
    abstract protected function defineRequestParams(): void;

    /**
     * Return controller namespace according to PSR-4.
     */
    abstract protected function getControllerNameSpace(): string;

    /**
     * @param mixed $out
     * @return mixed
     * // TODO check for moving output in index.
     */
    abstract protected function out($out);

    public function __construct(private array $params)
    {
        //$this->params = $params;
    }

    /**
     * Create concrete app object.
     */
    public static function factory(array $params): static
    {
        $className = (PHP_SAPI === 'cli') ? AppConsole::class : AppWeb::class;
        return new $className($params);
    }

    /**
     * Execute request processing of the concrete app object.
     * TODO return
     */
    public function run()
    {
        $this->defineRequestParams();

        try {
            if ($this->isCleanUrlApply() && $route = $this->createRoute()) {
                foreach ($route as $param => $value) {
                    $this->setRequestParam($param, $value);
                }
            }

            $this->setModuleName($this->getRequestModuleName());
            $this->setApiName($this->getRequestApiName());
            $this->setControllerName($this->getRequestControllerName());
            $this->setActionName($this->getRequestActionName());
            $this->defineControllerClassName();

            $this->checkActionAvailableToRun();

            $out = $this->runController();

        } catch (Throwable $e) {

            // clear previous buffer outputs
            while (ob_get_level()) {
                ob_end_clean();
            }
            $this->addThrowable($e);
            $this->setControllerName($this->getParam('exceptionControllerName'));
            $this->defineControllerClassName(true);
            $this->setActionName($this->getParam('exceptionMethodName'));

            $out = $this->runController();
        }

        $this->out($out);
    }

    /**
     * Define routeing map for router.
     *
     * @throws Throwable
     */
    protected function createRoute(): array
    {
        $this->router = new AltoRouter();
        foreach ($this->params['routes'] as $urlPattern => $handler) {
            $this->router->map($handler['method'], $urlPattern, $handler['func'], $handler['name']);
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

    /* TODO what type of phpdoc comments?
     * Define controller class name for running.
     */
    protected function defineControllerClassName(bool $doSearchInApp = false): void
    {
        $controllerName = $this->getControllerName() . ucfirst($this->getControllerKey());

        if (!$doSearchInApp && $this->apiName) {

            $controllerClassName = $this->params['apiNameSpace'] . $this->apiName . $this->params['apiControllerNameSpace'] . $controllerName;

        } elseif (!$doSearchInApp && $this->moduleName) {

            $controllerClassName = $this->params['moduleNameSpace'] . $this->moduleName . $this->params['moduleControllerNameSpace'] . $controllerName;

        } else {

            $controllerClassName = $this->getControllerNameSpace() . $controllerName;
        }

        $this->setControllerClassName($controllerClassName);
    }

    /**
     * Check available action for running in controller.
     *
     * @throws BadMethodCallException
     */
    protected function checkActionAvailableToRun(): void
    {
        // Using this function will use any registered auto loaders if the class has not already been known
        // It uses psr-4..
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
        return $this->params['isCleanUrlApply'];
    }

    /**
     * Set request param.
     */
    protected function setRequestParam(string $key, $value): void
    {
        $this->requestParams[$key] = $value;
    }

    /**
     * Set controller class name to create object for processing request.
     */
    protected function setControllerClassName(string $name): void
    {
        $this->controllerClassName = $name;
    }

    /**
     * Set entity (model) name of an API.
     */
    protected function setApiName(string $name = null): void
    {
        $this->apiName = $name;
    }

    /**
     * Set entity (model) name of a module.
     */
    protected function setModuleName(string $name = null): void
    {
        $this->moduleName = $name;
    }

    /**
     * Return module.
     */
    public function getModule(): ?object
    {
        $modules = $this->getParam('modules');
        return $modules[$this->moduleName] ?? null;
    }

    /**
     * Return API.
     */
    public function getApi(): ?object
    {
        $api = $this->getParam('api');
        return $api[$this->apiName] ?? null;
    }

    /**
     * Set controller name to create object for processing request.
     */
    protected function setControllerName(string $name): void
    {
        $this->controllerName = $name;
    }

    /**
     * Return module name.
     */
    public function getRequestModuleName(): ?string
    {
        return $this->requestParams[$this->getModuleKey()] ?? null;
    }

    /**
     * Return API entity (model) name.
     */
    public function getRequestApiName(): ?string
    {
        return $this->requestParams[$this->getApiKey()] ?? null;
    }

    /**
     * Return request controller name.
     */
    public function getRequestControllerName(): string
    {
        return $this->requestGetParams[$this->getControllerKey()] ?? $this->getParam('defaultControllerName');
    }

    /**
     * Return request action name from request params.
     */
    public function getRequestActionName(): string
    {
        return $this->requestGetParams[$this->getActionKey()] ?? $this->getParam('defaultActionName');
    }

    /**
     * Return controller name.
     */
    protected function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * Set action name of a controller.
     */
    protected function setActionName(string $name): void
    {
        $this->actionName = $name;
    }

    /**
     * Return action name.
     */
    protected function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Return config param by key.
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
     * Return request params.
     */
    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    /**
     * Return request param by key.
     */
    public function getRequestParam(string $name): ?string
    {
        return $this->requestParams[$name] ?? null;
    }

    /**
     * Add throwable object in chain.
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
        $key = array_key_first($this->throwableChain);

        return $this->throwableChain[$key];
    }

    /**
     * Return last throwable object from chain.
     */
    public function getLastThrowable(): Throwable
    {
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

}