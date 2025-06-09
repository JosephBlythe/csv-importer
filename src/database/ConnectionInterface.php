<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

interface ConnectionInterface
{
    /**
     * Creates a new database connection from environment variables.
     */
    public static function fromEnvironment(): self;

    /**
     * Gets a PDO instance for database interaction.
     *
     * @throws \PDOException if the connection cannot be established
     */
    public function getPdo(): PDO;
}
