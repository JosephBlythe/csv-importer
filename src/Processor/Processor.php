<?php

declare(strict_types=1);

namespace App\Processor;

/**
 * Base processor interface for processing data.
 */
interface Processor
{
    /**
     * Validate the given data without processing it.
     *
     * @param mixed $data The data to validate
     * @return bool True if validation passes, false otherwise
     */
    public function validate(mixed $data): bool;

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
