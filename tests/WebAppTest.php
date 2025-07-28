<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use myth21\viewcontroller\AbstractApp;
use myth21\viewcontroller\AppParamInterface;
use myth21\viewcontroller\WebApp;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use function get_class;

class WebAppTest extends TestCase
{
    public function testIsCleanUrlApply(): void
    {
        $app = new WebApp([]);
        $this->assertFalse($app->isCleanUrlApply());

        $app = new WebApp([AppParamInterface::IS_CLEAN_URL => false]);
        $this->assertFalse($app->isCleanUrlApply());

        $app = new WebApp([AppParamInterface::IS_CLEAN_URL => true]);
        $this->assertTrue($app->isCleanUrlApply());
    }

    public function testRequestGetParam(): void
    {
        $app = new WebApp([]);

        $reflectionClass = new ReflectionClass($app);

        $key = 'key';
        $value = 'value';

        $method = $reflectionClass->getMethod('setRequestGetParam');
        $method->setAccessible(true);
        $method->invoke($app, $key, $value);

        $this->assertEquals($value, $app->getRequestGetParam($key));
    }

    public function testApi(): void
    {
        $apiName = 'articles';

        $apis = [
            $apiName => new class
            {
                public function getSomething(): string
                {
                    return __METHOD__;
                }
            },
        ];

        $app = new WebApp([
            AppParamInterface::API => $apis
        ]);

        $reflectionClass = new ReflectionClass($app);

        $method = $reflectionClass->getMethod('setApiName');
        $method->setAccessible(true);
        $method->invoke($app, $apiName);

        $api = $app->getApi();

        $reflectionClass = new ReflectionClass($api);

        $this->assertTrue($reflectionClass->isAnonymous());
    }

    public function testThrowableChain(): void
    {
        $app = new WebApp([]);

        $reflectionClass = new ReflectionClass($app);
        $method = $reflectionClass->getMethod('addThrowable');
        $method->setAccessible(true);

        $errorException = 'ErrorException';
        $runtimeException = 'RuntimeException';
        $logicException = 'LogicException';

        $method->invoke($app, new $errorException());
        $method->invoke($app, new $runtimeException());
        $method->invoke($app, new $logicException());

        $this->assertCount(3, $app->getThrowableChain());

        $this->assertEquals($errorException, get_class($app->getFirstThrowable()));
        $this->assertEquals($logicException, get_class($app->getLastThrowable()));
    }

    public function testAvailableToRunWebApp(): void
    {
        $controllerNameSpace = '\\app\\controller\\';
        $defaultControllerName = 'Index';
        $defaultActionName = 'index';

        $expectedControllerClassName = '\\app\\controller\\IndexController';

        $app = new WebApp([
            AppParamInterface::WEB_CONTROLLER_NAMESPACE => $controllerNameSpace,
            AppParamInterface::DEFAULT_CONTROLLER_NAME => $defaultControllerName,
            AppParamInterface::DEFAULT_ACTION_NAME => $defaultActionName,
        ]);

        $reflectionClass = new ReflectionClass($app);

        $defineControllerNameSpace = $reflectionClass->getMethod('defineControllerNameSpace');
        $defineControllerNameSpace->setAccessible(true);
        $defineControllerNameSpace->invoke($app);

        $setControllerNameMethod = $reflectionClass->getMethod('setControllerName');
        $setControllerNameMethod->setAccessible(true);
        $setControllerNameMethod->invoke($app, $app->getRequestControllerName());

        $setActionName = $reflectionClass->getMethod('setActionName');
        $setActionName->setAccessible(true);
        $setActionName->invoke($app, $app->getRequestActionName());

        $defineControllerNameSpace = $reflectionClass->getMethod('defineControllerClassName');
        $defineControllerNameSpace->setAccessible(true);
        $defineControllerNameSpace->invoke($app);

        $reflectionProperty = new ReflectionProperty(AbstractApp::class, 'controllerClassName');
        $reflectionProperty->setAccessible(true);
        $controllerClassName = $reflectionProperty->getValue($app);

        $reflectionProperty = new ReflectionProperty(AbstractApp::class, 'actionName');
        $reflectionProperty->setAccessible(true);
        $actionName = $reflectionProperty->getValue($app);

        $this->assertEquals($expectedControllerClassName, $controllerClassName);
        $this->assertEquals($defaultActionName, $actionName);
    }
}