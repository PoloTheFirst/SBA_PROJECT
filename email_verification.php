<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

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
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'travelgo.orbits@gmail.com';
        $mail->Password = 'upgrgcmgjicyokux'; 
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
        $verificationUrl = "http://" . $_SERVER['HTTP_HOST'] . "/SBA_PROJECT/verify_email.php?token=" . $token;
        
        // Updated email content with website color scheme - matching index.php
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Email Verification - TravelGO Orbit</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
                
                body { 
                    font-family: 'Poppins', sans-serif; 
                    background-color: #111827; 
                    color: #ffffff; 
                    margin: 0; 
                    padding: 0; 
                    line-height: 1.6;
                }
                
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #1f2937;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                }
                
                .header { 
                    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center; 
                }
                
                .logo {
                    margin-bottom: 20px;
                    font-size: 28px;
                    font-weight: bold;
                }
                
                .content { 
                    background: #1f2937; 
                    padding: 40px; 
                }
                
                .button { 
                    background: #f59e0b; 
                    color: #1e3a8a; 
                    padding: 16px 40px; 
                    text-decoration: none; 
                    border-radius: 8px; 
                    font-weight: bold; 
                    display: inline-block;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    border: none;
                    cursor: pointer;
                    text-align: center;
                }
                
                .button:hover {
                    background: #d97706;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
                }
                
                .footer { 
                    text-align: center; 
                    margin-top: 40px; 
                    font-size: 12px; 
                    color: #9ca3af; 
                    padding: 30px 20px;
                    border-top: 1px solid #374151;
                    background: #111827;
                }
                
                .verification-link {
                    background: #374151;
                    padding: 16px;
                    border-radius: 8px;
                    word-break: break-all;
                    margin: 25px 0;
                    font-family: monospace;
                    color: #f59e0b;
                    border: 1px solid #4b5563;
                    font-size: 14px;
                }
                
                .info-box {
                    background: rgba(59, 130, 246, 0.1);
                    border: 1px solid #3b82f6;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 25px 0;
                }
                
                .step-item {
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    margin-bottom: 16px;
                    padding: 12px;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 8px;
                }
                
                .step-icon {
                    color: #10b981;
                    font-weight: bold;
                    min-width: 24px;
                }
                
                .verification-url {
                    color: #93c5fd;
                    text-decoration: underline;
                    word-break: break-all;
                }
                
                @media only screen and (max-width: 600px) {
                    .container {
                        margin: 10px;
                        border-radius: 8px;
                    }
                    
                    .content {
                        padding: 25px 20px;
                    }
                    
                    .header {
                        padding: 30px 20px;
                    }
                    
                    .logo {
                        font-size: 24px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>TravelGO Orbit</div>
                    <h1 style='margin: 0; font-size: 28px; font-weight: 600;'>Email Verification Required</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Complete your account activation</p>
                </div>
                
                <div class='content'>
                    <h2 style='color: #f59e0b; margin-bottom: 20px; font-size: 22px;'>Hello " . htmlspecialchars($userName) . ",</h2>
                    
                    <p style='line-height: 1.6; margin-bottom: 25px; color: #d1d5db;'>
                        Thank you for registering with <strong style='color: #ffffff;'>TravelGO Orbit</strong>! To complete your account setup 
                        and access all features, please verify your email address by clicking the button below:
                    </p>
                    
                    <div style='text-align: center; margin: 35px 0;'>
                        <a href='" . $verificationUrl . "' class='button' style='color: #1e3a8a; text-decoration: none;'>
                            Verify Email Address
                        </a>
                    </div>
                    
                    <div class='info-box'>
                        <h3 style='color: #93c5fd; margin-top: 0; font-size: 16px;'>What happens next?</h3>
                        <div class='step-item'>
                            <span class='step-icon'>✓</span>
                            <span style='color: #d1d5db;'>Click the verification link in your email</span>
                        </div>
                        <div class='step-item'>
                            <span class='step-icon'>✓</span>
                            <span style='color: #d1d5db;'>Your account will be fully activated</span>
                        </div>
                        <div class='step-item'>
                            <span class='step-icon'>✓</span>
                            <span style='color: #d1d5db;'>Start exploring and booking your travels</span>
                        </div>
                    </div>
                    
                    <p style='margin-bottom: 10px; color: #d1d5db;'>Or copy and paste this link in your browser:</p>
                    <div class='verification-link'>
                        <a href='" . $verificationUrl . "' class='verification-url'>" . $verificationUrl . "</a>
                    </div>
                    
                    <div style='background: rgba(245, 158, 11, 0.1); border: 1px solid #f59e0b; border-radius: 8px; padding: 16px; margin: 25px 0;'>
                        <p style='color: #f59e0b; font-weight: bold; margin: 0; text-align: center;'>
                            ⚠ This link will expire in 24 hours.
                        </p>
                    </div>
                    
                    <p style='color: #9ca3af; font-size: 14px; line-height: 1.5;'>
                        If you didn't create an account with TravelGO Orbit, please ignore this email. 
                        Your email address was entered by someone else during registration.
                    </p>
                </div>
                
                <div class='footer'>
                    <p style='margin: 0 0 10px 0; color: #9ca3af;'>&copy; " . date('Y') . " TravelGO Orbit. All rights reserved.</p>
                    <p style='margin: 5px 0; color: #6b7280;'>Travel Street, Hong Kong SAR</p>
                    <p style='margin: 5px 0; color: #6b7280; font-size: 11px;'>
                        This is an automated message. Please do not reply to this email.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Alternative plain text version
        $mail->AltBody = "Hello " . $userName . ",\n\nPlease verify your email address by visiting this link: " . $verificationUrl . "\n\nThis link will expire in 24 hours.\n\nIf you didn't create an account with TravelGO Orbit, please ignore this email.";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
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