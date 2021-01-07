<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Throwable;
use BadMethodCallException;

/**
 * Class App
 * App must define request params, route, choose controller and action to run, pass self as DI...
 * App must not know about db, view...
 *
 * @package myth21\viewcontroller
 * @property Throwable[] $throwableChain
 */
abstract class App implements IApp
{
    use UrlQueryManager;

    private array $params;
    private ?Router $router;
    private ?string $moduleName;
    private ?string $controllerName;
    private ?string $controllerClassName;
    private array $throwableChain;

    protected array $requestParams;
    protected object $controller;
    protected string $actionName;

    abstract protected function setRequestParams(): void;

    abstract protected function getControllerNameSpace(): string;

    /**
     * @param mixed $out
     * @return mixed
     */
    abstract protected function out($out);

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public static function factory(array $params): self
    {
        $className = (PHP_SAPI === 'cli') ? AppConsole::class : AppWeb::class;
        return new $className($params);
    }

    public function run()
    {
        $this->setRequestParams();

        try {
            if ($this->isCleanUrlApply() && $route = $this->createRoute()) {
                foreach ($route as $param => $value) {
                    $this->setRequestParam($param, $value);
                }
            }

            $this->setModuleName($this->getRequestModuleName());
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
            throw new BadMethodCallException('Route not found', 404);
        }

        return call_user_func_array($match['target'], $match['params']);
    }

    protected function defineControllerClassName(bool $doSearchInApp = false): void
    {
        $controllerName = $this->getControllerName() . ucfirst($this->getControllerKey());

        if (!$doSearchInApp && $this->moduleName) {
            $controllerClassName = $this->params['moduleNameSpace'] . $this->moduleName . $this->params['moduleControllerNameSpace'] . $controllerName;
        } else {
            $controllerClassName = $this->getControllerNameSpace() . $controllerName;
        }

        $this->setControllerClassName($controllerClassName);
    }

    /**
     * @throws BadMethodCallException
     */
    protected function checkActionAvailableToRun(): void
    {
        // Using this function will use any registered auto loaders if the class has not already been known
        if (!method_exists($this->controllerClassName, $this->actionName)) {
            throw new BadMethodCallException('Action is not available to run', 404);
        }
    }

    protected function createController(): object
    {
        $controller = new $this->controllerClassName($this);
        $this->controller = $controller;

        return $this->controller;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function isCleanUrlApply(): bool
    {
        return $this->params['isCleanUrlApply'];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setRequestParam(string $key, $value): void
    {
        $this->requestParams[$key] = $value;
    }

    protected function setControllerClassName(string $name): void
    {
        $this->controllerClassName = $name;
    }

    protected function setModuleName(string $name = null): void
    {
        $this->moduleName = $name;
    }

    public function getModule(): object
    {
        $modules = $this->getParam('modules');
        return $modules[$this->moduleName];
    }

    protected function setControllerName(string $name): void
    {
        $this->controllerName = $name;
    }

    public function getRequestModuleName(): ?string
    {
        return $this->requestParams[$this->getModuleKey()] ?? null;
    }

    public function getRequestControllerName(): string
    {
        return $this->requestParams[$this->getControllerKey()] ?? $this->getParam('defaultControllerName');
    }

    public function getRequestActionName(): string
    {
        return $this->requestParams[$this->getActionKey()] ?? $this->getParam('defaultActionName');
    }

    protected function getControllerName(): string
    {
        return $this->controllerName;
    }

    protected function setActionName(string $name): void
    {
        $this->actionName = $name;
    }

    protected function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * @param string $name
     * @return mixed|bool|null
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    public function getRequestParam(string $name): ?string
    {
        return $this->requestParams[$name] ?? null;
    }

    protected function addThrowable(Throwable $e): void
    {
        $this->throwableChain[] = $e;
    }

    public function shiftThrowableChain(): ?Throwable
    {
        return array_shift($this->throwableChain);
    }

    public function popThrowableChain(): ?Throwable
    {
        return array_pop($this->throwableChain);
    }

    public function getThrowableChain(): array
    {
        return $this->throwableChain;
    }

}