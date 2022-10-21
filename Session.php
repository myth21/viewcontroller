<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use RuntimeException;

/**
 * Responsible for session work.
 */
class Session
{
    /**
     * Session data.
     */
    private array $data = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (headers_sent()) {
            throw new RuntimeException('Headers have already been sent');
        }

        session_start();

        $this->data =& $_SESSION;
    }

    /**
     * Set session key and value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check existing key in session data.
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Return session value by key.
     * Whether to delete the data on getting from session.
     *
     * @param string $key
     * @param bool $delete
     *
     * @return mixed
     */
    public function get(string $key, bool $delete = false): mixed
    {
        $value = $this->data[$key];
        if ($delete) {
            $this->delete($key);
        }

        return $value;
    }

    /**
     * Delete data from session by kye.
     *
     * @param string $key
     */
    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Destroy session.
     *
     * @return bool
     */
    public function destroy(): bool
    {
        return session_destroy();
    }

    /**
     * Get session data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}