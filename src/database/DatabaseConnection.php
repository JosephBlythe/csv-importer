<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class DatabaseConnection
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $dbname,
        private readonly string $user,
        private readonly string $password
    ) {
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $this->host,
                    $this->port,
                    $this->dbname
                );
                
                $this->connection = new PDO(
                    $dsn,
                    $this->user,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException(
                    sprintf('Connection failed: %s', $e->getMessage()),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return $this->connection;
    }
}
