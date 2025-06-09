<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure;

use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class EnvironmentTest extends TestCase
{
    public function testPhpVersion(): void
    {
        $this->assertTrue(
            version_compare(PHP_VERSION, '8.3.0', '>='),
            'PHP version must be 8.3 or higher'
        );
    }

    public function testRequiredExtensions(): void
    {
        $requiredExtensions = [
            'pdo',
            'pdo_pgsql',
            'xml',
            'mbstring',
            'zip',
            'curl',
            'dom'
        ];

        foreach ($requiredExtensions as $extension) {
            $this->assertTrue(
                extension_loaded($extension),
                sprintf('Required PHP extension "%s" is missing', $extension)
            );
        }
    }
}
