<?php

declare(strict_types=1);

namespace App\Scripts;

use App\Database\TableCreator;
use App\Database\Connection;
use App\Runner\UserScriptRunner;

require_once __DIR__ . '/../../src/bootstrap.php';

if ($argc === 1 || in_array('--help', $argv)) {
    echo "Usage: php user_upload.php [options] [csv_file]\n";
    echo "Options:\n";
    echo "--file [csv file name]    Name of the CSV file to be parsed (must have headers)\n";
    echo "--create_table           Build the database table\n";
    echo "--drop_table            Drop the database table if it exists\n";
    echo "--dry_run               Run the script but not insert into the DB\n";
    echo "-u [DB username]\n";
    echo "-p [DB password]\n";
    echo "-h [DB host]\n";
    echo "--help                  Display this help message\n";
    exit(0);
}

// Process command line arguments
$options = getopt('u:p:h:', ['file:', 'create_table', 'drop_table', 'dry_run', 'help']);

// Apply command line database credentials if provided
if (isset($options['h'])) {
    $_ENV['DB_HOST'] = $options['h'];
}
if (isset($options['u'])) {
    $_ENV['DB_USER'] = $options['u'];
}
if (isset($options['p'])) {
    $_ENV['DB_PASSWORD'] = $options['p'];
}

// Validate that we have a CSV file if not creating/dropping table
if (!isset($options['create_table']) && !isset($options['drop_table']) && !isset($options['file'])) {
    echo "ERROR: Please provide a CSV file with --file option or use --help for usage information.\n";
    exit(1);
}

// Create database connection and script runner
try {
    $connection = Connection::fromEnvironment();
    $runner = new UserScriptRunner($connection);
} catch (\RuntimeException $e) {
    echo "ERROR: Failed to connect to database: {$e->getMessage()}\n";
    exit(1);
}

// Determine if this is a dry run
$dryRun = isset($options['dry_run']);
if ($dryRun) {
    echo "DRY RUN MODE: No changes will be made to the database.\n\n";
}

try {
    // Handle table operations
    if (isset($options['drop_table'])) {
        $result = $runner->dropTable($dryRun);
        if ($result) {
            echo $dryRun ? "DRY RUN: Would drop users table.\n" : "SUCCESS: Users table has been dropped successfully.\n";
            // Exit if only dropping table was requested
            if (!isset($options['create_table'])) {
                exit(0);
            }
        } else {
            echo "ERROR: Failed to drop users table.\n";
            foreach ($runner->getErrors() as $type => $message) {
                echo "- $type: $message\n";
            }
            exit(1);
        }
    }

    if (isset($options['create_table'])) {
        $result = $runner->createTable($dryRun);
        if ($result) {
            echo $dryRun ? "DRY RUN: Would create users table.\n" : "SUCCESS: Users table has been created successfully.\n";
            if (!isset($options['file'])) {
                exit(0);
            }
        } else {
            echo "ERROR: Failed to create users table.\n";
            foreach ($runner->getErrors() as $type => $message) {
                echo "- $type: $message\n";
            }
            exit(1);
        }
    }

    // Process CSV file if provided
    if (isset($options['file'])) {
        // Set up a progress callback to show real-time feedback
        $runner->setProgressCallback(function(string $type, string $message) {
            // Only show errors and skipped records, not successes
            if ($type === 'error') {
                echo "✗ SKIPPED: " . $message . PHP_EOL;
            } elseif ($type === 'skip') {
                echo "• SKIPPED: " . $message . PHP_EOL;
            }
        });
        
        $result = $runner->run(
            filePath: $options['file'],
            dryRun: $dryRun
        );

        if ($result) {
            $counts = $runner->getCounts();
            echo sprintf(
                "SUCCESS: Successfully imported %d of %d records, %d skipped due to errors.\n",
                $counts['success'],
                $counts['total'],
                $counts['error']
            );
            exit(0);
        } else {
            echo "ERROR: CSV import failed.\n";
            foreach ($runner->getErrors() as $type => $message) {
                echo "- $type: $message\n";
            }
            exit(1);
        }
    }
} catch (\RuntimeException $e) {
    echo "ERROR: {$e->getMessage()}\n";
    exit(1);
}
