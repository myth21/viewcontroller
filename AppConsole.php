<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class AppConsole
 * @package myth21\viewcontroller
 */
class AppConsole extends App
{
    protected function getControllerNameSpace(): string
    {
        return $this->getParam('consoleControllerNameSpace');
    }

    protected function setRequestParams(): void
    {
        $params = [];
        foreach ($_SERVER['argv'] as $value) {
            $explodedValue = explode('=', $value);

            if (sizeof($explodedValue) === 2) {
                $key = $explodedValue[0];
                $value = $explodedValue[1];
                $params[$key] = $value;
            }
        }

        $this->requestParams = $params;
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
     * @param mixed $out
     */
    protected function out($out): void
    {
        exit($out);
    }

    public function getSession(): ?Session
    {
        // sometime it make sense for tests
        return null;
    }
}

