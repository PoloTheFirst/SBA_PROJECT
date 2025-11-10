<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

$message = '';
$error = '';

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Check if token exists and is valid
        $stmt = $pdo->prepare("SELECT * FROM email_verification_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if ($tokenData) {
            // Update user's email verification status
            $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
            $stmt->execute([$tokenData['user_id']]);
            
            // Delete the used token
            $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE id = ?");
            $stmt->execute([$tokenData['id']]);
            
            $message = "Your email has been successfully verified!";
            
            // If user is logged in, update session
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $tokenData['user_id']) {
                $_SESSION['email_verified'] = true;
                $_SESSION['success'] = $message;
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid or expired verification token. Please request a new verification email.";
        }
        
    } catch (Exception $e) {
        $error = "Error verifying email: " . $e->getMessage();
    }
} else {
    $error = "No verification token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #111827; 
            color: #ffffff; 
        }
        .card-dark { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="card-dark rounded-xl shadow-lg p-8 max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <a href="index.php" class="flex items-center justify-center space-x-2 mb-4">
                <i data-feather="navigation" class="text-yellow-400"></i>
                <span class="text-xl font-bold">TravelGO Orbit</span>
            </a>
            <h1 class="text-2xl font-bold text-white">Email Verification</h1>
        </div>

        <!-- Success Message -->
        <?php if (!empty($message)): ?>
            <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-6 text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <i data-feather="check-circle" class="w-6 h-6"></i>
                    <span class="font-semibold">Success!</span>
                </div>
                <?= htmlspecialchars($message) ?>
            </div>
            <div class="text-center">
                <a href="login.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded-lg transition-colors inline-flex items-center">
                    <i data-feather="log-in" class="w-5 h-5 mr-2"></i>
                    Proceed to Login
                </a>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-6 text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <i data-feather="alert-circle" class="w-6 h-6"></i>
                    <span class="font-semibold">Error</span>
                </div>
                <?= htmlspecialchars($error) ?>
            </div>
            <div class="text-center space-y-3">
                <a href="email_verification.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded-lg transition-colors inline-flex items-center">
                    <i data-feather="mail" class="w-5 h-5 mr-2"></i>
                    Request New Verification Email
                </a>
                <div class="mt-4">
                    <a href="index.php" class="text-gray-400 hover:text-white text-sm transition-colors inline-flex items-center">
                        <i data-feather="home" class="w-4 h-4 mr-1"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>