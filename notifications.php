<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token invalid. Please try again.";
        header("Location: notifications.php");
        exit();
    }
    
    switch ($action) {
        case 'mark_all_read':
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['success'] = "All notifications marked as read";
            break;
            
        case 'mark_read':
            $notification_id = (int)$_POST['notification_id'] ?? 0;
            if ($notification_id > 0) {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
                $stmt->execute([$notification_id, $_SESSION['user_id']]);
                $_SESSION['success'] = "Notification marked as read";
            }
            break;
            
        case 'delete':
            $notification_id = (int)$_POST['notification_id'] ?? 0;
            if ($notification_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->execute([$notification_id, $_SESSION['user_id']]);
                $_SESSION['success'] = "Notification deleted";
            }
            break;
    }
    
    // Regenerate CSRF token
    unset($_SESSION['csrf_token']);
    header("Location: notifications.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get notifications with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_notifications = $stmt->fetch()['total'];
$total_pages = ceil($total_notifications / $limit);

// Get notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$_SESSION['user_id'], $limit, $offset]);
$notifications = $stmt->fetchAll();

// Get unread count for badge
$stmt = $pdo->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = FALSE");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetch()['unread'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | TravelGO Orbit</title>
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
<body class="min-h-screen bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <a href="index.php" class="flex items-center space-x-2">
                    <i data-feather="navigation" class="text-yellow-400"></i>
                    <span class="text-xl font-bold">TravelGO Orbit</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Back to Dashboard
                </a>
                <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="card-dark rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Notifications</h1>
                    <p class="text-gray-400">You have <?= $unread_count ?> unread notifications</p>
                </div>
                <?php if ($total_notifications > 0): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                        Mark All as Read
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Notifications List -->
        <div class="card-dark rounded-lg p-6">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-12">
                    <i data-feather="bell-off" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-xl font-bold text-white mb-2">No notifications</h3>
                    <p class="text-gray-400">You're all caught up! Check back later for new notifications.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="flex items-start justify-between p-4 bg-gray-800 rounded-lg border-l-4 <?= $notification['is_read'] ? 'border-gray-600' : 'border-yellow-500' ?>">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <?php
                                    $icon = 'bell';
                                    $color = 'text-gray-400';
                                    switch ($notification['type']) {
                                        case 'booking':
                                            $icon = 'briefcase';
                                            $color = 'text-blue-400';
                                            break;
                                        case 'security':
                                            $icon = 'shield';
                                            $color = 'text-green-400';
                                            break;
                                        case 'promotion':
                                            $icon = 'tag';
                                            $color = 'text-yellow-400';
                                            break;
                                        case 'system':
                                            $icon = 'settings';
                                            $color = 'text-purple-400';
                                            break;
                                    }
                                    ?>
                                    <i data-feather="<?= $icon ?>" class="w-4 h-4 <?= $color ?>"></i>
                                    <span class="text-sm text-gray-400 capitalize"><?= $notification['type'] ?></span>
                                    <?php if (!$notification['is_read']): ?>
                                        <span class="bg-yellow-500 text-yellow-900 text-xs px-2 py-1 rounded-full font-bold">New</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="font-semibold text-white mb-1"><?= htmlspecialchars($notification['title']) ?></h3>
                                <p class="text-gray-300 text-sm mb-2"><?= htmlspecialchars($notification['message']) ?></p>
                                <p class="text-gray-500 text-xs"><?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?></p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <?php if (!$notification['is_read']): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                        <button type="submit" class="text-blue-400 hover:text-blue-300 text-sm" title="Mark as read">
                                            <i data-feather="check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm" title="Delete">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center items-center space-x-2 mt-8">
                        <?php if ($page > 1): ?>
                            <a href="notifications.php?page=<?= $page - 1 ?>" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition-colors">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <span class="text-gray-400 text-sm">
                            Page <?= $page ?> of <?= $total_pages ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="notifications.php?page=<?= $page + 1 ?>" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition-colors">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>