<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$rootDir = dirname(__DIR__);
$dotenv = Dotenv::createImmutable($rootDir);
$dotenv->safeLoad();

// Set default values if not provided
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'db';
$_ENV['DB_PORT'] = $_ENV['DB_PORT'] ?? '5432';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'csv_importer';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'postgres';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? 'secret';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('UTC');

// Database configuration
$dbConfig = [
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
];

return $dbConfig;
