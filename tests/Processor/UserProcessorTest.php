<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Database\ConnectionInterface;
use App\Processor\Processor;
use App\Processor\UserProcessor;
use App\Transformer\UserTransformer;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(UserProcessor::class)]
final class UserProcessorTest extends ProcessorContractTest
{
    private ConnectionInterface $connection;
    private UserTransformer $transformer;
    /** @var PDO&MockObject */
    private PDO $pdo;
    /** @var PDOStatement&MockObject */
    private PDOStatement $statement;

    protected function createProcessor(): Processor
    {
        return new UserProcessor(
            $this->connection,
            $this->transformer
        );
    }

    protected function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->transformer = new UserTransformer();
        $this->pdo = $this->createMock(PDO::class);
        $this->statement = $this->createMock(PDOStatement::class);
        
        $this->connection->method('getPdo')->willReturn($this->pdo);
        parent::setUp();
    }

    public function testProcessSuccessfullyStoresValidUser(): void
    {
        // Mock checking for existing email
        $this->statement->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $this->statement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);
        
        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->statement);

        $result = $this->processor->process([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $this->assertTrue($result);
        $this->assertEmpty($this->processor->getErrors());
    }

    public function testProcessFailsForDuplicateEmail(): void
    {
        // Mock finding existing email
        $this->statement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->statement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);
        
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $result = $this->processor->process([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('duplicate', $this->processor->getErrors());
    }

    public function testProcessFailsForInvalidData(): void
    {
        $result = $this->processor->process([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'invalid-email'  // Invalid email format
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('validation', $this->processor->getErrors());
    }

    public function testProcessHandlesDatabaseErrors(): void
    {
        // Mock checking for existing email
        $this->statement->method('fetchColumn')->willReturn(0);
        $this->statement->method('execute')
            ->willThrowException(new \RuntimeException('Database connection failed'));
        $this->pdo
            ->method('prepare')
            ->willReturn($this->statement);

        $result = $this->processor->process([
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('database', $this->processor->getErrors());
    }
}
