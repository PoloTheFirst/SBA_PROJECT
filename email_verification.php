<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Debug: Check if PHPMailer files exist
echo "Checking PHPMailer files:<br>";
$files = [
    'PHPMailer/src/Exception.php',
    'PHPMailer/src/PHPMailer.php', 
    'PHPMailer/src/SMTP.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>";
    } else {
        echo "✗ $file MISSING<br>";
    }
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check if email is already verified
if ($user['email_verified']) {
    $_SESSION['success'] = "Your email is already verified!";
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $error = "Security token invalid. Please try again.";
    } else {
        try {
            // Generate verification token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Delete any existing tokens for this user
            $stmt = $pdo->prepare("DELETE FROM email_verification_tokens WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO email_verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $token, $expires_at]);
            
            // Send verification email using PHPMailer
            if (sendVerificationEmail($user['email'], $user['name'], $token)) {
                $_SESSION['success'] = "Verification email sent successfully! Please check your inbox.";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Failed to send verification email. Please try again.";
            }
            
        } catch (Exception $e) {
            $error = "Error generating verification token: " . $e->getMessage();
        }
    }
}

/**
 * Send verification email using PHPMailer
 */
function sendVerificationEmail($toEmail, $userName, $token) {
    $mail = new PHPMailer(true);

    try {
        // Enable verbose debugging
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents('smtp_debug.log', date('Y-m-d H:i:s') . " [{$level}]: {$str}\n", FILE_APPEND | LOCK_EX);
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'travelgo.orbits@gmail.com';
        $mail->Password = 'upgrgcmgjicyokux'; // ← Replace with new app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Additional settings for better compatibility
        $mail->Timeout = 30;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('travelgo.orbits@gmail.com', 'TravelGO Orbit');
        $mail->addAddress($toEmail, $userName);
        $mail->addReplyTo('travelgo.orbits@gmail.com', 'TravelGO Orbit');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - TravelGO Orbit';
        
        // Create verification URL
        $verificationUrl = "http://" . $_SERVER['HTTP_HOST'] . "/verify_email.php?token=" . $token;
        
        // Email content (your existing HTML template)
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f59e0b, #1e3a8a); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { background: #f59e0b; color: #1e3a8a; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>TravelGO Orbit</h1>
                    <h2>Email Verification</h2>
                </div>
                <div class='content'>
                    <h3>Hello " . htmlspecialchars($userName) . ",</h3>
                    <p>Thank you for registering with TravelGO Orbit! To complete your account setup and access all features, please verify your email address by clicking the button below:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . $verificationUrl . "' class='button'>Verify Email Address</a>
                    </div>
                    
                    <p>Or copy and paste this link in your browser:</p>
                    <p style='word-break: break-all; background: #eee; padding: 10px; border-radius: 5px;'>" . $verificationUrl . "</p>
                    
                    <p><strong>This link will expire in 24 hours.</strong></p>
                    
                    <p>If you didn't create an account with TravelGO Orbit, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " TravelGO Orbit. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Alternative plain text version
        $mail->AltBody = "Hello " . $userName . ",\n\nPlease verify your email address by visiting this link: " . $verificationUrl . "\n\nThis link will expire in 24 hours.\n\nIf you didn't create an account with TravelGO Orbit, please ignore this email.";

        $mail->send();
        
        // Log success
        error_log("Email sent successfully to: " . $toEmail);
        return true;
        
    } catch (Exception $e) {
        // Log detailed error
        error_log("PHPMailer Error: " . $e->getMessage());
        error_log("SMTP Debug: " . $mail->ErrorInfo);
        return false;
    }
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
        body { font-family: 'Poppins', sans-serif; background-color: #111827; color: #ffffff; }
        .card-dark { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="card-dark rounded-xl shadow-lg p-8 max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <a href="index.php" class="flex items-center justify-center space-x-2 mb-4">
                    <i data-feather="navigation" class="text-yellow-400"></i>
                    <span class="text-xl font-bold">TravelGO Orbit</span>
                </a>
                <h1 class="text-2xl font-bold text-white">Verify Your Email</h1>
                <p class="text-gray-400 mt-2">Complete your account activation</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Information Card -->
            <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-lg p-6 mb-6">
                <div class="flex items-start space-x-3">
                    <i data-feather="mail" class="w-6 h-6 text-blue-300 mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-white">Email Verification Required</h3>
                        <p class="text-blue-200 text-sm mt-1">
                            We need to verify your email address: <strong><?= htmlspecialchars($user['email']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-gray-800 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-white mb-2">What happens next?</h4>
                <ul class="text-sm text-gray-300 space-y-2">
                    <li class="flex items-center space-x-2">
                        <i data-feather="check" class="w-4 h-4 text-green-400"></i>
                        <span>Click the button below to send verification email</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-feather="check" class="w-4 h-4 text-green-400"></i>
                        <span>Check your inbox for the verification link</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-feather="check" class="w-4 h-4 text-green-400"></i>
                        <span>Click the link to complete verification</span>
                    </li>
                </ul>
            </div>

            <!-- Verification Form -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <button type="submit" 
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                    <i data-feather="send" class="w-5 h-5 mr-2"></i>
                    Send Verification Email
                </button>
            </form>

            <!-- Additional Options -->
            <div class="mt-6 text-center space-y-3">
                <a href="dashboard.php" class="text-gray-400 hover:text-white text-sm transition-colors inline-flex items-center">
                    <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i>
                    Back to Dashboard
                </a>
                <div class="text-xs text-gray-500">
                    Didn't receive the email? Check your spam folder or try again.
                </div>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>