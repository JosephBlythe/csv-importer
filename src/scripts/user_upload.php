<?php

declare(strict_types=1);

namespace App\Scripts;

use App\Database\TableCreator;
use App\Database\Connection;
use App\Runner\UserScriptRunner;
use Symfony\Component\Console\Helper\Table;

require_once __DIR__ . '/../../src/bootstrap.php';

// Create runner with default console output
$connection = Connection::fromEnvironment();
$runner = new UserScriptRunner($connection);
$output = $runner->getOutput();

// Force colors in development Docker environment
if (getenv('APP_ENV') === 'development') {
    putenv('COLUMNS=120');
    putenv('TERM=xterm-256color');
    $output->setDecorated(true);
}

// Set console width for table formatting
if ($output instanceof \Symfony\Component\Console\Output\ConsoleOutput) {
    $terminal = new \Symfony\Component\Console\Terminal();
    putenv('COLUMNS=' . $terminal->getWidth());
}

if ($argc === 1 || in_array('--help', $argv)) {
    $output->writeln([
        '<info>CSV Importer Tool</info>',
        '<comment>A command line tool to import CSV data into PostgreSQL database</comment>',
        '',
        '<info>Usage:</info>',
        '  php user_upload.php [options] [csv_file]',
        '',
        '<info>Options:</info>',
        '  <comment>--file</comment> [csv file name]   Name of the CSV file to be parsed (must have headers)',
        '  <comment>--create_table</comment>           Build the database table',
        '  <comment>--drop_table</comment>             Drop the database table if it exists',
        '  <comment>--dry_run</comment>                Run the script but not insert into the DB',
        '  <comment>-u</comment> [DB username]         Database username',
        '  <comment>-p</comment> [DB password]         Database password',
        '  <comment>-h</comment> [DB host]             Database host',
        '  <comment>--verbose</comment>                Show detailed progress messages',
        '  <comment>--help</comment>                   Display this help message',
        '',
    ]);
    exit(0);
}

// Process command line arguments
$options = getopt('u:p:h:', ['file:', 'create_table', 'drop_table', 'dry_run', 'verbose', 'help']);

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
    $output->writeln('<error>ERROR: Please provide a CSV file with --file option or use --help for usage information.</error>');
    exit(1);
}

// Verify database connection
try {
    $output->write('<info>Verifying database connection... </info>');
    $connection->getPdo();
    $output->writeln('<success>✓</success>');
} catch (\RuntimeException $e) {
    $output->writeln('<error>✗</error>');
    $output->writeln('<error>ERROR: Failed to connect to database: ' . $e->getMessage() . '</error>');
    exit(1);
}

// Determine if this is a dry run
$dryRun = isset($options['dry_run']);
if ($dryRun) {
    $output->writeln('<comment>DRY RUN MODE: No changes will be made to the database.</comment>');
}

try {
    // Handle table operations
    if (isset($options['drop_table'])) {
        $output->write($dryRun ? '<info>Would drop users table... </info>' : '<info>Dropping users table... </info>');
        $result = $runner->dropTable($dryRun);
        if ($result) {
            $output->writeln('<success>✓</success>');
            // Exit if only dropping table was requested
            if (!isset($options['create_table'])) {
                exit(0);
            }
        } else {
            $output->writeln('<error>✗</error>');
            $output->writeln('<error>ERROR: Failed to drop users table.</error>');
            foreach ($runner->getErrors() as $type => $message) {
                $output->writeln("  <comment>- {$type}:</comment> {$message}");
            }
            exit(1);
        }
    }

    if (isset($options['create_table'])) {
        $output->write($dryRun ? '<info>Would create users table... </info>' : '<info>Creating users table... </info>');
        $result = $runner->createTable($dryRun);
        if ($result) {
            $output->writeln('<success>✓</success>');
            if (!isset($options['file'])) {
                exit(0);
            }
        } else {
            $output->writeln('<error>✗</error>');
            $output->writeln('<error>ERROR: Failed to create users table.</error>');
            foreach ($runner->getErrors() as $type => $message) {
                $output->writeln("  <comment>- {$type}:</comment> {$message}");
            }
            exit(1);
        }
    }

    // Process CSV file if provided
    if (isset($options['file'])) {
        $filePath = $options['file'];
        
        $output->writeln('<info>Preparing to import from ' . basename($filePath) . '...</info>');
        
        // Set up a progress callback to show real-time feedback
        $errorsDetected = false;
        $isVerbose = isset($options['verbose']);
        $runner->setVerbose($isVerbose);
        $runner->setProgressCallback(function(string $type, string $message) use ($output, &$errorsDetected, $isVerbose) {
            if ($type === 'error') {
                $output->writeln('<error>✗ SKIPPED: ' . $message . '</error>');
                $errorsDetected = true;
            } elseif ($type === 'skip') {
                $output->writeln('<comment>• SKIPPED: ' . $message . '</comment>');
            } elseif ($type === 'success') {
                $output->writeln('<info>✓ IMPORTED: ' . $message . '</info>');
            }
        });
        
        // Run the import
        $result = $runner->run(
            filePath: $filePath,
            dryRun: $dryRun
        );
        
        $output->writeln('');
        
        if ($result) {
            $counts = $runner->getCounts();
            
            // Display a summary table
            $table = new Table($output);
            $table->setHeaderTitle('Import Summary');
            $table->setHeaders(['Status', 'Count']);
            $table->setRows([
                ['Successful', $counts['success']],
                ['Skipped', $counts['error']],
                ['Total', $counts['total']]
            ]);
            $table->render();
            $output->writeln('');
            
            if ($errorsDetected) {
                $output->writeln('<comment>Some records were skipped due to errors.</comment>');
            }
            
            $output->writeln('<success>CSV import completed successfully.</success>');
            exit(0);
        } else {
            $output->writeln('<error>CSV import failed.</error>');
            foreach ($runner->getErrors() as $type => $message) {
                $output->writeln("<comment>- {$type}:</comment> {$message}");
            }
            exit(1);
        }
    }
} catch (\RuntimeException $e) {
    echo "ERROR: {$e->getMessage()}\n";
    exit(1);
}
