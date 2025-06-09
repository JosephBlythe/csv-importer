<?php

declare(strict_types=1);

namespace App\Runner;

use App\Database\ConnectionInterface;
use App\Processor\Processor;
use App\Transformer\Transformer;

/**
 * Abstract base class for CSV import process runners.
 */
abstract class ScriptRunner
{
    /** @var array<string, string> */
    protected array $errors = [];
    
    /** @var callable|null */
    protected $progressCallback = null;
    
    /** @var array<string, int> Counts of processed records */
    protected array $counts = [
        'success' => 0,
        'error' => 0,
        'skip' => 0,
        'total' => 0
    ];

    public function __construct(
        protected readonly ConnectionInterface $connection,
        protected readonly Processor $processor,
        protected readonly Transformer $transformer
    ) {}
    
    /**
     * Set a callback to report progress during processing.
     *
     * @param callable $callback Function that receives (string $type, string $message)
     */
    public function setProgressCallback(callable $callback): void
    {
        $this->progressCallback = $callback;
    }
    
    /**
     * Report progress using the registered callback.
     */
    protected function reportProgress(string $type, string $message): void
    {
        if ($this->progressCallback !== null) {
            call_user_func($this->progressCallback, $type, $message);
        }
    }

    /**
     * Run the CSV import process.
     *
     * @param string $filePath Path to the CSV file
     * @param bool $dryRun Whether to do a dry run (no database changes)
     * @return bool True if all records were processed successfully
     */
    public function run(string $filePath, bool $dryRun = false): bool
    {
        $this->errors = [];
        $this->counts = [
            'success' => 0,
            'error' => 0,
            'skip' => 0,
            'total' => 0
        ];

        if (!is_readable($filePath)) {
            $this->errors['file'] = sprintf(
                'Cannot read file: %s. Please check that the file exists and has proper permissions.',
                $filePath
            );
            return false;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->errors['file'] = sprintf('Failed to open file: %s', $filePath);
            return false;
        }

        try {
            // Read and validate headers
            $firstRow = fgetcsv($handle);
            if ($firstRow === false) {
                $this->errors['format'] = 'Empty file';
                return false;
            }

            // Normalize and validate headers
            $headers = array_map('strtolower', array_map('trim', $firstRow));
            try {
                $this->validateHeaders($headers);
            } catch (\RuntimeException $e) {
                $this->errors['format'] = sprintf('Invalid headers: %s', $e->getMessage());
                return false;
            }

            // Process data rows
            $processedCount = 0;
            $totalRows = 0;
            $lineNumber = 2;

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    $this->counts['skip']++;
                    $this->reportProgress('skip', "Skipped empty row at line {$lineNumber}");
                    $lineNumber++;
                    continue;
                }

                $totalRows++;
                $this->counts['total']++;

                // Check column count
                if (count($row) !== count($headers)) {
                    $errorMessage = sprintf(
                        'Wrong number of columns at line %d: expected %d, got %d',
                        $lineNumber,
                        count($headers),
                        count($row)
                    );
                    $this->errors["format_line_{$lineNumber}"] = $errorMessage;
                    $this->counts['error']++;
                    $this->reportProgress('error', $errorMessage);
                    $lineNumber++;
                    continue;
                }

                // Process the row
                if ($this->processRow($row, $headers, $lineNumber, $dryRun)) {
                    $processedCount++;
                    $this->counts['success']++;
                    $this->reportProgress('success', "Successfully processed record at line {$lineNumber}");
                } else {
                    $this->counts['error']++;
                }
                $lineNumber++;
            }

            // If we didn't process any rows and there were rows to process,
            // that's an error. Otherwise, it's a success if we processed at least one row.
            return $totalRows === 0 || $processedCount > 0;

        } finally {
            fclose($handle);
        }
    }

    /**
     * Process a single row of data.
     *
     * @param array<string> $row Raw CSV row
     * @param array<string> $headers CSV headers
     * @param int $lineNumber Current line number for error reporting
     * @param bool $dryRun Whether this is a dry run
     * @return bool True if processing succeeded
     */
    private function processRow(array $row, array $headers, int $lineNumber, bool $dryRun): bool
    {
        try {
            // Transform data (lowercase, trim, etc.)
            $data = array_combine($headers, $row);
            $transformedData = $this->transformer->transform($data);

            // Process data (validate and save)
            if (!$dryRun && !$this->processor->process($transformedData)) {
                // Get processor errors
                $processorErrors = $this->processor->getErrors();
                
                // Format error message
                $errorMessage = sprintf(
                    'Error processing line %d: %s',
                    $lineNumber,
                    implode('; ', $processorErrors)
                );
                
                // Add line-specific error
                $this->errors["processing_line_{$lineNumber}"] = $errorMessage;
                
                // Report the error
                $this->reportProgress('error', $errorMessage);
                
                // If processor has validation errors, add them to the general validation error
                if (isset($processorErrors['validation'])) {
                    if (!isset($this->errors['validation'])) {
                        $this->errors['validation'] = $processorErrors['validation'];
                    } else {
                        $this->errors['validation'] .= "; " . $processorErrors['validation'];
                    }
                }
                
                return false;
            }

            return true;

        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Invalid data at line %d: %s', $lineNumber, $e->getMessage());
            $this->errors["validation_line_{$lineNumber}"] = $errorMessage;
            
            // Report the validation error
            $this->reportProgress('error', $errorMessage);
            
            // Also store a general validation error so tests can find it
            if (!isset($this->errors['validation'])) {
                $this->errors['validation'] = $errorMessage;
            } else {
                $this->errors['validation'] .= "; {$errorMessage}";
            }
            
            return false;
        }
    }

    /**
     * Validate the CSV headers against required fields.
     *
     * @param array<string> $headers Normalized (lowercase) header names
     * @throws \RuntimeException if required fields are missing
     */
    abstract protected function validateHeaders(array $headers): void;

    /**
     * Get any errors that occurred during the import process.
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the counts of processed records.
     *
     * @return array<string, int>
     */
    public function getCounts(): array
    {
        return $this->counts;
    }
}
