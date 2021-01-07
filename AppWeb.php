<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use RuntimeException;

/**
 * Class AppWeb
 * @package myth21\viewcontroller
 */
class AppWeb extends App
{
    protected const PUT_REQUEST_METHOD = 'PUT';
    protected const POST_REQUEST_METHOD = 'POST';
    // TODO other methods

    protected string $requestMethod = 'GET';
    protected bool $isAjaxRequest = false;
    protected Session $session;
    protected ?ResponseHeader $responseHeader = null;

    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->session = new Session();
    }

    protected function setRequestParams(): void
    {
        $this->requestParams = $_REQUEST;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $this->isAjaxRequest = true;
        }
    }

    public function isAjaxRequest(): bool
    {
        return $this->isAjaxRequest;
    }

    public function getSession(): Session
    {
        if (is_null($this->session)) {
            throw new RuntimeException(Session::class . ' is not set');
        }

        return $this->session;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function isPutRequest(): bool
    {
        return $this->requestMethod === static::PUT_REQUEST_METHOD;
    }

    public function isPostRequest(): bool
    {
        return $this->requestMethod === static::POST_REQUEST_METHOD;
    }

    protected function getControllerNameSpace(): string
    {
        return $this->getParam('webControllerNameSpace');
    }

    /**
     * @return mixed
     */
    protected function runController()
    {
        $this->createController();
        $this->responseHeader = new ResponseHeader();

        return $this->controller->{$this->actionName}();
    }

    /**
     * @param mixed $out
     */
    protected function out($out): void
    {
        $this->responseHeader->sendHeaders();

        print_r($out);
    }

    public function getResponseHeader(): ResponseHeader
    {
        return $this->responseHeader;
    }

}