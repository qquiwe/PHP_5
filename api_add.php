<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Дозволено лише POST-запити']);
    exit;
}

// дані можуть прийти або як звичайна форма ($_POST) або як JSON-тіло
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$amount = trim((string) ($input['amount'] ?? ''));
$category = trim((string) ($input['category'] ?? ''));
$date = trim((string) ($input['transaction_date'] ?? ''));

$errors = [];
if ($amount === '' || !is_numeric($amount)) {
    $errors[] = 'Сума має бути числом.';
}
if ($category === '') {
    $errors[] = 'Вкажіть категорію.';
}
if ($date === '' || !DateTime::createFromFormat('Y-m-d', $date)) {
    $errors[] = 'Вкажіть коректну дату у форматі РРРР-ММ-ДД.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => implode(' ', $errors)]);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO transactions (amount, category, transaction_date) VALUES (:amount, :category, :date)'
);
$stmt->execute([
    ':amount'   => (float) $amount,
    ':category' => $category,
    ':date'     => $date,
]);

$newId = (int) $pdo->lastInsertId();

$row = $pdo->prepare('SELECT * FROM transactions WHERE id = :id');
$row->execute([':id' => $newId]);
$newTransaction = $row->fetch();

$balanceStmt = $pdo->query('SELECT SUM(amount) AS balance FROM transactions');
$balance = (float) ($balanceStmt->fetch()['balance'] ?? 0);

http_response_code(201);
echo json_encode([
    'transaction' => $newTransaction,
    'balance'     => $balance,
], JSON_UNESCAPED_UNICODE);
