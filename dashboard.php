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

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user data directly from users table - FIXED: Changed from UPDATE to SELECT
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get all countries, states, and cities for dropdowns
$countries = $pdo->query("SELECT * FROM countries ORDER BY name")->fetchAll();
$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();

// Get user statistics
$bookingsCount = 0;
$wishlistCount = 0;
$unreadNotifications = 0;

try {
    // Get bookings count
    $bookingsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
    $bookingsStmt->execute([$_SESSION['user_id']]);
    $bookingsResult = $bookingsStmt->fetch();
    $bookingsCount = $bookingsResult ? $bookingsResult['count'] : 0;

    // Get wishlist count
    $wishlistStmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_wishlist WHERE user_id = ?");
    $wishlistStmt->execute([$_SESSION['user_id']]);
    $wishlistResult = $wishlistStmt->fetch();
    $wishlistCount = $wishlistResult ? $wishlistResult['count'] : 0;

    // Get unread notifications count
    $notifStmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $notifStmt->execute([$_SESSION['user_id']]);
    $notifResult = $notifStmt->fetch();
    $unreadNotifications = $notifResult ? $notifResult['count'] : 0;
} catch (Exception $e) {
    // Log error but don't break the page
    error_log("Dashboard stats error: " . $e->getMessage());
}

// Determine current view
$current_view = $_GET['view'] ?? 'dashboard';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token invalid. Please try again.";
        header("Location: dashboard.php?view=" . $current_view);
        exit();
    }

    switch ($action) {
        case 'toggle_2fa':
            $enable_2fa = $_POST['enable_2fa'] ?? 0;

            if ($enable_2fa == 1) {
                // Enable 2FA - show setup page
                $_SESSION['temp_user_id'] = $_SESSION['user_id'];
                $_SESSION['temp_2fa_email'] = $user['email'];

                // Generate 2FA secret
                require 'TwoFAAuth.php';
                $twoFA = new TwoFAAuth($pdo);
                $_SESSION['temp_2fa_secret'] = $twoFA->generateSecret();

                header("Location: 2fa_setup.php");
                exit();
            } else {
                // Disable 2FA - show confirmation
                $_SESSION['show_2fa_confirm'] = true;
            }
            break;

        case 'confirm_disable_2fa':
            // Actually disable 2FA
            $stmt = $pdo->prepare("UPDATE users SET is_2fa_enabled = 0, secret_2fa = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['success'] = "Two-factor authentication has been disabled.";
            $user['is_2fa_enabled'] = 0;
            unset($_SESSION['show_2fa_confirm']);
            break;

        case 'cancel_disable_2fa':
            // Reset the toggle to ON since user canceled
            $user['is_2fa_enabled'] = 1;
            unset($_SESSION['show_2fa_confirm']);
            break;

        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $_SESSION['error'] = "All password fields are required.";
            } elseif ($new_password !== $confirm_password) {
                $_SESSION['error'] = "New passwords do not match.";
            } elseif (strlen($new_password) < 8) {
                $_SESSION['error'] = "New password must be at least 8 characters long.";
            } elseif (!password_verify($current_password, $user['password_hash'])) {
                $_SESSION['error'] = "Current password is incorrect.";
            } else {
                // Show confirmation modal for password change
                $_SESSION['show_password_confirm'] = true;
                $_SESSION['new_password_hash'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            break;

        case 'confirm_change_password':
            // Actually change password
            $new_password_hash = $_SESSION['new_password_hash'] ?? '';
            if ($new_password_hash) {
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

                // Create notification
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message) 
                    VALUES (?, 'security', 'Password Changed', 'Your password was successfully changed.')
                ");
                $notifStmt->execute([$_SESSION['user_id']]);

                $_SESSION['success'] = "Password has been changed successfully.";
                unset($_SESSION['show_password_confirm']);
                unset($_SESSION['new_password_hash']);
            }
            break;

        case 'cancel_change_password':
            unset($_SESSION['show_password_confirm']);
            unset($_SESSION['new_password_hash']);
            break;

        case 'update_profile':
            $name = $_POST['name'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $city_id = $_POST['city_id'] ?? null;
            $postal_code = $_POST['postal_code'] ?? '';
            $date_of_birth = $_POST['date_of_birth'] ?? '';
            $gender = $_POST['gender'] ?? '';

            if (empty($name) || empty($email) || empty($username)) {
                $_SESSION['error'] = "Name, username and email are required.";
            } else {
                // Check if email is already taken by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $_SESSION['user_id']]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = "Email is already registered by another user.";
                } else {
                    // Check if username is already taken by another user
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                    $stmt->execute([$username, $_SESSION['user_id']]);
                    if ($stmt->fetch()) {
                        $_SESSION['error'] = "Username is already taken by another user.";
                    } else {
                        // Handle pending avatar update
                        $profile_picture_update = '';
                        if (!empty($_SESSION['pending_avatar'])) {
                            // Get current avatar path to delete old file
                            $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $current_avatar = $stmt->fetchColumn();

                            // Delete old avatar file if it exists and is not the same as new one
                            if ($current_avatar && $current_avatar !== $_SESSION['pending_avatar'] && file_exists($current_avatar)) {
                                unlink($current_avatar);
                            }

                            $profile_picture_update = ', profile_picture = ?';
                        }

                        // Update user - including username and potentially profile picture
                        $sql = "
                    UPDATE users SET 
                    name = ?, username = ?, email = ?, phone = ?, address = ?, 
                    city_id = ?, postal_code = ?, date_of_birth = ?, gender = ?
                    $profile_picture_update
                    WHERE id = ?
                ";

                        $params = [
                            $name,
                            $username,
                            $email,
                            $phone,
                            $address,
                            $city_id,
                            $postal_code,
                            $date_of_birth,
                            $gender,
                        ];

                        if (!empty($_SESSION['pending_avatar'])) {
                            $params[] = $_SESSION['pending_avatar'];
                            $success_message = "Profile and avatar updated successfully!";
                        } else {
                            $success_message = "Profile updated successfully.";
                        }

                        $params[] = $_SESSION['user_id'];

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);

                        // Clear pending avatar after successful update
                        if (!empty($_SESSION['pending_avatar'])) {
                            unset($_SESSION['pending_avatar']);
                        }

                        // Create notification
                        $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message) 
                    VALUES (?, 'profile', 'Profile Updated', 'Your profile information was updated.')
                ");
                        $notifStmt->execute([$_SESSION['user_id']]);

                        $_SESSION['success'] = $success_message;
                        $_SESSION['name'] = $name;
                        $_SESSION['username'] = $username; // Update session username
                        $_SESSION['email'] = $email;

                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                    }
                }
            }
            break;

        case 'add_to_wishlist':
            $destination = $_POST['destination'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $priority = $_POST['priority'] ?? 'medium';
            $target_date = $_POST['target_date'] ?? null;
            $estimated_budget = $_POST['estimated_budget'] ?? null;

            if (!empty($destination)) {
                $stmt = $pdo->prepare("
                    INSERT INTO user_wishlist (user_id, destination, notes, priority, target_date, estimated_budget) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $destination, $notes, $priority, $target_date, $estimated_budget]);
                $_SESSION['success'] = "Destination added to wishlist!";
                $wishlistCount++;
            }
            break;

        case 'remove_from_wishlist':
            $wishlist_id = $_POST['wishlist_id'] ?? 0;
            if ($wishlist_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM user_wishlist WHERE id = ? AND user_id = ?");
                $stmt->execute([$wishlist_id, $_SESSION['user_id']]);
                $_SESSION['success'] = "Destination removed from wishlist.";
                $wishlistCount = max(0, $wishlistCount - 1);
            }
            break;

        case 'mark_notification_read':
            $notification_id = $_POST['notification_id'] ?? 0;
            if ($notification_id > 0) {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
                $stmt->execute([$notification_id, $_SESSION['user_id']]);
                $_SESSION['success'] = "Notification marked as read.";
                $unreadNotifications = max(0, $unreadNotifications - 1);
            }
            break;

        case 'mark_all_notifications_read':
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['success'] = "All notifications marked as read.";
            $unreadNotifications = 0;
            break;

        case 'dismiss_notice':
            $_SESSION['email_verification_dismissed'] = true;
            break;

        // Add this to the switch($action) block
        case 'remove_avatar':
            // Get current avatar path
            $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $current_avatar = $stmt->fetchColumn();

            if ($current_avatar) {
                // Delete the file from server
                if (file_exists($current_avatar)) {
                    unlink($current_avatar);
                }

                // Update database
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);

                // Update session
                unset($_SESSION['profile_picture']);

                $_SESSION['success'] = "Avatar removed successfully.";
            }
            break;
    }

    // Regenerate CSRF token after POST
    unset($_SESSION['csrf_token']);

    // Redirect to avoid form resubmission
    header("Location: dashboard.php?view=" . $current_view);
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user wishlist
$wishlistStmt = $pdo->prepare("SELECT * FROM user_wishlist WHERE user_id = ? ORDER BY created_at DESC");
$wishlistStmt->execute([$_SESSION['user_id']]);
$wishlist = $wishlistStmt->fetchAll();

