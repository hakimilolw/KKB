<?php
header("Content-Type: application/json"); // Set header for all API responses

$host = 'localhost';
$db = 'kkb_app';
$user = 'root'; // Replace with your database username
$pass = ''; // Replace with your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In a real app, you would log this error and not expose it publicly
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}
?>