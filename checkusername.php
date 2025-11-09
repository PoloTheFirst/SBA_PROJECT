<?php
require 'connection.php';

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode(['available' => false]);
    exit();
}

// Check if username exists (excluding current user if logged in)
$sql = "SELECT id FROM users WHERE username = ?";
$params = [$username];

if (isset($_SESSION['user_id'])) {
    $sql .= " AND id != ?";
    $params[] = $_SESSION['user_id'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['available' => !$stmt->fetch()]);
?>