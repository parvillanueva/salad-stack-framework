<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Salad\Core\Migration;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dsn = "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}";
$pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$migration = new Migration($pdo, dirname(__DIR__));
$migration->runMigrations();
