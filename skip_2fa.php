<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: register.php");
    exit();
}

// Skip 2FA setup and log user in
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['temp_user_id']]);
$user = $stmt->fetch();

if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['is_2fa_enabled'] = 0;
    
    // Cleanup temp session
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['temp_2fa_email']);
    unset($_SESSION['temp_2fa_secret']);
    
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: register.php?error=User not found");
    exit();
}
?>