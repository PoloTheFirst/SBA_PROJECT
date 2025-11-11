<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';
require 'TwoFAAuth.php';

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit();
}

$twoFA = new TwoFAAuth($pdo);

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['temp_user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    $code = $_POST['code'];
    
    if ($twoFA->verifyCode($user['secret_2fa'], $code)) {
        // Successful verification
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['is_2fa_enabled'] = $user['is_2fa_enabled'];
        
        // Cleanup temp session
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_2fa_email']);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
        }
        .card-dark {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="card-dark p-8 rounded-lg shadow-md max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4 text-center text-white">Two-Factor Verification</h2>
        <p class="text-gray-300 mb-6 text-center">Enter the 6-digit code from your authenticator app</p>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="verify">
            <div class="mb-4">
                <input type="text" name="code" required 
                       class="w-full px-3 py-3 border border-gray-600 rounded text-center text-2xl tracking-widest font-mono bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       maxlength="6" pattern="[0-9]{6}" placeholder="000000" autofocus>
            </div>
            <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 rounded transition-colors">
                Verify & Sign In
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="login.php" class="text-blue-400 hover:text-blue-300 text-sm">Back to login</a>
        </div>
    </div>
</body>
</html>