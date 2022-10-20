<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use function var_dump;

/**
 * Responsible for AppConsole work.
 */
class AppConsole extends App
{
    protected function getControllerNameSpace(): string
    {
        return $this->getParam('consoleControllerNameSpace');
    }

    protected function defineRequestParams(): void
    {
        $params = [];
        foreach ($_SERVER['argv'] as $argument) {
            $explodedValue = explode('=', $argument);

            // Signature of console parameters definition: key1=val1
            if (count($explodedValue) === 2) {
                [$key, $value] = $explodedValue;
                $params[$key] = $value;
            }
        }

//        $this->requestParams = $params; // todo check and delete
        $this->requestGetParams = $params; // rename as requestParams or console params
    }

    /**
     * @return mixed
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

    public function getSession(): ?Session
    {
        // sometime it make sense for tests
        return null;
    }
}

