<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for console work.
 */
class ConsoleApp extends AbstractApp
{
    /**
     * Define console params.
     * Signature of console parameters definition: key1=val1
     * Note that console params are named as requestGetParams for common interface.
     */
    public function defineRequestParams() : void
    {
        $params = [];
        $argv = $_SERVER['argv'] ?? [];
        foreach ($argv as $argument) {
            $explodedValue = explode('=', $argument);

            if (count($explodedValue) === 2) {
                [$key, $value] = $explodedValue;
                $params[$key] = $value;
            }
        }

        $this->requestGetParams = $params;
    }

    /**
     * @inheritdoc
     */
    protected function getControllerNameSpace(): string
    {
        return $this->getParam(AppParamInterface::CONSOLE_CONTROLLER_NAMESPACE);
    }

    /**
     * Run console controller and return result of processing.
     */
    protected function runController(): string|int|null
    {
        $this->createController();
        return $this->controller->{$this->actionName}();
    }

    /**
     * Send output content to requester.
     */
    protected function out(mixed $out): void
    {
        print_r($out);
        exit();
    }
}