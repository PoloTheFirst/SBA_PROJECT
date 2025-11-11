<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';
require 'TwoFAAuth.php';
require 'phpqrcode.php';

if (!isset($_SESSION['temp_user_id']) && !isset($_SESSION['temp_2fa_secret'])) {
    header("Location: register.php");
    exit();
}

$twoFA = new TwoFAAuth($pdo);
$secret = $_SESSION['temp_2fa_secret'];
$email = $_SESSION['temp_2fa_email'];

// Generate QR code URL for Google Authenticator using the existing function
$issuer = "TravelGO Orbit";
$qrContent = "otpauth://totp/" . urlencode($issuer) . ":" . urlencode($email) . "?secret=" . $secret . "&issuer=" . urlencode($issuer);
$qrCodeUrl = generateQRCode($qrContent);

// Handle 2FA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'verify_2fa') {
        $code = $_POST['code'];
        
        if ($twoFA->verifyCode($secret, $code)) {
            // Enable 2FA for user and store secret in database
            $stmt = $pdo->prepare("UPDATE users SET is_2fa_enabled = 1, secret_2fa = ? WHERE id = ?");
            $stmt->execute([$secret, $_SESSION['temp_user_id']]);
            
            // Get user data and set proper session
            $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $userStmt->execute([$_SESSION['temp_user_id']]);
            $user = $userStmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'] ?? '';
                $_SESSION['first_name'] = $user['first_name'] ?? '';
                $_SESSION['last_name'] = $user['last_name'] ?? '';
                $_SESSION['name'] = $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['is_2fa_enabled'] = 1;
                $_SESSION['email_verified'] = $user['email_verified'];
                
                // Cleanup temp session
                unset($_SESSION['temp_user_id']);
                unset($_SESSION['temp_2fa_email']);
                unset($_SESSION['temp_2fa_secret']);
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "User not found. Please try registering again.";
            }
        } else {
            $error = "Invalid verification code. Please try again.";
        }
    } elseif ($action === 'skip_2fa') {
        // Skip 2FA setup - log user in without 2FA
        $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['temp_user_id']]);
        $user = $userStmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['first_name'] = $user['first_name'] ?? '';
            $_SESSION['last_name'] = $user['last_name'] ?? '';
            $_SESSION['name'] = $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['is_2fa_enabled'] = 0;
            $_SESSION['email_verified'] = $user['email_verified'];
            
            // Cleanup temp session
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_2fa_email']);
            unset($_SESSION['temp_2fa_secret']);
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "User not found. Please try registering again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup 2FA | TravelGO Orbit</title>
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
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="card-dark p-8 rounded-lg shadow-md max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4 text-center text-white">Setup Two-Factor Authentication</h2>
        
        <div class="mb-6">
            <p class="text-gray-300 mb-4">Scan this QR code with Google Authenticator:</p>
            <img src="<?= $qrCodeUrl ?>" alt="QR Code" class="mx-auto border rounded-lg border-gray-600">
        </div>
        
        <div class="mb-6">
            <p class="text-sm text-gray-400 mb-2">Or manually enter this secret:</p>
            <div class="bg-gray-800 p-3 rounded text-center font-mono text-sm text-white">
                <?= chunk_split($secret, 4, ' ') ?>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="verify_2fa">
            <div class="mb-4">
                <label class="block text-white mb-2">Enter verification code:</label>
                <input type="text" name="code" required 
                       class="w-full px-3 py-3 border border-gray-600 rounded text-center text-2xl tracking-widest font-mono bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                       maxlength="6" pattern="[0-9]{6}" placeholder="000000" autofocus>
            </div>
            <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 rounded transition-colors mb-2">
                Verify & Complete Setup
            </button>
        </form>
        
        <form method="POST">
            <input type="hidden" name="action" value="skip_2fa">
            <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 rounded transition-colors">
                Skip for now (not recommended)
            </button>
        </form>
    </div>
</body>
</html>