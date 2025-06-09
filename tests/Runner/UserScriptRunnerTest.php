<?php

declare(strict_types=1);

namespace App\Tests\Runner;

use App\Database\ConnectionInterface;
use App\Runner\UserScriptRunner;
use App\Database\Connection;
use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserScriptRunner::class)]
final class UserScriptRunnerTest extends TestCase
{
    private UserScriptRunner $runner;
    private ConnectionInterface $connection;
    private string $testDataDir;

    protected function setUp(): void
    {
        $this->connection = Connection::fromEnvironment();
        $this->runner = new UserScriptRunner($this->connection);
        $this->testDataDir = dirname(__DIR__) . '/data';

        // Clean up any existing test data
        $this->cleanupDatabase();
    }

    private function cleanupDatabase(): void
    {
        $this->connection->getPdo()->exec('DROP TABLE IF EXISTS users');
    }

    private function createTable(): void
    {
        $sql = <<<SQL
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            surname VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )
        SQL;
        $this->connection->getPdo()->exec($sql);
    }

    private function assertTableExists(): void
    {
        $count = $this->getTableCount();
        $this->assertEquals(1, $count, 'Users table should exist');
    }

    private function assertTableDoesNotExist(): void
    {
        $count = $this->getTableCount();
        $this->assertEquals(0, $count, 'Users table should not exist');
    }

    private function getTableCount(): int
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'users'
        ");
        return (int) $stmt->fetchColumn();
    }

    private function getUserCount(): int
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query('SELECT COUNT(*) FROM users');
        return (int) $stmt->fetchColumn();
    }

    public function testCreateTableCreatesUsersTable(): void
    {
        $result = $this->runner->createTable();
        
        $this->assertTrue($result);
        $this->assertTableExists();
    }

    public function testCreateTableInDryRunMode(): void
    {
        $result = $this->runner->createTable(dryRun: true);
        
        $this->assertTrue($result);
        $this->assertTableDoesNotExist();
    }

    public function testDropTableRemovesExistingTable(): void
    {
        // First create the table
        $this->runner->createTable();
        $this->assertTableExists();
        
        // Then drop it
        $result = $this->runner->dropTable();
        
        $this->assertTrue($result);
        $this->assertTableDoesNotExist();
    }

    public function testDropTableInDryRunMode(): void
    {
        // First create the table
        $this->runner->createTable();
        $this->assertTableExists();
        
        // Try to drop in dry run mode
        $result = $this->runner->dropTable(dryRun: true);
        
        $this->assertTrue($result);
        $this->assertTableExists(); // Table should still exist in dry run mode
    }

    public function testRunProcessesValidUsers(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/valid_users.csv');
        
        $this->assertTrue($result);
        $this->assertEquals(3, $this->getUserCount());
    }

    public function testRunWithDryRunDoesNotInsertData(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/valid_users.csv', true);
        
        $this->assertTrue($result);
        $this->assertEquals(0, $this->getUserCount());
    }

    public function testRunWithoutTableFails(): void
    {
        $result = $this->runner->run($this->testDataDir . '/valid_users.csv');
        
        $this->assertFalse($result);
        $this->assertArrayHasKey('database', $this->runner->getErrors());
    }

    public function testRunImportsValidUsers(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/valid_users.csv');

        $this->assertTrue($result);
        $this->assertEmpty($this->runner->getErrors());
        $this->assertEquals(3, $this->getUserCount());
    }

    public function testRunHandlesAlternativeHeaders(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/alternative_headers.csv');

        $this->assertTrue($result);
        $this->assertEmpty($this->runner->getErrors());
        $this->assertEquals(2, $this->getUserCount());
    }

    public function testRunHandlesInvalidEmail(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/invalid_email.csv');

        // Since we have a valid row in the file, the import should succeed
        $this->assertTrue($result);
        $this->assertArrayHasKey('validation', $this->runner->getErrors());
        $this->assertStringContainsString('Invalid email format', $this->runner->getErrors()['validation']);
        // Should have imported only the valid rows
        $this->assertEquals(1, $this->getUserCount());
    }

    public function testRunHandlesMissingFields(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/missing_field.csv');

        $this->assertFalse($result);
        $this->assertArrayHasKey('format', $this->runner->getErrors());
        $this->assertStringContainsString('Missing required fields', $this->runner->getErrors()['format']);
    }

    public function testRunHandlesEmptyFile(): void
    {
        $this->runner->createTable();
        $result = $this->runner->run($this->testDataDir . '/empty.csv');

        $this->assertFalse($result);
        $this->assertArrayHasKey('format', $this->runner->getErrors());
        $this->assertStringContainsString('Empty file', $this->runner->getErrors()['format']);
    }

    // Tests related to headerless functionality removed

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
    }
}
