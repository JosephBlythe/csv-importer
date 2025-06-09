<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

/**
 * PostgreSQL database connection implementation.
 *
 * This class provides a connection to a PostgreSQL database using PDO.
 * It is designed to be immutable and thread-safe, with lazy connection
 * initialization.
 */
final class Connection implements ConnectionInterface
{
    private ?PDO $pdo = null;
    
    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $dbname,
        private readonly string $user,
        private readonly string $password
    ) {
    }
    
    public static function fromEnvironment(): self
    {
        return new self(
            host: $_ENV['DB_HOST'],
            port: $_ENV['DB_PORT'],
            dbname: $_ENV['DB_NAME'],
            user: $_ENV['DB_USER'],
            password: $_ENV['DB_PASSWORD']
        );
    }
    
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $this->host,
                $this->port,
                $this->dbname
            );
            
            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        
        return $this->pdo;
    }
}
