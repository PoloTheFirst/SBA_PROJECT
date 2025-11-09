<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = "Invalid verification link.";
    header("Location: login.php");
    exit();
}

try {
    // Check if token exists and is not expired
    $stmt = $pdo->prepare("SELECT user_id FROM email_verification_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch();
    
    if ($token_data) {
        // Mark email as verified
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $stmt->execute([$token_data['user_id']]);
        
        // Delete used token
        $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE token = ?");
        $stmt->execute([$token]);
        
        $_SESSION['success'] = "Email verified successfully! You can now access all features.";
        
        // Update session if current user is the one being verified
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $token_data['user_id']) {
            $_SESSION['email_verified'] = 1;
        }
    } else {
        $_SESSION['error'] = "Verification link is invalid or has expired.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error verifying email: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();
?>