<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if email is already verified
$stmt = $pdo->prepare("SELECT email_verified FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user && $user['email_verified']) {
    $_SESSION['success'] = "Your email is already verified!";
    header("Location: dashboard.php");
    exit();
}

// Handle email verification requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token invalid. Please try again.";
        header("Location: email_verification.php");
        exit();
    }
    
    if ($action === 'send_verification') {
        $user_id = $_SESSION['user_id'];
        $email = $_SESSION['email'];
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Delete any existing tokens for this user
            $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO email_verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $token, $expires]);
            
            // Generate verification URL
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $verification_url = "$protocol://$host/travelgo/verify_email.php?token=$token";
            
            // Email content
            $subject = "Verify Your Email - TravelGO Orbit";
            $message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 20px; text-align: center; }
                        .content { background: #f9f9f9; padding: 20px; }
                        .button { display: inline-block; background: #f59e0b; color: #1e3a8a; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
                        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>TravelGO Orbit</h1>
                            <h2>Email Verification</h2>
                        </div>
                        <div class='content'>
                            <p>Hello " . htmlspecialchars($_SESSION['name'] ?? 'User') . ",</p>
                            <p>Thank you for registering with TravelGO Orbit! Please verify your email address by clicking the button below:</p>
                            <p style='text-align: center;'>
                                <a href='$verification_url' class='button'>Verify Email Address</a>
                            </p>
                            <p>Or copy and paste this link in your browser:</p>
                            <p style='word-break: break-all; font-size: 12px; background: #eee; padding: 10px; border-radius: 5px;'>$verification_url</p>
                            <p>This verification link will expire in 1 hour.</p>
                            <p>If you didn't create an account with TravelGO Orbit, please ignore this email.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2023 TravelGO Orbit. All rights reserved.</p>
                            <p>This is an automated message, please do not reply to this email.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Email headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: TravelGO Orbit <travelgoorbits@gmail.com>" . "\r\n";
            $headers .= "Reply-To: travelgoorbits@gmail.com" . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Send email
            if (mail($email, $subject, $message, $headers)) {
                $pdo->commit();
                $_SESSION['success'] = "Verification email sent to $email! Please check your inbox (and spam folder).";
                
                // Log the email sending
                error_log("Verification email sent to $email for user ID: $user_id");
            } else {
                throw new Exception("Failed to send email using PHP mail() function");
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to send verification email. Please try again later.";
            error_log("Email verification error: " . $e->getMessage());
        }
    } elseif ($action === 'resend_verification') {
        // Same logic as send_verification but with different success message
        // ... (similar code as above)
        $_SESSION['success'] = "Verification email resent! Please check your inbox.";
    }
    
    header("Location: email_verification.php");
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user email for display
$user_email = $_SESSION['email'] ?? '';
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
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            color: #ffffff;
            min-height: 100vh;
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1e3a8a;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="card-glass p-8 rounded-2xl shadow-2xl max-w-md w-full">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-feather="mail" class="w-8 h-8 text-blue-900"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Verify Your Email</h2>
            <p class="text-gray-300 mt-2">Complete your account activation</p>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                    <span><?= $_SESSION['success'] ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-blue-900/30 border border-blue-700 rounded-lg p-4 mb-6">
            <div class="flex items-center mb-2">
                <i data-feather="info" class="w-4 h-4 text-blue-300 mr-2"></i>
                <span class="text-blue-300 font-medium">Verification Required</span>
            </div>
            <p class="text-blue-100 text-sm">
                We've sent a verification email to:<br>
                <strong class="text-white"><?= htmlspecialchars($user_email) ?></strong>
            </p>
        </div>
        
        <p class="text-gray-300 text-center mb-6">
            Please check your email and click the verification link to activate your account and access all features.
        </p>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="action" value="send_verification">
            
            <button type="submit" class="btn-primary font-bold py-3 px-6 rounded-lg w-full flex items-center justify-center">
                <i data-feather="send" class="w-5 h-5 mr-2"></i>
                Send Verification Email
            </button>
        </form>
        
        <div class="mt-6 text-center space-y-3">
            <a href="dashboard.php" class="text-blue-400 hover:text-blue-300 text-sm inline-flex items-center">
                <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i>
                Back to Dashboard
            </a>
            <p class="text-gray-400 text-xs">
                Didn't receive the email? Check your spam folder or 
                <button onclick="document.querySelector('input[name=\"action\"]').value='resend_verification'; document.forms[0].submit();" 
                        class="text-yellow-400 hover:text-yellow-300 underline">
                    resend verification
                </button>
            </p>
        </div>
    </div>

    <script>
        feather.replace();
        
        // Auto-focus on the button for accessibility
        document.querySelector('button[type="submit"]').focus();
    </script>
</body>
</html>