<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? '';
$valid_token = false;
$email = '';
$message = '';
$message_type = '';

if ($token) {
    try {
        // Validate token
        $stmt = $pdo->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();
        
        if ($token_data) {
            $valid_token = true;
            $email = $token_data['email'];
        } else {
            $message = "Invalid or expired reset token. Please request a new password reset.";
            $message_type = 'error';
        }
    } catch (Exception $e) {
        error_log("Token validation error: " . $e->getMessage());
        $message = "An error occurred. Please try again.";
        $message_type = 'error';
    }
} else {
    $message = "No reset token provided.";
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $message = "Security token invalid. Please try again.";
        $message_type = 'error';
    } elseif (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all password fields.";
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } elseif (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->execute([$password_hash, $email]);
            
            // Delete used token
            $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
            $stmt->execute([$token]);
            
            // Create notification
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message) 
                SELECT id, 'security', 'Password Reset', 'Your password was successfully reset.' 
                FROM users WHERE email = ?
            ");
            $stmt->execute([$email]);
            
            $pdo->commit();
            
            $message = "Password has been reset successfully! You can now login with your new password.";
            $message_type = 'success';
            $valid_token = false; // Prevent form from being used again
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Password reset error: " . $e->getMessage());
            $message = "An error occurred while resetting your password. Please try again.";
            $message_type = 'error';
        }
    }
    
    // Regenerate CSRF token
    unset($_SESSION['csrf_token']);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <!-- Reset Password Card -->
    <div class="login-card rounded-xl p-8 w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center text-white mb-4">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Home
            </a>
            <h2 class="text-3xl font-bold text-white mb-2">Set New Password</h2>
            <p class="text-blue-200">Create your new password</p>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="<?= $message_type === 'error' ? 'bg-red-900 border border-red-700' : 'bg-green-900 border border-green-700' ?> text-white px-4 py-3 rounded-lg mb-4">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($valid_token || $message_type === 'success'): ?>
            <!-- Reset Password Form -->
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div>
                    <label for="new_password" class="block text-white text-sm font-medium mb-1">New Password</label>
                    <div class="relative">
                        <i data-feather="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="password" id="new_password" name="new_password" required minlength="8"
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 placeholder-gray-500"
                            placeholder="Enter new password">
                    </div>
                    <p class="text-gray-400 text-xs mt-1">Must be at least 8 characters long</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-white text-sm font-medium mb-1">Confirm Password</label>
                    <div class="relative">
                        <i data-feather="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 placeholder-gray-500"
                            placeholder="Confirm new password">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors mt-4">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-6">
            <p class="text-blue-200">
                <a href="login.php" class="text-white font-medium hover:text-yellow-400 transition-colors">Back to Login</a>
            </p>
        </div>

        <?php if ($message_type === 'success'): ?>
            <div class="text-center mt-4">
                <a href="login.php" 
                   class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                    Go to Login
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        feather.replace();
        
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (newPassword.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Passwords do not match. Please check your entries.');
                        confirmPassword.focus();
                    }
                });
            }
        });
    </script>
</body>
</html>