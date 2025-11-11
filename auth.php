<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
// Check session status before starting
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files with error handling
if (!file_exists('connection.php')) {
    die("Error: connection.php file not found");
}
require 'connection.php';

if (!file_exists('TwoFAAuth.php')) {
    die("Error: TwoFAAuth.php file not found.");
}
require 'TwoFAAuth.php';

// Check if TwoFAAuth class exists
if (!class_exists('TwoFAAuth')) {
    die("Error: TwoFAAuth class not defined.");
}

try {
    $twoFA = new TwoFAAuth($pdo);
} catch (Exception $e) {
    die("Error initializing TwoFAAuth: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING); // Can be email or username
        $password = $_POST['password'];

        if (empty($login) || empty($password)) {
            header("Location: login.php?error=Login and password are required");
            exit();
        }

        try {
            // Search by both email and username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_2fa_enabled'] && $user['secret_2fa']) {
                    // Store temp session for 2FA verification
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_2fa_email'] = $user['email'];
                    header("Location: 2fa_verify.php");
                    exit();
                } else {
                    // No 2FA enabled, log in directly
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['username'] = $user['username'] ?? '';
                    $_SESSION['first_name'] = $user['first_name'] ?? '';
                    $_SESSION['last_name'] = $user['last_name'] ?? '';
                    $_SESSION['name'] = $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['is_2fa_enabled'] = $user['is_2fa_enabled'];
                    $_SESSION['email_verified'] = $user['email_verified'];
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                header("Location: login.php?error=Invalid login credentials");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            header("Location: login.php?error=Database error occurred");
            exit();
        }
    } elseif ($action === 'register') {
        // Enhanced registration with phone codes
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $phone_code = $_POST['phone_code'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $gender = $_POST['gender'] ?? null;
        $country_id = (int)$_POST['country_id'] ?? 0;
        $city_id = (int)$_POST['city_id'] ?? 0;
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        $postal_code = filter_input(INPUT_POST, 'postal_code', FILTER_SANITIZE_STRING);
        // newsletter column removed since it doesn't exist in the database

        // Combine phone number
        $full_phone = $phone_code . ' ' . $phone_number;
        $name = $first_name . ' ' . $last_name;

        // Validation
        $errors = [];

        if (
            empty($first_name) || empty($last_name) || empty($username) || empty($email) ||
            empty($password) || empty($confirm_password) || empty($phone_code) ||
            empty($phone_number) || empty($date_of_birth) || empty($country_id) ||
            empty($city_id) || empty($address) || empty($postal_code)
        ) {
            $errors[] = "All required fields must be filled";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }

        // Phone validation
        if (strlen($phone_number) < 7) {
            $errors[] = "Phone number is too short";
        }

        // Age validation
        $dob = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            $errors[] = "You must be at least 18 years old";
        }

        if (!isset($_POST['terms'])) {
            $errors[] = "You must accept the terms and conditions";
        }

        if (!empty($errors)) {
            header("Location: register.php?error=" . urlencode(implode(", ", $errors)));
            exit();
        }

        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                header("Location: register.php?error=Email already registered");
                exit();
            }

            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                header("Location: register.php?error=Username already taken");
                exit();
            }

            // Hash password and create user - FIXED: Consistent column names
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Improved state_id handling
            $state_id = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null;
            // FIXED: Include state_id in both column list and values
            $stmt = $pdo->prepare("
    INSERT INTO users (username, first_name, last_name, name, email, password_hash, phone, date_of_birth, gender, address, state_id, city_id, postal_code) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

            $stmt->execute([
                $username,
                $first_name,
                $last_name,
                $name,
                $email,
                $password_hash,
                $full_phone,
                $date_of_birth,
                $gender,
                $address,
                $state_id,     // ← Now state_id is included!
                $city_id,
                $postal_code
            ]);
            $user_id = $pdo->lastInsertId();

            // Generate 2FA secret for the new user
            $secret_2fa = $twoFA->generateSecret();

            // Store in temporary session for 2FA setup
            $_SESSION['temp_user_id'] = $user_id;
            $_SESSION['temp_2fa_email'] = $email;
            $_SESSION['temp_2fa_secret'] = $secret_2fa;

            // Redirect to 2FA setup page
            header("Location: 2fa_setup.php");
            exit();
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            header("Location: register.php?error=Database error occurred: " . $e->getMessage());
            exit();
        }
    }
}

// If not POST request, redirect to home
header("Location: index.php");
exit();
