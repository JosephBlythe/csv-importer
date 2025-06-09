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
}
