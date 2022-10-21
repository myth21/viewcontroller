<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use function parse_url;

use const PHP_URL_PATH;

/**
 * Responsible for web work.
 */
class AppWeb extends App
{
    protected const HEAD_REQUEST_METHOD = 'HEAD';
    protected const GET_REQUEST_METHOD = 'GET';
    protected const POST_REQUEST_METHOD = 'POST';
    protected const PUT_REQUEST_METHOD = 'PUT';
    protected const DELETE_REQUEST_METHOD = 'DELETE';
    protected const PATCH_REQUEST_METHOD = 'PATCH';
    protected const CONNECT_REQUEST_METHOD = 'CONNECT';
    protected const TRACE_REQUEST_METHOD = 'TRACE';

    /**
     * HTTP request method.
     */
    protected ?string $requestMethod = null;

    /**
     * Request URI.
     */
    protected ?string $requestUri = null;

    /**
     * Request URI path, part of URI.
     */
    protected ?string $requestUriPath = null;

    /**
     * Whether AJAX request.
     */
    protected bool $isAjaxRequest = false;

    /***
     * Server session object.
     */
    protected Session $session;

    /**
     * Response header object.
     */
    protected ?ResponseHeader $responseHeader = null;

    /**
     * Class constructor.
     *
     * @param array $params
     */
    public function __construct(protected array $params)
    {
        parent::__construct($params);

        $this->session = new Session();
    }

    /**
     * Init params from HTTP request and server.
     */
    protected function defineRequestParams(): void
    {
        $this->requestGetParams = $_GET;
        $this->requestPostParams = $_POST;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->requestUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * @inheritdoc
     */
    protected function getControllerNameSpace(): string
    {
        return $this->getParam('webControllerNameSpace');
    }

    /**
     * Run web controller and return result of processing.
     */
    protected function runController()
    {
        $this->createController();
        $this->responseHeader = new ResponseHeader();

        return $this->controller->{$this->actionName}();
    }

    /**
     * Send output content to requester.
     *
     * @param string|int $out
     */
    protected function out(string|int $out): void
    {
        $this->responseHeader->sendHeaders();

        print_r($out);
    }

    /**
     * @return ResponseHeader
     */
    public function getResponseHeader(): ResponseHeader
    {
        return $this->responseHeader;
    }

    /**
     * Check whether AJAX request.
     */
    public function isAjaxRequest(): bool
    {
        return $this->isAjaxRequest;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * Return request method name.
     *
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Return request URI.
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * Return request URI part.
     *
     * @return string
     */
    public function getRequestUriPath(): string
    {
        return $this->requestUriPath;
    }

    /**
     * Check whether GET request.
     */
    public function isGetRequest(): bool
    {
        return $this->requestMethod === static::GET_REQUEST_METHOD;
    }

    /**
     * Check whether POST request.
     */
    public function isPostRequest(): bool
    {
        return $this->requestMethod === static::POST_REQUEST_METHOD;
    }

    /**
     * Check whether PUT request.
     */
    public function isPutRequest(): bool
    {
        return $this->requestMethod === static::PUT_REQUEST_METHOD;
    }

    /**
     * Check whether DELETE request.
     */
    public function isDeleteRequest(): bool
    {
        return $this->requestMethod === static::DELETE_REQUEST_METHOD;
    }
}