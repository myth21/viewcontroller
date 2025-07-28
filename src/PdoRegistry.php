<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use PDO;
use RuntimeException;

/**
 * PdoRegistry is a simple singleton-style registry for storing and retrieving
 * a globally shared PDO instance.
 *
 * This class was created to replace the anti-pattern of using static properties
 * like `static::$pdo` in Active Record base classes (e.g., PdoRecord),
 * which can make testing, dependency injection, and flexibility harder to achieve.
 *
 * By centralizing the PDO instance in this registry, all models can access the
 * same connection without hardcoding it into their base class, and without
 * needing a full dependency injection container.
 *
 * Usage:
 * - Call `PdoRegistry::set($pdo)` once during app bootstrapping.
 * - Then retrieve it anywhere using `PdoRegistry::get()`.
 */
final class PdoRegistry
{
    /**
     * The global PDO instance.
     */
    private static ?PDO $pdo = null;

    /**
     * Stores the PDO instance for later use.
     *
     * @param PDO $pdo The PDO object to register.
     */
    public static function set(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Retrieves the stored PDO instance.
     *
     * @return PDO The registered PDO instance.
     *
     * @throws RuntimeException If the PDO instance has not been initialized.
     */
    public static function get(): PDO
    {
        if (!self::$pdo) {
            throw new RuntimeException('PDO not initialized');
        }

        return self::$pdo;
    }
}