<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for console work.
 */
class AppConsole extends App
{
    /**
     * Define console params.
     * Signature of console parameters definition: key1=val1
     * Note that console params are named as requestGetParams for common interface.
     */
    protected function defineRequestParams() : void
    {
        $params = [];
        foreach ($_SERVER['argv'] as $argument) {
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
        return $this->getParam('consoleControllerNameSpace');
    }

    /**
     * Run console controller and return result of processing.
     */
    protected function runController()
    {
        $this->createController();
        return $this->controller->{$this->actionName}();
    }

    /**
     * Send output content to requester.
     *
     * @param string|int $out
     */
    protected function out(string|int $out): void
    {
        exit($out);
    }
}