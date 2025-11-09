<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Check if email exists in database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate unique reset token
        $reset_token = bin2hex(random_bytes(50));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$reset_token, $expires, $email]);

        // In a real application, you would send an email here with a link like:
        // reset_password.php?token=$reset_token
        $_SESSION['message'] = "Password reset instructions have been sent to your email";
    } else {
        // Don't reveal whether email exists for security
        $_SESSION['message'] = "If that email exists in our system, we've sent reset instructions";
    }

    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        <form method="POST" action="forget_password.php">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit">Send Reset Instructions</button>
        </form>
        <p>Remember your password? <a href="index.php">Login here</a></p>
    </div>
</body>
</html>