<?php
// Enhanced connection.php with security headers
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

$host = 'localhost';
$db   = 'travelgo';
$user = 'root';
$pass = '';
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
    error_log("Database connection failed: " . $e->getMessage());
    // Don't expose database errors to users
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php?error=Database connection error");
    } else {
        header("Location: index.php?error=System temporarily unavailable");
    }
    exit();
}
