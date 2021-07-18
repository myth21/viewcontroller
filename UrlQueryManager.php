<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Creates url query string (use "dirty" url)
 */
trait UrlQueryManager
{
    public function getModuleKey(): string
    {
        return 'module';
    }

    public function getApiKey(): string
    {
        return 'api';
    }

    public function getControllerKey(): string
    {
        return 'controller';
    }

    public function getActionKey(): string
    {
        return 'action';
    }

    public function createUrl(string $controller = '', string $action = '', array $params = []): string
    {
        if (!$controller && !$action && !$params) {
            return '';
        }

        $url = '?';
        if ($controller) {
            $url .= $this->getControllerKey() . '=' . $controller;
            if ($action) {
                $url .= '&'.$this->getActionKey(). '=' . $action;
                if ($params) {
                    foreach ($params as $key => $value) {
                        $url .= '&' . $key . '=' . $value;
                    }
                }
            }
        }

        return $url;
    }

}