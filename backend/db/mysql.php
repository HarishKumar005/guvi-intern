<?php
// backend/db/mysql.php
$config = require __DIR__ . '/config.php';
$mysql = $config['mysql'];

$dsn = "mysql:host={$mysql['host']};dbname={$mysql['dbname']};charset={$mysql['charset']}";
try {
    $pdo = new PDO($dsn, $mysql['user'], $mysql['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
