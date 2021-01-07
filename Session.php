<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class Session
 * @package myth21\viewcontroller
 */
class Session
{
    private array $data = [];

    public function __construct()
    {
        session_start();
        $this->data =& $_SESSION;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @param bool $delete
     * @return mixed
     */
    public function get(string $key, bool $delete = false)
    {
        $value = $this->data[$key];
        if ($delete) {
            $this->delete($key);
        }

        return $value;
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    public function destroy(): bool
    {
        return session_destroy();
    }

    public function getData(): array
    {
        return $this->data;
    }
}