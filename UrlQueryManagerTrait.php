<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Provide methods for working with url query string (use "dirty" url).
 */
trait UrlQueryManagerTrait
{
    /**
     * Return module key name.
     */
    public function getModuleKey(): string
    {
        return 'module';
    }

    /**
     * Return api key name.
     */
    public function getApiKey(): string
    {
        return 'api';
    }

    /**
     * Return controller key name.
     */
    public function getControllerKey(): string
    {
        return 'controller';
    }

    /**
     * Return action key name.
     */
    public function getActionKey(): string
    {
        return 'action';
    }

    /**
     * Return a combination of query string url by keys.
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     *
     * @return string
     */
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