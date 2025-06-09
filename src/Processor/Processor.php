<?php

declare(strict_types=1);

namespace App\Processor;

/**
 * Base processor interface for processing data.
 */
interface Processor
{
    /**
     * Process the given data.
     *
     * @param mixed $data The data to process
     * @return bool True if processing was successful, false otherwise
     * @throws \RuntimeException If there is an error during processing
     */
    public function process(mixed $data): bool;

    /**
     * Get any errors that occurred during processing.
     *
     * @return array<string, string> Array of error messages keyed by a meaningful identifier
     */
    public function getErrors(): array;
}
