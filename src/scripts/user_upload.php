<?php

declare(strict_types=1);

namespace App\Scripts;

require_once __DIR__ . '/../../src/bootstrap.php';

if ($argc === 1 || in_array('--help', $argv)) {
    echo "Usage: php user_upload.php [options] [csv_file]\n";
    echo "Options:\n";
    echo "--file [csv file name]    Name of the CSV file to be parsed\n";
    echo "--create_table           Build the database table\n";
    echo "--dry_run               Run the script but not insert into the DB\n";
    echo "-u [DB username]\n";
    echo "-p [DB password]\n";
    echo "-h [DB host]\n";
    echo "--help                  Display this help message\n";
    exit(0);
}

// Main script logic will be implemented here
