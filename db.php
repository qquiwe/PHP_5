<?php

$dsn = 'mysql:host=localhost;dbname=practicum4;charset=utf8mb4';
$username = 'root';
$password = ''; // вкажіть свій пароль від MySQL

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Помилка підключення до бази даних']);
    exit;
}
