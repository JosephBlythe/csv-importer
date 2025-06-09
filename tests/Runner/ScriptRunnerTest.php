<?php

declare(strict_types=1);

namespace App\Tests\Runner;

use App\Database\ConnectionInterface;
use App\Processor\Processor;
use App\Runner\ScriptRunner;
use App\Transformer\Transformer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ScriptRunner::class)]
class ScriptRunnerTest extends TestCase
{
    private ScriptRunner $runner;
    private ConnectionInterface&MockObject $connection;
    private Processor&MockObject $processor;
    private Transformer&MockObject $transformer;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->processor = $this->createMock(Processor::class);
        $this->transformer = $this->createMock(Transformer::class);

        $this->runner = new TestScriptRunner(
            $this->connection,
            $this->processor,
            $this->transformer
        );
        
        // Enable test mode to suppress console output
        $this->runner->setTestMode(true);
    }

    public function testRunProcessesCsvFileSuccessfully(): void
    {
        $csvFile = __DIR__ . '/../../data/test.csv';
        $csvData = "name,surname,email\njohn,doe,john.doe@example.com\n";
        
        // Create test CSV file
        file_put_contents($csvFile, $csvData);

        $this->transformer->expects($this->once())
            ->method('transform')
            ->willReturn([
                'name' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com'
            ]);

        $this->processor->expects($this->once())
            ->method('process')
            ->willReturn(true);

        $result = $this->runner->run($csvFile);

        $this->assertTrue($result);
        $this->assertEmpty($this->runner->getErrors());

        // Clean up test file
        unlink($csvFile);
    }

    public function testRunHandlesMissingFile(): void
    {
        $result = $this->runner->run('nonexistent.csv');

        $this->assertFalse($result);
        $this->assertArrayHasKey('file', $this->runner->getErrors());
    }

    public function testRunHandlesInvalidCsvFormat(): void
    {
        $csvFile = __DIR__ . '/../../data/test.csv';
        // Create CSV with missing required column
        file_put_contents($csvFile, "email\njohn.doe@example.com\n");

        $result = $this->runner->run($csvFile);

        $this->assertFalse($result);
        $this->assertArrayHasKey('format', $this->runner->getErrors());
        $this->assertStringContainsString('Missing required headers', $this->runner->getErrors()['format']);

        unlink($csvFile);
    }

    public function testDryRunDoesNotCallProcessor(): void
    {
        $csvFile = __DIR__ . '/../../data/test.csv';
        file_put_contents($csvFile, "name,surname,email\njohn,doe,john.doe@example.com\n");

        $this->transformer->expects($this->once())
            ->method('transform')
            ->willReturn([
                'name' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com'
            ]);

        $this->processor->expects($this->never())
            ->method('process');

        $result = $this->runner->run($csvFile, dryRun: true);

        $this->assertTrue($result);
        $this->assertEmpty($this->runner->getErrors());

        unlink($csvFile);
    }

    public function testRunHandlesProcessingErrors(): void
    {
        $csvFile = __DIR__ . '/../../data/test.csv';
        file_put_contents($csvFile, "name,surname,email\njohn,doe,john.doe@example.com\n");

        $this->transformer->expects($this->once())
            ->method('transform')
            ->willReturn([
                'name' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@example.com'
            ]);

        $this->processor->expects($this->once())
            ->method('process')
            ->willReturn(false);

        $this->processor->expects($this->once())
            ->method('getErrors')
            ->willReturn(['validation' => 'Invalid data format']);

        $result = $this->runner->run($csvFile);

        $this->assertFalse($result);
        $this->assertArrayHasKey('processing', $this->runner->getErrors());

        unlink($csvFile);
    }
}
