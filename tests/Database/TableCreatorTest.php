<?php

declare(strict_types=1);

namespace App\Tests\Database;

use App\Database\Connection;
use App\Database\ConnectionInterface;
use App\Database\TableCreator;
use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TableCreator::class)]
final class TableCreatorTest extends TestCase
{
    private TableCreator $tableCreator;
    private ConnectionInterface $connection;

    protected function setUp(): void
    {
        $this->connection = Connection::fromEnvironment();
        $this->tableCreator = new TableCreator($this->connection);

        // Drop table if exists for clean state
        $this->connection->getPdo()->exec('DROP TABLE IF EXISTS users');
    }

    public function testCreateTableCreatesUsersTable(): void
    {
        $result = $this->tableCreator->createTable();

        $this->assertTrue($result);
        $this->assertTableExists('users');
        $this->assertColumnsExist();
    }

    public function testCreateTableCreatesUniqueEmailIndex(): void
    {
        $this->tableCreator->createTable();

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM pg_indexes 
            WHERE tablename = 'users' 
            AND indexname = 'users_email_unique'
        ");

        $this->assertEquals(1, $stmt->fetchColumn());
    }

    public function testCreateTableIsIdempotent(): void
    {
        // Create table twice
        $firstResult = $this->tableCreator->createTable();
        $secondResult = $this->tableCreator->createTable();

        $this->assertTrue($firstResult);
        $this->assertTrue($secondResult);
        $this->assertTableExists('users');
    }

    private function assertTableExists(string $tableName): void
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = '$tableName'
        ");

        $this->assertEquals(1, $stmt->fetchColumn());
    }

    public function testDropTableRemovesTable(): void
    {
        // First create the table
        $this->tableCreator->createTable();
        $this->assertTableExists('users');
        
        // Then drop it
        $result = $this->tableCreator->dropTable();
        
        $this->assertTrue($result);
        $this->assertTableDoesNotExist('users');
    }

    public function testDropTableIsIdempotent(): void
    {
        // Drop table when it doesn't exist
        $result = $this->tableCreator->dropTable();
        $this->assertTrue($result);
        
        // Create and then drop twice
        $this->tableCreator->createTable();
        $result1 = $this->tableCreator->dropTable();
        $result2 = $this->tableCreator->dropTable();
        
        $this->assertTrue($result1);
        $this->assertTrue($result2);
        $this->assertTableDoesNotExist('users');
    }

    private function assertTableDoesNotExist(string $tableName): void
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = '$tableName'
        ");

        $this->assertEquals(0, $stmt->fetchColumn());
    }

    private function assertColumnsExist(): void
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query("
            SELECT column_name, data_type, character_maximum_length
            FROM information_schema.columns
            WHERE table_name = 'users'
            ORDER BY ordinal_position
        ");

        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $expectedColumns = [
            ['column_name' => 'id', 'data_type' => 'integer'],
            ['column_name' => 'name', 'data_type' => 'character varying', 'character_maximum_length' => 255],
            ['column_name' => 'surname', 'data_type' => 'character varying', 'character_maximum_length' => 255],
            ['column_name' => 'email', 'data_type' => 'character varying', 'character_maximum_length' => 255],
        ];

        foreach ($expectedColumns as $index => $expected) {
            $actual = $columns[$index];
            $this->assertEquals($expected['column_name'], $actual['column_name']);
            $this->assertEquals($expected['data_type'], $actual['data_type']);
            if (isset($expected['character_maximum_length'])) {
                $this->assertEquals(
                    $expected['character_maximum_length'], 
                    $actual['character_maximum_length']
                );
            }
        }
    }
}
