<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Throwable;
use function parse_url;
use const PHP_URL_PATH;

/**
 * Responsible for web work.
 */
class WebApp extends AbstractApp
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
     * Request URI, e.g. /photo-galleries/favorite?page=2&sort=desc#top
     */
    protected ?string $requestUri = null;

    /**
     * Request URI path, part of URI, e.g. /photo-galleries/favorite
     */
    protected ?string $requestUriPath = null;

    /**
     * Whether AJAX request.
     */
    protected bool $isAjaxRequest = false;

    /***
     * Server session object.
     */
    protected ?AbstractSession $session = null;

    /**
     * Response header object.
     */
    protected ?ResponseHeader $responseHeader = null;

    /**
     * Class constructor.
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->session = AbstractSession::factory();
    }

    /**
     * Init params from HTTP request and server.
     *
     * @throws Throwable
     */
    public function defineRequestParams(): void
    {
        $this->requestGetParams = $_GET;
        $this->requestPostParams = $_POST;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->requestUriPath = $urlPath !== false ? $urlPath : '/';
        $this->isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        if ($this->isCleanUrlApply() && $this->routes = $this->createRoutes()) {

            foreach ($this->routes as $param => $value) {
                // Set param from route like GET param.
                $this->setRequestGetParam($param, $value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getControllerNameSpace(): string
    {
        return $this->getParam(AppParamInterface::WEB_CONTROLLER_NAMESPACE);
    }

    /**
     * Run web controller and return result of processing.
     */
    protected function runController(): mixed
    {
        $this->createController();
        $this->responseHeader = new ResponseHeader();

        return $this->controller->{$this->actionName}();
    }

    /**
     * Send output content to requester.
     */
    protected function out(mixed $out): void
    {
        $this->responseHeader->sendHeaders();

        print_r($out);
    }

    /**
     * Return response header object.
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

    public function getSession(): AbstractSession
    {
        if ($this->session === null) {
            $this->session = AbstractSession::factory();
        }

        return $this->session;
    }

    /**
     * Return request method name.
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * Return request URI.
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * Return request URI part.
     */
    public function getRequestUriPath(): string
    {
        return $this->requestUriPath;
    }

    /**
     * Check whether HEAD request.
     */
    public function isHeadRequest(): bool
    {
        return $this->requestMethod === static::HEAD_REQUEST_METHOD;
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
     * Check whether PATCH request.
     */
    public function isPatchRequest(): bool
    {
        return $this->requestMethod === static::PATCH_REQUEST_METHOD;
    }

    /**
     * Check whether CONNECT request.
     */
    public function isConnectRequest(): bool
    {
        return $this->requestMethod === static::CONNECT_REQUEST_METHOD;
    }

    /**
     * Check whether TRACE request.
     */
    public function isTraceRequest(): bool
    {
        return $this->requestMethod === static::TRACE_REQUEST_METHOD;
    }

    /**
     * Check whether DELETE request.
     */
    public function isDeleteRequest(): bool
    {
        return $this->requestMethod === static::DELETE_REQUEST_METHOD;
    }
}