<?php

declare(strict_types=1);

namespace App\Tests\Database;

use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Database\DatabaseConnection;

#[CoversClass(DatabaseConnection::class)]
class DatabaseConnectionTest extends TestCase
{
    private DatabaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->connection = new DatabaseConnection(
            host: $_ENV['DB_HOST'],
            port: $_ENV['DB_PORT'],
            dbname: $_ENV['DB_NAME'],
            user: $_ENV['DB_USER'],
            password: $_ENV['DB_PASSWORD']
        );
    }

    public function testGetConnectionReturnsPdoInstance(): void
    {
        $this->assertInstanceOf(
            PDO::class,
            $this->connection->getConnection(),
            'getConnection should return a PDO instance'
        );
    }

    public function testConnectionUsesCorrectDsn(): void
    {
        $pdo = $this->connection->getConnection();
        
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_NAME']
        );
        
        $this->assertEquals(
            $dsn,
            $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'Connection should use correct DSN'
        );
    }

    public function testConnectionCanExecuteQuery(): void
    {
        $pdo = $this->connection->getConnection();
        
        $stmt = $pdo->query('SELECT 1 as value');
        $this->assertNotFalse($stmt, 'Query should execute successfully');
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(
            ['value' => '1'],
            $result,
            'Database should return correct result from simple query'
        );
    }

    public function testConnectionIsSingleton(): void
    {
        $connection1 = $this->connection->getConnection();
        $connection2 = $this->connection->getConnection();
        
        $this->assertSame(
            $connection1,
            $connection2,
            'Multiple calls to getConnection should return the same PDO instance'
        );
    }
}
