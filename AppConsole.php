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
     */
    protected function defineRequestParams(): void
    {
        $params = [];
        foreach ($_SERVER['argv'] as $argument) {
            $explodedValue = explode('=', $argument);

            if (count($explodedValue) === 2) {
                [$key, $value] = $explodedValue;
                $params[$key] = $value;
            }
        }

        $this->requestGetParams = $params; // rename as requestParams or console params
    }

    /**
     * @inheritdoc
     */
    protected function getControllerNameSpace(): string
    {
        return $this->getParam('consoleControllerNameSpace');
    }

    /**
     * @return int|void
     */
    protected function runController()
    {
        $this->createController();
        return $this->controller->{$this->actionName}();
    }

    /**
     * @param string|int $out
     */
    protected function out(string|int $out): void
    {
        exit($out);
    }
}