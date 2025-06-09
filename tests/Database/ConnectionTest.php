<?php

declare(strict_types=1);

namespace App\Tests\Database;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Database\Connection;
use App\Database\ConnectionInterface;

#[CoversClass(Connection::class)]
#[CoversClass(ConnectionInterface::class)]
final class ConnectionTest extends TestCase
{
    public function testConnectionCanBeCreatedFromEnvironmentVariables(): void
    {
        $connection = Connection::fromEnvironment();
        
        $this->assertInstanceOf(Connection::class, $connection);
    }

    public function testConnectionThrowsExceptionOnInvalidCredentials(): void
    {
        $this->expectException(PDOException::class);
        
        $connection = new Connection(
            host: 'db',
            port: '5432',
            dbname: 'csv_importer',
            user: 'invalid_user',
            password: 'invalid_password'
        );
        
        $connection->getPdo();
    }

    public function testConnectionReturnsSamePdoInstanceOnMultipleCalls(): void
    {
        $connection = Connection::fromEnvironment();
        
        $pdo1 = $connection->getPdo();
        $pdo2 = $connection->getPdo();
        
        $this->assertSame($pdo1, $pdo2);
    }

    public function testConnectionCanExecuteQuery(): void
    {
        $connection = Connection::fromEnvironment();
        $pdo = $connection->getPdo();
        
        $result = $pdo->query('SELECT 1 as value');
        
        $this->assertNotFalse($result);
        $this->assertEquals(['value' => '1'], $result->fetch(PDO::FETCH_ASSOC));
    }

    public function testConnectionHasProperPdoAttributes(): void
    {
        $connection = Connection::fromEnvironment();
        $pdo = $connection->getPdo();
        
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        $this->assertFalse($pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES));
    }
}
