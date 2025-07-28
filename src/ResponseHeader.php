<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for managing HTTP response headers.
 */
class ResponseHeader
{
    /**
     * @var array<string> Headers as array string.
     */
    private array $headers = [];

    /**
     * HTTP status code (default 200 OK).
     */
    private int $statusCode = 200;

    /**
     * HTTP status message (optional).
     */
    private ?string $statusMessage = null;

    /**
     * Set HTTP status code.
     *
     * @param int $code HTTP status code (e.g., 200, 404)
     * @return $this Fluent interface
     */
    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set HTTP status message.
     *
     * @param string $message Custom status message
     */
    public function setStatusMessage(string $message): void
    {
        $this->statusMessage = $message;
    }

    /**
     * Add a response header string.
     */
    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    /**
     * Send all headers to the client.
     */
    public function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach ($this->headers as $header) {
            header($header);
        }

        $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';
        $message = is_null($this->statusMessage) ? '' : ' ' . $this->statusMessage;
        header($serverProtocol . ' ' . $this->statusCode . $message);
    }

    /**
     * Redirect to given URL with specified HTTP status code.
     * Immediately terminates script execution.
     *
     * @param string $to URL to redirect to
     * @param int $code HTTP status code (usually 301 or 302)
     */
    public function redirect(string $to = '', int $code = 301): void
    {
        header('Location:' . $to, true, $code);
        exit();
    }

    /**
     * Get current headers as array.
     *
     * @return array<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

}