// Get user bookings
$bookingsStmt = $pdo->prepare("
    SELECT b.*, 
           COALESCE(rtf.flight_number, 'N/A') as flight_number,
           COALESCE(rtf.airline_name, 'Unknown Airline') as airline_name,
           COALESCE(rtf.origin, 'Unknown') as origin,
           COALESCE(rtf.destination, 'Unknown') as destination
    FROM bookings b 
    LEFT JOIN round_trip_flights rtf ON b.flight_id = rtf.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 10
");
$bookingsStmt->execute([$_SESSION['user_id']]);
$bookings = $bookingsStmt->fetchAll();

// Get notifications
$notificationsStmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$notificationsStmt->execute([$_SESSION['user_id']]);
$notifications = $notificationsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TravelGO Orbit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
        }

        .sidebar {
            transition: all 0.3s;
            background: #1f2937;
        }

        .sidebar-item.active {
            background-color: #374151;
            color: #f59e0b;
        }

        .card-dark {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-overlay {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
    </style>
</head>

<body>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-gray-800 text-white shadow-lg">
            <div class="p-4 flex items-center space-x-2 border-b border-gray-700">
                <a href="index.php" class="flex items-center space-x-2">
                    <i data-feather="navigation" class="text-yellow-400"></i>
                    <span class="text-xl font-bold">TravelGO Orbit</span>
                </a>
            </div>
            <nav class="p-4 space-y-2">
                <a href="dashboard.php?view=dashboard" class="sidebar-item <?= $current_view === 'dashboard' ? 'active' : '' ?> flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-feather="home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="dashboard.php?view=bookings" class="sidebar-item <?= $current_view === 'bookings' ? 'active' : '' ?> flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-feather="briefcase"></i>
                    <span>My Bookings (<?= $bookingsCount ?>)</span>
                </a>
                <a href="dashboard.php?view=wishlist" class="sidebar-item <?= $current_view === 'wishlist' ? 'active' : '' ?> flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-feather="heart"></i>
                    <span>Wishlist (<?= $wishlistCount ?>)</span>
                </a>
                <a href="dashboard.php?view=notifications" class="sidebar-item <?= $current_view === 'notifications' ? 'active' : '' ?> flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-feather="bell"></i>
                    <span>Notifications (<?= $unreadNotifications ?>)</span>
                </a>
                <a href="dashboard.php?view=profile" class="sidebar-item <?= $current_view === 'profile' ? 'active' : '' ?> flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-feather="user"></i>
                    <span>Profile</span>
                </a>
                <a href="dashboard.php?view=security" class="sidebar-item <?= $current_view === 'security' ? 'active' : '' ?> flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-feather="shield"></i>
                    <span>Security (2FA: <?= $user['is_2fa_enabled'] ? 'ON' : 'OFF' ?>)</span>
                </a>
                <form method="POST" action="logout.php" class="sidebar-item">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" class="flex items-center space-x-2 p-3 rounded-lg hover:bg-gray-700 transition-colors w-full text-left">
                        <i data-feather="log-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto bg-gray-900">
            <!-- Header -->
            <header class="bg-gray-800 shadow-sm p-4 flex justify-between items-center">
                <div class="flex items-center">
                    <!-- Go Back Button -->
                    <a href="index.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors inline-flex items-center shadow-lg border-2 border-red-800 mr-4">
                        <i data-feather="home" class="w-4 h-4 mr-2"></i>
                        Go Back to Site
                    </a>

                    <h1 class="text-2xl font-bold text-white">
                        <?php
                        switch ($current_view) {
                            case 'dashboard':
                                echo 'Welcome back, ' . htmlspecialchars($user['name']);
                                break;
                            case 'profile':
                                echo 'Profile Information';
                                break;
                            case 'security':
                                echo 'Security Settings';
                                break;
                            case 'bookings':
                                echo 'My Bookings';
                                break;
                            case 'wishlist':
                                echo 'My Wishlist';
                                break;
                            case 'notifications':
                                echo 'Notifications';
                                break;
                            default:
                                echo 'Dashboard';
                        }
                        ?>
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Quick Stats -->
                    <div class="hidden md:flex items-center space-x-4 text-sm text-gray-300">
                        <span><?= $bookingsCount ?> Bookings</span>
                        <span>•</span>
                        <span><?= $wishlistCount ?> Wishlist</span>
                        <span>•</span>
                        <span><?= $unreadNotifications ?> Unread</span>
                    </div>

                    <!-- User Info -->
                    <div class="flex items-center space-x-2">
                        <?php
                        // Use the actual profile picture from database if available
                        $profile_pic = !empty($user['profile_picture']) ?
                            htmlspecialchars($user['profile_picture']) . '?t=' . time() :
                            "https://ui-avatars.com/api/?name=" . urlencode($user['name'] ?? 'User') . "&background=f59e0b&color=1e3a8a&size=64";
                        ?>
                        <img src="<?= $profile_pic ?>" alt="User" class="w-8 h-8 rounded-full object-cover border-2 border-yellow-500">
                        <span class="profile-name">@<?= htmlspecialchars($_SESSION['username'] ?? $user['username'] ?? 'user') ?></span>
                    </div>
                </div>
            </header>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 mx-4 mt-4 rounded">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 mx-4 mt-4 rounded">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Email Verification Banner -->
            <?php if (isset($_SESSION['user_id']) && !$user['email_verified'] && !isset($_SESSION['email_verification_dismissed'])): ?>
                <div class="bg-gradient-to-r from-green-900 to-green-800 border-l-4 border-green-500 text-white p-4 mx-4 mt-4 rounded shadow-lg">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <i data-feather="mail" class="w-6 h-6 text-green-300 mr-3"></i>
                            <div>
                                <h3 class="font-semibold">Verify Your Email Address</h3>
                                <p class="text-green-200 text-sm">Please verify your email to complete account activation and access all features.</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="email_verification.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                Verify Email
                            </a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="dismiss_notice">
                                <button type="submit" class="text-green-300 hover:text-green-100 p-1 rounded">
                                    <i data-feather="x" class="w-5 h-5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Content -->
            <main class="p-6">
                <?php if ($current_view === 'dashboard'): ?>
                    <!-- DASHBOARD VIEW -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Stats Card 1 -->
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-300">Upcoming Trips</p>
                                    <h3 class="text-2xl font-bold text-white"><?= $bookingsCount ?></h3>
                                </div>
                                <div class="p-3 rounded-full bg-blue-900 text-blue-300">
                                    <i data-feather="calendar"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Card 2 -->
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-300">Wishlist Items</p>
                                    <h3 class="text-2xl font-bold text-white"><?= $wishlistCount ?></h3>
                                </div>
                                <div class="p-3 rounded-full bg-green-900 text-green-300">
                                    <i data-feather="heart"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Card 3 -->
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-300">Unread Notifications</p>
                                    <h3 class="text-2xl font-bold text-white"><?= $unreadNotifications ?></h3>
                                </div>
                                <div class="p-3 rounded-full bg-yellow-900 text-yellow-300">
                                    <i data-feather="bell"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card-dark p-6 rounded-xl shadow-sm mb-6">
                        <h2 class="text-xl font-bold text-white mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="index.php" class="bg-blue-900 hover:bg-blue-800 p-4 rounded-lg text-center transition-colors">
                                <i data-feather="plane" class="w-8 h-8 text-blue-300 mx-auto mb-2"></i>
                                <span class="font-medium text-white">Book Flights</span>
                            </a>
                            <a href="dashboard.php?view=wishlist" class="bg-green-900 hover:bg-green-800 p-4 rounded-lg text-center transition-colors">
                                <i data-feather="heart" class="w-8 h-8 text-green-300 mx-auto mb-2"></i>
                                <span class="font-medium text-white">My Wishlist</span>
                            </a>
                            <a href="dashboard.php?view=profile" class="bg-purple-900 hover:bg-purple-800 p-4 rounded-lg text-center transition-colors">
                                <i data-feather="user" class="w-8 h-8 text-purple-300 mx-auto mb-2"></i>
                                <span class="font-medium text-white">My Profile</span>
                            </a>
                            <a href="dashboard.php?view=security" class="bg-orange-900 hover:bg-orange-800 p-4 rounded-lg text-center transition-colors">
                                <i data-feather="shield" class="w-8 h-8 text-orange-300 mx-auto mb-2"></i>
                                <span class="font-medium text-white">Security</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Bookings -->
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <h2 class="text-xl font-bold text-white mb-4">Recent Bookings</h2>
                            <?php if (empty($bookings)): ?>
                                <p class="text-gray-400 text-center py-4">No bookings yet. <a href="index.php" class="text-blue-400 hover:text-blue-300">Book your first flight!</a></p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($bookings, 0, 3) as $booking): ?>
                                        <div class="flex justify-between items-center p-4 bg-gray-800 rounded-lg">
                                            <div>
                                                <h3 class="font-semibold text-white">Booking #<?= htmlspecialchars($booking['booking_reference']) ?></h3>
                                                <p class="text-sm text-gray-400"><?= $booking['airline_name'] ?> • <?= htmlspecialchars($booking['origin']) ?> to <?= htmlspecialchars($booking['destination']) ?></p>
                                                <p class="text-sm text-gray-400">Status: <?= ucfirst($booking['status']) ?></p>
                                            </div>
                                            <span class="px-3 py-1 bg-blue-900 text-blue-300 rounded-full text-sm">
                                                HKD $<?= number_format($booking['total_amount'], 2) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($bookings) > 3): ?>
                                    <div class="mt-4 text-center">
                                        <a href="dashboard.php?view=bookings" class="text-blue-400 hover:text-blue-300 text-sm">View all bookings</a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Recent Notifications -->
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <h2 class="text-xl font-bold text-white mb-4">Recent Notifications</h2>
                            <?php if (empty($notifications)): ?>
                                <p class="text-gray-400 text-center py-4">No notifications</p>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                                        <div class="p-3 bg-gray-800 rounded-lg border-l-2 <?= $notification['is_read'] ? 'border-gray-600' : 'border-yellow-500' ?>">
                                            <p class="text-sm text-white"><?= htmlspecialchars($notification['title']) ?></p>
                                            <p class="text-xs text-gray-400 mt-1"><?= date('M j, g:i A', strtotime($notification['created_at'])) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($notifications) > 3): ?>
                                    <div class="mt-4 text-center">
                                        <a href="dashboard.php?view=notifications" class="text-blue-400 hover:text-blue-300 text-sm">View all notifications</a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($current_view === 'profile'): ?>
                    <!-- PROFILE VIEW -->
                    <div class="w-full">
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <h2 class="text-2xl font-bold text-white mb-6">Profile Information</h2>

                            <!-- Enhanced Profile Picture Section -->
                            <div class="mb-8">
                                <h3 class="text-xl font-semibold text-white mb-4">Profile Picture</h3>
                                <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                                    <div class="relative">
                                        <img id="profile-preview"
                                            src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) . '?t=' . time() : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=f59e0b&color=1e3a8a&size=150' ?>"
                                            alt="Profile Picture"
                                            class="w-32 h-32 rounded-full object-cover border-4 border-yellow-500 shadow-lg">
                                        <div id="upload-loading" class="hidden absolute inset-0 bg-gray-900 bg-opacity-70 rounded-full flex items-center justify-center">
                                            <i data-feather="loader" class="w-8 h-8 text-yellow-400 animate-spin"></i>
                                        </div>
                                    </div>
                                    <div class="text-center md:text-left">
                                        <div class="flex flex-col sm:flex-row gap-3 mb-3">
                                            <form id="avatar-form" enctype="multipart/form-data" class="sm:flex-1">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="file" name="avatar" accept="image/*" class="hidden" id="avatar-input">
                                                <button type="button" onclick="document.getElementById('avatar-input').click()"
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors w-full shadow-md hover:shadow-lg flex items-center justify-center h-12 whitespace-nowrap">
                                                    <i data-feather="edit-3" class="w-4 h-4 mr-2"></i>
                                                    <span>Upload Avatar</span>
                                                </button>
                                            </form>

                                            <?php if (!empty($user['profile_picture'])): ?>
                                                <form method="POST" id="remove-avatar-form" class="sm:flex-1">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="action" value="remove_avatar">
                                                    <button type="button" onclick="removeAvatar()"
                                                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors w-full shadow-md hover:shadow-lg flex items-center justify-center h-12 whitespace-nowrap">
                                                        <i data-feather="trash-2" class="w-4 h-4 mr-2"></i>
                                                        <span>Remove Avatar</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Hidden remove avatar form that will be shown after upload -->
                                                <form method="POST" id="remove-avatar-form" class="sm:flex-1 hidden">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="action" value="remove_avatar">
                                                    <button type="button" onclick="removeAvatar()"
                                                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-colors w-full shadow-md hover:shadow-lg flex items-center justify-center h-12 whitespace-nowrap">
                                                        <i data-feather="trash-2" class="w-4 h-4 mr-2"></i>
                                                        <span>Remove Avatar</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>

                                        <p class="text-gray-400 text-sm max-w-xs">
                                            <i data-feather="info" class="w-4 h-4 inline mr-1"></i>
                                            JPG, PNG, GIF or WebP. Max 5MB.
                                            <br>Click edit to crop, rotate, and flip your image.
                                        </p>
                                        <div id="upload-message" class="text-sm mt-2 hidden"></div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="update_profile">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Full Name *</label>
                                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            required>
                                    </div>

                                    <div>
                                        <label for="username" class="block text-gray-300 text-sm font-medium mb-2">Username *</label>
                                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            placeholder="Your username"
                                            pattern="[a-zA-Z0-9_]{3,20}"
                                            title="3-20 characters, letters, numbers, and underscores only">
                                        <div id="username-availability" class="text-xs mt-1"></div>
                                    </div>

                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Email *</label>
                                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Phone</label>
                                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Date of Birth</label>
                                        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>

                                    <!-- Country Dropdown -->
                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Country</label>
                                        <select name="country_id" id="country"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            <option value="">Select Country</option>
                                            <?php foreach ($countries as $country): ?>
                                                <option value="<?= $country['id'] ?>"
                                                    <?= ($user['country_id'] ?? '') == $country['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($country['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- City Dropdown -->
                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">City</label>
                                        <select name="city_id" id="city"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            <option value="">Select City</option>
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?= $city['id'] ?>"
                                                    <?= ($user['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($city['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Address</label>
                                        <textarea name="address"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Postal Code</label>
                                        <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Gender</label>
                                        <select name="gender"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            <option value="">Select Gender</option>
                                            <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-8 pt-6 border-t border-gray-700">
                                    <button type="submit"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded-lg transition-colors">
                                        Update Profile
                                    </button>
                                    <a href="dashboard.php?view=dashboard"
                                        class="ml-4 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php elseif ($current_view === 'security'): ?>
                    <!-- SECURITY VIEW -->
                    <div class="w-full max-w-4xl">
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <h2 class="text-2xl font-bold text-white mb-6">Security Settings</h2>

                            <!-- 2FA Section -->
                            <div class="mb-8">
                                <h3 class="text-xl font-semibold text-white mb-4">Two-Factor Authentication</h3>
                                <div class="bg-gray-800 rounded-lg p-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-white">2FA Status:
                                                <span class="<?= $user['is_2fa_enabled'] ? 'text-green-400' : 'text-yellow-400' ?>">
                                                    <?= $user['is_2fa_enabled'] ? 'Enabled' : 'Disabled' ?>
                                                </span>
                                            </h4>
                                            <p class="text-gray-400 text-sm mt-1">
                                                <?= $user['is_2fa_enabled']
                                                    ? 'Your account is protected with two-factor authentication.'
                                                    : 'Add an extra layer of security to your account.'
                                                ?>
                                            </p>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="action" value="toggle_2fa">
                                            <input type="hidden" name="enable_2fa" value="<?= $user['is_2fa_enabled'] ? '0' : '1' ?>">
                                            <button type="submit"
                                                class="bg-<?= $user['is_2fa_enabled'] ? 'red' : 'green' ?>-600 hover:bg-<?= $user['is_2fa_enabled'] ? 'red' : 'green' ?>-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                                <?= $user['is_2fa_enabled'] ? 'Disable' : 'Enable' ?> 2FA
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <div>
                                <h3 class="text-xl font-semibold text-white mb-4">Change Password</h3>
                                <div class="bg-gray-800 rounded-lg p-6">
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="change_password">

                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-gray-300 text-sm font-medium mb-2">Current Password</label>
                                                <input type="password" name="current_password" required
                                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-gray-300 text-sm font-medium mb-2">New Password</label>
                                                <input type="password" name="new_password" required minlength="8"
                                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-gray-300 text-sm font-medium mb-2">Confirm New Password</label>
                                                <input type="password" name="confirm_password" required minlength="8"
                                                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            </div>
                                            <div>
                                                <button type="submit"
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-2 px-6 rounded transition-colors">
                                                    Change Password
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($current_view === 'bookings'): ?>
                    <!-- BOOKINGS VIEW -->
                    <div class="w-full">
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <h2 class="text-2xl font-bold text-white mb-6">My Bookings</h2>

                            <?php if (empty($bookings)): ?>
                                <div class="text-center py-8">
                                    <i data-feather="package" class="w-16 h-16 text-gray-500 mx-auto mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-400 mb-2">No bookings yet</h3>
                                    <p class="text-gray-500 mb-4">Start planning your next adventure!</p>
                                    <a href="index.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-2 px-6 rounded-lg transition-colors inline-flex items-center">
                                        <i data-feather="search" class="w-4 h-4 mr-2"></i>
                                        Search Flights
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($bookings as $booking): ?>
                                        <div class="bg-gray-800 rounded-lg p-6 border-l-4 
                                            <?= $booking['status'] === 'confirmed' ? 'border-green-500' : ($booking['status'] === 'pending' ? 'border-yellow-500' : 'border-red-500') ?>">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-4 mb-3">
                                                        <h3 class="text-lg font-semibold text-white">
                                                            Booking #<?= htmlspecialchars($booking['booking_reference']) ?>
                                                        </h3>
                                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                                            <?= $booking['status'] === 'confirmed' ? 'bg-green-900 text-green-300' : ($booking['status'] === 'pending' ? 'bg-yellow-900 text-yellow-300' : 'bg-red-900 text-red-300') ?>">
                                                            <?= ucfirst($booking['status']) ?>
                                                        </span>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                                        <div>
                                                            <p class="text-gray-400">Airline</p>
                                                            <p class="text-white font-medium"><?= $booking['airline_name'] ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-400">Flight</p>
                                                            <p class="text-white font-medium"><?= $booking['flight_number'] ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-400">Route</p>
                                                            <p class="text-white font-medium"><?= htmlspecialchars($booking['origin']) ?> → <?= htmlspecialchars($booking['destination']) ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-400">Amount</p>
                                                            <p class="text-white font-medium">HKD $<?= number_format($booking['total_amount'], 2) ?></p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 text-xs text-gray-500">
                                                        Booked on <?= date('M j, Y g:i A', strtotime($booking['created_at'])) ?>
                                                    </div>
                                                </div>

                                                <div class="flex space-x-2">
                                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                                        View Details
                                                    </button>
                                                    <?php if ($booking['status'] === 'pending'): ?>
                                                        <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                                            Cancel
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($current_view === 'wishlist'): ?>
                    <!-- WISHLIST VIEW -->
                    <div class="w-full">
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <h2 class="text-2xl font-bold text-white mb-6">My Wishlist</h2>

                            <!-- Add to Wishlist Form -->
                            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-white mb-4">Add New Destination</h3>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="add_to_wishlist">

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-gray-300 text-sm font-medium mb-2">Destination *</label>
                                            <input type="text" name="destination" required
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                                placeholder="e.g., Tokyo, Japan">
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 text-sm font-medium mb-2">Priority</label>
                                            <select name="priority"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 text-sm font-medium mb-2">Target Date</label>
                                            <input type="date" name="target_date"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 text-sm font-medium mb-2">Budget (HKD)</label>
                                            <input type="number" name="estimated_budget" step="0.01"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block text-gray-300 text-sm font-medium mb-2">Notes</label>
                                        <textarea name="notes" rows="2"
                                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            placeholder="Any specific details or preferences..."></textarea>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit"
                                            class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-2 px-6 rounded transition-colors">
                                            Add to Wishlist
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Wishlist Items -->
                            <?php if (empty($wishlist)): ?>
                                <div class="text-center py-8">
                                    <i data-feather="heart" class="w-16 h-16 text-gray-500 mx-auto mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-400 mb-2">Your wishlist is empty</h3>
                                    <p class="text-gray-500">Start adding destinations you'd love to visit!</p>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($wishlist as $item): ?>
                                        <div class="bg-gray-800 rounded-lg p-6 border-l-4 
                                            <?= $item['priority'] === 'high' ? 'border-red-500' : ($item['priority'] === 'medium' ? 'border-yellow-500' : 'border-blue-500') ?>">
                                            <div class="flex justify-between items-start mb-3">
                                                <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($item['destination']) ?></h3>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="action" value="remove_from_wishlist">
                                                    <input type="hidden" name="wishlist_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="text-gray-400 hover:text-red-400 transition-colors">
                                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <?php if (!empty($item['notes'])): ?>
                                                <p class="text-gray-300 text-sm mb-3"><?= htmlspecialchars($item['notes']) ?></p>
                                            <?php endif; ?>

                                            <div class="space-y-2 text-sm">
                                                <?php if ($item['target_date']): ?>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400">Target Date:</span>
                                                        <span class="text-white"><?= date('M j, Y', strtotime($item['target_date'])) ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($item['estimated_budget']): ?>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400">Estimated Budget:</span>
                                                        <span class="text-white">HKD $<?= number_format($item['estimated_budget'], 2) ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="flex justify-between">
                                                    <span class="text-gray-400">Priority:</span>
                                                    <span class="text-white capitalize"><?= $item['priority'] ?></span>
                                                </div>
                                            </div>

                                            <div class="mt-4 pt-3 border-t border-gray-700">
                                                <a href="index.php?search=<?= urlencode($item['destination']) ?>"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors inline-flex items-center">
                                                    <i data-feather="search" class="w-3 h-3 mr-1"></i>
                                                    Search Flights
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($current_view === 'notifications'): ?>
                    <!-- NOTIFICATIONS VIEW -->
                    <div class="w-full">
                        <div class="card-dark p-6 rounded-xl shadow-sm">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-2xl font-bold text-white">Notifications</h2>
                                <?php if ($unreadNotifications > 0): ?>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="mark_all_notifications_read">
                                        <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors inline-flex items-center">
                                            <i data-feather="check" class="w-4 h-4 mr-2"></i>
                                            Mark All as Read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($notifications)): ?>
                                <div class="text-center py-8">
                                    <i data-feather="bell" class="w-16 h-16 text-gray-500 mx-auto mb-4"></i>
                                    <h3 class="text-xl font-semibold text-gray-400 mb-2">No notifications</h3>
                                    <p class="text-gray-500">You're all caught up!</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="bg-gray-800 rounded-lg p-4 border-l-4 <?= $notification['is_read'] ? 'border-gray-600' : 'border-yellow-500' ?>">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h3 class="font-semibold text-white mb-1"><?= htmlspecialchars($notification['title']) ?></h3>
                                                    <p class="text-gray-300 text-sm mb-2"><?= htmlspecialchars($notification['message']) ?></p>
                                                    <div class="flex items-center text-xs text-gray-500">
                                                        <i data-feather="clock" class="w-3 h-3 mr-1"></i>
                                                        <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                                                        <span class="mx-2">•</span>
                                                        <span class="capitalize"><?= $notification['type'] ?></span>
                                                    </div>
                                                </div>

                                                <?php if (!$notification['is_read']): ?>
                                                    <form method="POST" class="ml-4">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <input type="hidden" name="action" value="mark_notification_read">
                                                        <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                        <button type="submit"
                                                            class="text-gray-400 hover:text-white transition-colors"
                                                            title="Mark as read">
                                                            <i data-feather="check" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($notification['action_url']): ?>
                                                <div class="mt-3">
                                                    <a href="<?= htmlspecialchars($notification['action_url']) ?>"
                                                        class="text-blue-400 hover:text-blue-300 text-sm inline-flex items-center">
                                                        View Details
                                                        <i data-feather="arrow-right" class="w-3 h-3 ml-1"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- 2FA Confirmation Modal -->
    <?php if (isset($_SESSION['show_2fa_confirm'])): ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal-overlay">
            <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Disable Two-Factor Authentication?</h3>
                <p class="text-gray-300 mb-6">Are you sure you want to disable 2FA? This will reduce the security of your account.</p>
                <div class="flex justify-end space-x-4">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="cancel_disable_2fa">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors">
                            Cancel
                        </button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="confirm_disable_2fa">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors">
                            Disable 2FA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Password Change Confirmation Modal -->
    <?php if (isset($_SESSION['show_password_confirm'])): ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 modal-overlay">
            <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-white mb-4">Confirm Password Change</h3>
                <p class="text-gray-300 mb-6">Are you sure you want to change your password? You will need to use your new password for future logins.</p>
                <div class="flex justify-end space-x-4">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="cancel_change_password">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors">
                            Cancel
                        </button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="confirm_change_password">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition-colors">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        feather.replace();

        // Location dropdown functionality
        document.getElementById('country')?.addEventListener('change', function() {
            const countryId = this.value;
            const citySelect = document.getElementById('city');

            if (countryId) {
                // Enable city dropdown
                citySelect.disabled = false;
            } else {
                citySelect.disabled = true;
                citySelect.value = '';
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                // You could add logic to close modals here if needed
            }
        });

        // Avatar upload functionality
        let currentAvatarFile = null;
        let cropper = null;
        let currentRotation = 0;
        let currentScaleX = 1;
        let currentScaleY = 1;

        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showUploadMessage('Please select a valid image file (JPEG, PNG, GIF, WebP).', 'error');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showUploadMessage('File size must be less than 5MB.', 'error');
                return;
            }

            // Store the file and open editor
            currentAvatarFile = file;
            openImageEditor(file);
        });

        function openImageEditor(file) {
            // Create editor modal
            const editorHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" id="avatar-editor-modal">
            <div class="bg-gray-800 rounded-lg p-6 max-w-2xl w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Edit Profile Picture</h3>
                    <button type="button" onclick="closeImageEditor()" class="text-gray-400 hover:text-white">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1">
                        <div class="bg-gray-900 rounded-lg p-4 mb-4">
                            <img id="editor-image" class="max-w-full max-h-64 mx-auto" alt="Image to edit">
                        </div>
                        
                        <!-- Three Button Controls in Correct Order -->
                        <div class="flex justify-center space-x-4">
                            <!-- Rotate Clockwise 90° -->
                            <button type="button" onclick="rotateImage(90)" class="p-4 bg-gray-700 hover:bg-gray-600 rounded-lg text-white transition-colors group flex flex-col items-center w-28" title="Rotate Clockwise 90°">
                                <i data-feather="rotate-cw" class="w-6 h-6 mb-2 group-hover:text-yellow-400"></i>
                                <span class="text-xs text-gray-300 group-hover:text-white text-center">Rotate Clockwise</span>
                            </button>
                            
                            <!-- Flip Horizontally -->
                            <button type="button" onclick="flipImage('horizontal')" class="p-4 bg-gray-700 hover:bg-gray-600 rounded-lg text-white transition-colors group flex flex-col items-center w-28" title="Flip Horizontally">
                                <i data-feather="columns" class="w-6 h-6 mb-2 group-hover:text-yellow-400"></i>
                                <span class="text-xs text-gray-300 group-hover:text-white text-center">Flip Horizontally</span>
                            </button>
                            
                            <!-- Flip Vertically -->
                            <button type="button" onclick="flipImage('vertical')" class="p-4 bg-gray-700 hover:bg-gray-600 rounded-lg text-white transition-colors group flex flex-col items-center w-28" title="Flip Vertically">
                                <i data-feather="columns" class="w-6 h-6 mb-2 group-hover:text-yellow-400 transform rotate-90"></i>
                                <span class="text-xs text-gray-300 group-hover:text-white text-center">Flip<br>Vertically</span>
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-gray-400 text-sm">
                                <i data-feather="mouse-pointer" class="w-4 h-4 inline mr-1"></i>
                                Drag to reposition • Scroll to zoom
                            </p>
                        </div>
                    </div>
                    
                    <div class="lg:w-48">
                        <div class="text-center mb-4">
                            <h4 class="text-white font-semibold mb-2">Preview</h4>
                            <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-yellow-500 bg-gray-700">
                                <img id="preview-image" class="w-full h-full object-cover">
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <button type="button" onclick="saveEditedImage()" class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center h-12">
                                <i data-feather="check" class="w-5 h-5 mr-2"></i>
                                Save
                            </button>
                            <button type="button" onclick="closeImageEditor()" class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center h-12">
                                <i data-feather="x" class="w-5 h-5 mr-2"></i>
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            document.body.insertAdjacentHTML('beforeend', editorHTML);
            feather.replace();

            // Reset transformation state
            currentRotation = 0;
            currentScaleX = 1;
            currentScaleY = 1;

            // Initialize image and cropper
            const reader = new FileReader();
            reader.onload = function(e) {
                const image = document.getElementById('editor-image');
                const preview = document.getElementById('preview-image');

                image.src = e.target.result;
                preview.src = e.target.result;

                // Initialize Cropper.js
                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    guides: false,
                    background: false,
                    autoCropArea: 0.8,
                    ready() {
                        updatePreview();
                    },
                    crop() {
                        updatePreview();
                    }
                });
            };
            reader.readAsDataURL(file);
        }

        function closeImageEditor() {
            const modal = document.getElementById('avatar-editor-modal');
            if (modal) {
                modal.remove();
            }
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            currentAvatarFile = null;
            currentRotation = 0;
            currentScaleX = 1;
            currentScaleY = 1;

            // Reset file input
            document.getElementById('avatar-input').value = '';
        }

        function rotateImage(degrees) {
            if (cropper) {
                currentRotation += degrees;
                cropper.rotateTo(currentRotation);
                updatePreview();
            }
        }

        function flipImage(direction) {
            if (cropper) {
                if (direction === 'horizontal') {
                    currentScaleX *= -1;
                    cropper.scaleX(currentScaleX);
                } else {
                    currentScaleY *= -1;
                    cropper.scaleY(currentScaleY);
                }
                updatePreview();
            }
        }

        function updatePreview() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 150,
                    height: 150,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                const preview = document.getElementById('preview-image');
                preview.src = canvas.toDataURL();
            }
        }

        function saveEditedImage() {
            if (!currentAvatarFile) return;

            if (cropper) {
                // Get cropped and edited image
                const canvas = cropper.getCroppedCanvas({
                    width: 150,
                    height: 150,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                canvas.toBlob(function(blob) {
                    uploadAvatar(blob);
                }, 'image/jpeg', 0.9);
            } else {
                // Fallback: upload original file
                uploadAvatar(currentAvatarFile);
            }

            closeImageEditor();
        }

        function uploadAvatar(blob) {
            const formData = new FormData();
            formData.append('avatar', blob);
            formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
            formData.append('edited', 'true');

            const loadingElement = document.getElementById('upload-loading');
            const previewElement = document.getElementById('profile-preview');

            loadingElement.classList.remove('hidden');
            previewElement.classList.add('opacity-50');

            fetch('avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the preview immediately
                        previewElement.src = data.file_path;

                        // Show success message
                        showUploadMessage('Avatar updated successfully!', 'success');

                        // Show the Remove Avatar button if hidden
                        document.getElementById('remove-avatar-form').classList.remove('hidden');

                        // Update session storage to indicate avatar was updated
                        sessionStorage.setItem('avatarUpdated', 'true');
                        sessionStorage.setItem('avatarTimestamp', Date.now());

                        // Force page refresh to update all avatar displays including navigation
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showUploadMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showUploadMessage('Upload failed: ' + error.message, 'error');
                })
                .finally(() => {
                    loadingElement.classList.add('hidden');
                    previewElement.classList.remove('opacity-50');
                });
        }

        function removeAvatar() {
            if (confirm('Are you sure you want to remove your avatar? This will set it back to default.')) {
                // Submit the remove avatar form
                document.getElementById('remove-avatar-form').submit();
            }
        }

        function showUploadMessage(message, type, autoHide = true) {
            const messageElement = document.getElementById('upload-message');
            messageElement.textContent = message;
            messageElement.className = `text-sm mt-2 ${type === 'success' ? 'text-green-400' : 'text-red-400'}`;
            messageElement.classList.remove('hidden');

            if (autoHide && type !== 'success') {
                setTimeout(() => {
                    messageElement.classList.add('hidden');
                }, 5000);
            }
        }

        // Username availability check
        document.querySelector('input[name="username"]')?.addEventListener('blur', function() {
            const username = this.value;
            const availabilityDiv = document.getElementById('username-availability');

            if (username.length >= 3 && username !== '<?= $user['username'] ?>') {
                fetch(`checkusername.php?username=${encodeURIComponent(username)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            availabilityDiv.textContent = '✓ Username available';
                            availabilityDiv.className = 'text-xs text-green-400 mt-1';
                        } else {
                            availabilityDiv.textContent = '✗ Username already taken';
                            availabilityDiv.className = 'text-xs text-red-400 mt-1';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking username:', error);
                    });
            } else if (username === '<?= $user['username'] ?>') {
                availabilityDiv.textContent = '✓ Your current username';
                availabilityDiv.className = 'text-xs text-blue-400 mt-1';
            } else {
                availabilityDiv.textContent = '';
            }
        });
    </script>
</body>

</html>