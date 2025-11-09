<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$step = $_GET['step'] ?? 1; // 1: Email entry, 2: Security question, 3: Reset password
$email = '';
$security_question = '';
$message = '';
$message_type = '';

// Common security questions with fake answers for demo
$security_questions = [
    "What was your first pet's name?" => "Buddy",
    "What city were you born in?" => "Springfield",
    "What is your mother's maiden name?" => "Johnson",
    "What was the name of your first school?" => "Maple Elementary",
    "What is your favorite book?" => "The Great Gatsby",
    "What was your childhood nickname?" => "Ace",
    "What is the name of your favorite childhood friend?" => "Mike",
    "What street did you grow up on?" => "Oak Street",
    "What was your dream job as a child?" => "Astronaut",
    "What is the last name of your favorite teacher?" => "Davis"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $message = "Security token invalid. Please try again.";
        $message_type = 'error';
    } else {
        try {
            switch ($step) {
                case 1:
                    // Step 1: Verify email
                    $email = $_POST['email'] ?? '';
                    
                    if (empty($email)) {
                        $message = "Please enter your email address.";
                        $message_type = 'error';
                    } else {
                        // Check if email exists
                        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();

                        if ($user) {
                            // For demo purposes, we'll use a random security question
                            // In a real app, this would be user-defined
                            $random_question = array_rand($security_questions);
                            $security_question = $random_question;
                            $_SESSION['reset_email'] = $email;
                            $_SESSION['security_question'] = $security_question;
                            $_SESSION['expected_answer'] = $security_questions[$random_question];
                            $step = 2;
                        } else {
                            // Don't reveal if email exists for security
                            $message = "If this email exists in our system, you'll be able to reset your password.";
                            $message_type = 'info';
                            // Still move to next step for security
                            $_SESSION['reset_email'] = $email;
                            $random_question = array_rand($security_questions);
                            $security_question = $random_question;
                            $_SESSION['security_question'] = $security_question;
                            $_SESSION['expected_answer'] = $security_questions[$random_question];
                            $step = 2;
                        }
                    }
                    break;

                case 2:
                    // Step 2: Verify security question
                    $answer = trim($_POST['security_answer'] ?? '');
                    
                    if (empty($answer)) {
                        $message = "Please answer the security question.";
                        $message_type = 'error';
                    } else {
                        // For demo, we'll verify against our fake answers
                        $expected_answer = $_SESSION['expected_answer'] ?? '';
                        if (strtolower($answer) === strtolower($expected_answer)) {
                            $step = 3;
                        } else {
                            $message = "Incorrect answer. Please try again.";
                            $message_type = 'error';
                        }
                    }
                    break;

                case 3:
                    // Step 3: Reset password
                    $new_password = $_POST['new_password'] ?? '';
                    $confirm_password = $_POST['confirm_password'] ?? '';
                    $email = $_SESSION['reset_email'] ?? '';

                    if (empty($new_password) || empty($confirm_password)) {
                        $message = "Please fill in all password fields.";
                        $message_type = 'error';
                    } elseif ($new_password !== $confirm_password) {
                        $message = "Passwords do not match.";
                        $message_type = 'error';
                    } elseif (strlen($new_password) < 8) {
                        $message = "Password must be at least 8 characters long.";
                        $message_type = 'error';
                    } else {
                        // Update password in database
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                        $stmt->execute([$password_hash, $email]);

                        // Create notification
                        $stmt = $pdo->prepare("
                            INSERT INTO notifications (user_id, type, title, message) 
                            SELECT id, 'security', 'Password Reset', 'Your password was successfully reset.' 
                            FROM users WHERE email = ?
                        ");
                        $stmt->execute([$email]);

                        $message = "Password has been reset successfully! You can now login with your new password.";
                        $message_type = 'success';
                        
                        // Clear reset session
                        unset($_SESSION['reset_email']);
                        unset($_SESSION['security_question']);
                        unset($_SESSION['expected_answer']);
                        
                        // Redirect to login after 3 seconds
                        header("Refresh: 3; url=login.php");
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = "An error occurred. Please try again.";
            $message_type = 'error';
        }
    }
    
    // Regenerate CSRF token
    unset($_SESSION['csrf_token']);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get data from session if available
$email = $_SESSION['reset_email'] ?? $email;
$security_question = $_SESSION['security_question'] ?? $security_question;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | TravelGO Orbit</title>
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
        .progress-step {
            background: rgba(255, 255, 255, 0.1);
        }
        .progress-step.active {
            background: #f59e0b;
            color: #1e3a8a;
        }
        .progress-step.completed {
            background: #10b981;
            color: white;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <!-- Forgot Password Card -->
    <div class="login-card rounded-xl p-8 w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center text-white mb-4">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Home
            </a>
            <h2 class="text-3xl font-bold text-white mb-2">Reset Your Password</h2>
            <p class="text-blue-200">Follow the steps to reset your password</p>
        </div>

        <!-- Progress Indicator -->
        <div class="flex justify-between items-center mb-8">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="flex items-center">
                    <div class="progress-step w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold <?= $i == $step ? 'active' : ($i < $step ? 'completed' : '') ?>">
                        <?= $i < $step ? '<i data-feather="check"></i>' : $i ?>
                    </div>
                    <?php if ($i < 3): ?>
                        <div class="w-8 h-1 <?= $i < $step ? 'bg-green-500' : 'bg-gray-600' ?> mx-2"></div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="<?= $message_type === 'error' ? 'bg-red-900 border border-red-700' : ($message_type === 'success' ? 'bg-green-900 border border-green-700' : 'bg-blue-900 border border-blue-700') ?> text-white px-4 py-3 rounded-lg mb-4">
                <?= $message ?>
                <?php if ($message_type === 'success'): ?>
                    <div class="text-sm mt-2">Redirecting to login page...</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Email Verification -->
        <?php if ($step == 1): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="verify_email">

                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-1">Email Address</label>
                    <div class="relative">
                        <i data-feather="mail" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="email" id="email" name="email" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 placeholder-gray-500"
                            placeholder="Enter your email address"
                            value="<?= htmlspecialchars($email) ?>">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors mt-4">
                    Continue
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 2: Security Question -->
        <?php if ($step == 2): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="verify_security">

                <div class="bg-blue-900 border border-blue-700 text-blue-200 px-4 py-3 rounded mb-4">
                    <strong>Email:</strong> <?= htmlspecialchars($email) ?>
                </div>

                <div>
                    <label class="block text-white text-sm font-medium mb-1">Security Question</label>
                    <p class="text-white bg-gray-800 p-3 rounded-lg mb-4"><?= htmlspecialchars($security_question) ?></p>
                    
                    <label for="security_answer" class="block text-white text-sm font-medium mb-1">Your Answer</label>
                    <div class="relative">
                        <i data-feather="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                        <input type="text" id="security_answer" name="security_answer" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 placeholder-gray-500"
                            placeholder="Enter your answer">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors mt-4">
                    Verify Answer
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 3: Reset Password -->
        <?php if ($step == 3): ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="reset_password">

                <div class="bg-blue-900 border border-blue-700 text-blue-200 px-4 py-3 rounded mb-4">
                    <strong>Email:</strong> <?= htmlspecialchars($email) ?>
                </div>

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
            <p class="text-blue-200">Remember your password?
                <a href="login.php" class="text-white font-medium hover:text-yellow-400 transition-colors">Sign In</a>
            </p>
        </div>

        <!-- Demo Notice -->
        <div class="mt-6 p-4 bg-yellow-900 border border-yellow-700 rounded-lg">
            <div class="flex items-start">
                <i data-feather="info" class="text-yellow-400 mr-3 mt-0.5 flex-shrink-0"></i>
                <div>
                    <h4 class="font-semibold text-yellow-300 text-sm">Demo Notice</h4>
                    <p class="text-yellow-200 text-xs mt-1">
                        <strong>Demo Credentials:</strong> For security questions, use these answers:<br>
                        • First pet: <strong>Buddy</strong><br>
                        • Birth city: <strong>Springfield</strong><br>
                        • Mother's maiden name: <strong>Johnson</strong><br>
                        • First school: <strong>Maple Elementary</strong><br>
                        • Favorite book: <strong>The Great Gatsby</strong><br>
                        • Childhood nickname: <strong>Ace</strong><br>
                        • Childhood friend: <strong>Mike</strong><br>
                        • Childhood street: <strong>Oak Street</strong><br>
                        • Dream job: <strong>Astronaut</strong><br>
                        • Teacher's last name: <strong>Davis</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
        
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form && form.querySelector('#new_password')) {
                const newPassword = document.getElementById('new_password');
                const confirmPassword = document.getElementById('confirm_password');
                
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
