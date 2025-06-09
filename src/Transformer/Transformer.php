<?php

declare(strict_types=1);

namespace App\Transformer;

/**
 * Base transformer interface for transforming raw data into a specific format.
 */
interface Transformer
{
    /**
     * Transform raw data into the required format.
     *
     * @param mixed $data The raw data to transform
     * @return mixed The transformed data
     * @throws \InvalidArgumentException If the data is invalid or cannot be transformed
     */
    public function transform(mixed $data): mixed;
}
