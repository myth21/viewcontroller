<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Provide methods for working with url query string (use "dirty" url).
 * It can be used for redirect, build url, pull common url keys are to define route.
 */
trait UrlQueryManagerTrait
{
    /**
     * Return api key name.
     */
    public function getApiKey(): string
    {
        return 'api';
    }

    /**
     * Return api version key name.
     */
    public function getApiVersionKey(): string
    {
        return 'version';
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