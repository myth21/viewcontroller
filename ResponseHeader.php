<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for response headers.
 */
class ResponseHeader
{
    /**
     * Response headers.
     */
    private array $headers = [];

    /**
     * Response status code.
     */
    private int $statusCode = 200;

    /**
     * Response status message.
     */
    private ?string $statusMessage = null;

    /**
     * Set status code.
     *
     * @param int $code
     */
    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    /**
     * Set status message.
     *
     * @param string $message
     */
    public function setStatusMessage(string $message): void
    {
        $this->statusMessage = $message;
    }

    /**
     * Add response header as string, like 'Content-Type: application/json; charset=UTF-8'.
     *
     * @param string $header
     */
    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    /**
     * Send response headers.
     */
    public function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach ($this->headers as $header) {
            header((string)$header);
        }

        $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';
        $message = is_null($this->statusMessage) ? '' : ' ' . $this->statusMessage;
        header($serverProtocol . ' ' . $this->statusCode . $message);
    }

    /**
     * HTTP response redirect header.
     *
     * @param string $to
     * @param int $code
     */
    public function redirect(string $to = '', int $code = 301): void
    {
        header('Location:' . $to, true, $code);
        exit();
    }

    /**
     * Return header list.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

}