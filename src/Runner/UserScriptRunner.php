<?php

declare(strict_types=1);

namespace App\Runner;

use App\Database\ConnectionInterface;
use App\Model\User;
use App\Processor\UserProcessor;
use App\Transformer\UserTransformer;

/**
 * Concrete implementation of ScriptRunner for importing user data.
 */
final class UserScriptRunner extends ScriptRunner
{
    /**
     * Create a new UserScriptRunner instance.
     */
    public function __construct(
        ConnectionInterface $connection
    ) {
        parent::__construct(
            connection: $connection,
            processor: new UserProcessor($connection, new UserTransformer()),
            transformer: new UserTransformer()
        );
    }

    /**
     * Creates the users table in the database.
     *
     * @param bool $dryRun If true, only validate but don't create the table
     * @return bool True if the operation was successful
     */
    public function createTable(bool $dryRun = false): bool
    {
        try {
            if ($dryRun) {
                return true;
            }

            $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                CONSTRAINT users_email_unique UNIQUE (email)
            )
            SQL;

            return $this->connection->getPdo()->exec($sql) !== false;
        } catch (\PDOException $e) {
            $this->errors['database'] = sprintf(
                'Failed to create users table: %s',
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Drops the users table from the database.
     *
     * @param bool $dryRun If true, only validate but don't drop the table
     * @return bool True if the operation was successful
     */
    public function dropTable(bool $dryRun = false): bool
    {
        try {
            if ($dryRun) {
                return true;
            }

            $sql = 'DROP TABLE IF EXISTS users';
            return $this->connection->getPdo()->exec($sql) !== false;
        } catch (\PDOException $e) {
            $this->errors['database'] = sprintf(
                'Failed to drop users table: %s',
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Runs the CSV import process.
     *
     * @param string $filePath Path to the CSV file
     * @param bool $dryRun If true, only validate but don't modify the database
     * @return bool True if the operation was successful
     */
    public function run(string $filePath, bool $dryRun = false): bool
    {
        try {
            // Check if users table exists
            $pdo = $this->connection->getPdo();
            $stmt = $pdo->query("
                SELECT COUNT(*) 
                FROM information_schema.tables
                WHERE table_schema = 'public' 
                AND table_name = 'users'
            ");

            if (!$dryRun && $stmt->fetchColumn() === 0) {
                $this->errors['database'] = 'Users table does not exist. Please run with --create_table first.';
                return false;
            }

            return parent::run($filePath, $dryRun);
        } catch (\PDOException $e) {
            $this->errors['database'] = sprintf(
                'Database error: %s',
                $e->getMessage()
            );
            return false;
        }
    }

    // No default headers needed - all files must have headers

    /**
     * {@inheritDoc}
     */
    protected function validateHeaders(array $headers): void
    {
        $fieldMappings = User::getFieldMappings();
        $foundRequiredFields = [];
        
        foreach (User::getRequiredFields() as $field) {
            $possibleHeaders = $fieldMappings[$field];
            $foundRequiredFields[$field] = count(array_intersect($possibleHeaders, $headers)) > 0;
        }

        $missingFields = array_keys(array_filter($foundRequiredFields, fn($found) => !$found));
        if (!empty($missingFields)) {
            throw new \RuntimeException(sprintf(
                'Missing required fields in CSV: %s',
                implode(', ', $missingFields)
            ));
        }
    }
}
