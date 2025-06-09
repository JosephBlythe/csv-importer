<?php

declare(strict_types=1);

namespace App\Scripts;

use App\Database\TableCreator;
use App\Database\Connection;

require_once __DIR__ . '/../../src/bootstrap.php';

if ($argc === 1 || in_array('--help', $argv)) {
    echo "Usage: php user_upload.php [options] [csv_file]\n";
    echo "Options:\n";
    echo "--file [csv file name]    Name of the CSV file to be parsed\n";
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

// Create database connection using potentially overridden environment variables
try {
    $connection = Connection::fromEnvironment();
    $tableCreator = new TableCreator($connection);
} catch (\RuntimeException $e) {
    echo "ERROR: Failed to connect to database: {$e->getMessage()}\n";
    exit(1);
}

// Handle table operations
try {
    // Handle --drop_table option
    if (isset($options['drop_table'])) {
        $result = $tableCreator->dropTable();
        if ($result) {
            echo "SUCCESS: Users table has been dropped successfully.\n";
            // Exit if only dropping table was requested
            if (!isset($options['create_table'])) {
                exit(0);
            }
        } else {
            echo "ERROR: Failed to drop users table.\n";
            exit(1);
        }
    }

    // Handle --create_table option
    if (isset($options['create_table'])) {
        $result = $tableCreator->createTable();
        if ($result) {
            echo "SUCCESS: Users table has been created successfully.\n";
            exit(0);
        } else {
            echo "ERROR: Failed to create users table.\n";
            exit(1);
        }
    }
} catch (\RuntimeException $e) {
    echo "ERROR: {$e->getMessage()}\n";
    exit(1);
}

// Main script logic will be implemented in upcoming steps
