<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Throwable;
use BadMethodCallException;

use function array_key_first;
use function method_exists;
use function ucfirst;

use const PHP_SAPI;

/**
 * App must define request params, route, choose controller and action to run, pass self in app as dependency injection.
 * App must not know about db, view...
 *
 * @property Throwable[] $throwableChain
 * @property array $params Config params.
 */
abstract class App implements AppInterface
{
    use UrlQueryManager;

    /**
     * RouterInterface responsible for routing.
     */
    private ?RouterInterface $router;

    /**
     * Module entity (model) name is specified in config params.
     */
    private ?string $moduleName = null;

    /**
     * API entity (model) name is specified in config params.
     */
    private ?string $apiName = null;

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
    protected array $route = [];

    /**
     * Namespace to handle request.
     */
    protected string $controllerNameSpace;

    /**
     * Controller to handle request.
     */
    protected object $controller;

    /**
     * Action of a controller to handle request.
     */
    protected string $actionName;

    /**
     * Defining request params for console or web application.
     */
    abstract protected function defineRequestParams(): void;

    /**
     * Return controller name space accroding to app type (console|web) and PSR-4.
     */
    abstract protected function getControllerNameSpace(): string;

    /**
     * Output handled request result.
     * @param string|int $out
     */
    abstract protected function out(string|int $out): void;

    /**
     * Run a controller and return result of processing.
     */
    abstract protected function runController();

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct(protected array $params)
    {
        // TODO getting vendor params only.
    }

    /**
     * Create concrete app object.
     *
     * @param array $params
     * @return App
     */
    public static function factory(array $params): static
    {
        $className = (PHP_SAPI === 'cli') ? AppConsole::class : AppWeb::class;
        return new $className($params);
    }

    /**
     * Run request processing of the concrete app object.
     */
    public function run(): void
    {
        $this->defineRequestParams();

        try {
            if ($this->isCleanUrlApply() && $this->route = $this->createRoute()) {

                foreach ($this->route as $param => $value) {
                    // $this->setRequestParam($param, $value);
                    // todo implement in child class web or console
//                    if (PHP_SAPI === 'cli') {
////                        $this->setRequestParam($param, $value);
//                        $this->setRequestGetParam($param, $value);
//                    } else {
//                        $this->setRequestGetParam($param, $value);
//                    }


                    // Set param from route like GET param
                    $this->setRequestGetParam($param, $value);
                }


            }

            $this->defineControllerNameSpace();

            //$this->setModuleName($this->getRequestModuleName());
            //$this->setApiName($this->getRequestApiName());
            $this->setControllerName($this->getRequestControllerName());
            $this->setActionName($this->getRequestActionName());
            $this->defineControllerClassName();

            $this->checkActionAvailableToRun();

            $out = $this->runController();

        } catch (Throwable $e) {

            $message = '['.date('Y-m-d H:i:s').']' . PHP_EOL;
            $message .= $e->getMessage() . PHP_EOL;
            $message .= $e->getFile() . ':' . $e->getLine() . PHP_EOL;
            $message .= PHP_EOL;
            $errorLogPath = $this->getParam('throwableLogFileName');
            // is_writable($errorLogPath)
            file_put_contents($errorLogPath, $message);

            // clear previous buffer outputs
            while (ob_get_level()) {
                ob_end_clean();
            }
            $this->addThrowable($e);
            // Set handler controller on throwable error

            //  $this->defineControllerNameSpace();
            if ($this->isRequestToApi()) {
                $this->controllerNameSpace = $this->getParam('apiExceptionControllerNameSpace');
            } else {
                $this->defineControllerNameSpace();
            }

            $this->setControllerName($this->getParam('exceptionControllerName'));
            $this->setActionName($this->getParam('exceptionMethodName'));
            $this->defineControllerClassName();

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

        foreach ($this->params['routes'] as $route) {
            $this->router->map($route['method'], $route['urlPattern'], $route['func'], $route['name']);
        }

        /*
        foreach ($this->params['routes'] as $urlPattern => $handler) {
            $this->router->map($handler['method'], $urlPattern, $handler['func'], $handler['name']);
        }
        */

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
    public function getRoute(): array
    {
        return $this->route;
    }

    public function isRequestToApi(): bool
    {
        return array_key_first($this->route) === $this->getApiKey();
    }

    /**
     * Define controller name space.
     */
    protected function defineControllerNameSpace(): void
    {
        if ($this->isRequestToApi()) {

            $this->controllerNameSpace = $this->params['apiNameSpace'] . $this->route[$this->getApiKey()] . $this->params['apiControllerNameSpace'];

        } elseif (array_key_first($this->route) === $this->getModuleKey()) {

            $this->controllerNameSpace = $this->params['moduleNameSpace'] . $this->route[$this->getModuleKey()]  . $this->params['moduleControllerNameSpace'];

        } else {

            $this->controllerNameSpace = $this->getControllerNameSpace();
        }
    }

    /**
     * Define full class name of a controller for running.
     */
    protected function defineControllerClassName(): void
    {
        $controllerName = $this->controllerName . ucfirst($this->getControllerKey());

        // define name space
        /*
        if (!$doSearchInApp && $this->apiName) {
            $controllerClassName = $this->params['apiNameSpace'] . $this->apiName . $this->params['apiControllerNameSpace'] . $controllerName;
        } elseif (!$doSearchInApp && $this->moduleName) {
            $controllerClassName = $this->params['moduleNameSpace'] . $this->moduleName . $this->params['moduleControllerNameSpace'] . $controllerName;
        } else {
            $controllerClassName = $this->getControllerNameSpace() . $controllerName;
        }*/

        $controllerClassName = $this->controllerNameSpace . $controllerName;

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
     * @deprecated
     */
//    protected function setRequestParam(string $key, $value): void
//    {
//        $this->requestParams[$key] = $value;
//    }

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
    protected function setControllerClassName(string $name): void
    {
        $this->controllerClassName = $name;
    }

    /**
     * Set controller name (e.g. Article) to create object for processing request.
     */
    protected function setControllerName(string $name): void
    {
        $this->controllerName = $name;
    }

    /**
     * Return request controller name (e.g. Article).
     */
    public function getRequestControllerName(): string
    {
        return $this->requestGetParams[$this->getControllerKey()] ?? $this->getParam('defaultControllerName');
    }

    /**
     * Set action name (e.g. view|list) of a controller.
     */
    protected function setActionName(string $name): void
    {
        $this->actionName = $name;
    }

    /**
     * Return request action name (e.g. view|list) from request params.
     */
    public function getRequestActionName(): string
    {
        return $this->requestGetParams[$this->getActionKey()] ?? $this->getParam('defaultActionName');
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
     * @deprecated
     */
//    public function getRequestParams(): array
//    {
//        return $this->requestParams;
//    }

    /**
     * Return request param by key.
     * @deprecated
     */
//    public function getRequestParam(string $name): ?string
//    {
//        return $this->requestParams[$name] ?? null;
//    }

    /**
     * Return request param by key.
     */
    public function getRequestGetParam(string $name): ?string
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


    // TODO need to manage Throwables?

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


    // TODO need to manage api and module getting names?

    /**
     * Set entity (model) name of an API.
     */
//    protected function setApiName(string $name = null): void
//    {
//        $this->apiName = $name;
//    }

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
     * Return module name.
     */
    public function getRequestModuleName(): ?string
    {
        return $this->requestGetParams[$this->getModuleKey()] ?? null;
    }

    /**
     * Return API entity (model) name.
     */
    public function getRequestApiName(): ?string
    {
        return $this->requestGetParams[$this->getApiKey()] ?? null;
    }

}