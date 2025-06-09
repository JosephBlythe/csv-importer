<?php

declare(strict_types=1);

namespace App\Database;

/**
 * Creates database tables for the application.
 */
final class TableCreator
{
    public function __construct(
        private readonly ConnectionInterface $connection
    ) {}

    /**
     * Creates the users table if it doesn't exist.
     *
     * @return bool True if the table was created or already exists
     * @throws \RuntimeException If there was an error creating the table
     */
    public function createTable(): bool
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            surname VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            CONSTRAINT users_email_unique UNIQUE (email)
        )
        SQL;

        try {
            $pdo = $this->connection->getPdo();
            return $pdo->exec($sql) !== false;
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                sprintf('Failed to create users table: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Drops the users table if it exists.
     *
     * @return bool True if the table was dropped or didn't exist
     * @throws \RuntimeException If there was an error dropping the table
     */
    public function dropTable(): bool
    {
        $sql = 'DROP TABLE IF EXISTS users';

        try {
            $pdo = $this->connection->getPdo();
            return $pdo->exec($sql) !== false;
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                sprintf('Failed to drop users table: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
}
