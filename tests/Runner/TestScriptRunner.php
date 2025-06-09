<?php

declare(strict_types=1);

namespace App\Tests\Runner;

use App\Runner\ScriptRunner;

/**
 * Test implementation of ScriptRunner for testing purposes.
 */
final class TestScriptRunner extends ScriptRunner
{
    public function createTable(bool $dryRun = false): bool
    {
        return true;
    }

    public function dropTable(bool $dryRun = false): bool
    {
        return true;
    }

    protected function validateHeaders(array $headers): void
    {
        if (!in_array('name', $headers) || !in_array('surname', $headers)) {
            throw new \RuntimeException('Missing required headers: name and surname');
        }
    }
}
