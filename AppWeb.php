<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class AppWeb
 * @package myth21\viewcontroller
 */
class AppWeb extends App
{
    protected const HEAD_REQUEST_METHOD = 'HEAD';
    protected const GET_REQUEST_METHOD = 'GET';
    protected const POST_REQUEST_METHOD = 'POST';
    protected const PUT_REQUEST_METHOD = 'PUT';
    protected const DELETE_REQUEST_METHOD = 'DELETE';
    // other methods...

    protected ?string $requestMethod = null;
    protected bool $isAjaxRequest = false;
    protected Session $session;
    protected ?ResponseHeader $responseHeader = null;

    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->session = new Session();
    }

    protected function defineRequestParams(): void
    {
        $this->requestParams = $_REQUEST;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function isAjaxRequest(): bool
    {
        return $this->isAjaxRequest;
    }

    public function getSession(): Session
    {
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

    public function isGetRequest(): bool
    {
        return $this->requestMethod === static::GET_REQUEST_METHOD;
    }

    public function isPostRequest(): bool
    {
        return $this->requestMethod === static::POST_REQUEST_METHOD;
    }

    public function isDeleteRequest(): bool
    {
        return $this->requestMethod === static::DELETE_REQUEST_METHOD;
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