<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    // жива фільтрація за категорією
    $stmt = $pdo->prepare(
        'SELECT * FROM transactions WHERE category LIKE :q ORDER BY transaction_date DESC, id DESC'
    );
    $stmt->execute([':q' => '%' . $q . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM transactions ORDER BY transaction_date DESC, id DESC');
}

$transactions = $stmt->fetchAll();

// баланс завжди рахується за всіма записами, незалежно від фільтра пошуку
$balanceStmt = $pdo->query('SELECT SUM(amount) AS balance FROM transactions');
$balance = (float) ($balanceStmt->fetch()['balance'] ?? 0);

echo json_encode([
    'transactions' => $transactions,
    'balance'       => $balance,
], JSON_UNESCAPED_UNICODE);