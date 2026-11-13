<?php
declare(strict_types=1);

$host = "localhost";
$database = "blogs3";
$username = "root";
$password = "YOUR_MYSQL_PASSWORD";
$port = 3306;

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $username, $password, $options);
