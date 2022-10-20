<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class ResponseHeader
 * @package myth21\viewcontroller
 */
class ResponseHeader
{
    private array $headers = [];
    private int $statusCode = 200;
    private ?string $statusMessage = null;

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    public function setStatusMessage(string $message): void
    {
        $this->statusMessage = $message;
    }

    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach ($this->headers as $header) {
            header("{$header}");
        }

        $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';
        $message = is_null($this->statusMessage) ? '' : ' ' . $this->statusMessage;
        header($serverProtocol . ' ' . $this->statusCode . $message);
    }

    public function redirect(string $to = '', int $code = 301): void
    {
        header('Location:' . $to, true, $code);
        exit();
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

}