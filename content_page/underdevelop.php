<?php
// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load user data if logged in but session data is missing
if (isset($_SESSION['user_id']) && (!isset($_SESSION['username']) || !isset($_SESSION['profile_picture']))) {
    require 'connection.php';

    try {
        $stmt = $pdo->prepare("SELECT username, name, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['username'] = $user['username'] ?? 'user';
            $_SESSION['name'] = $user['name'] ?? 'User';
            $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;
        }
    } catch (Exception $e) {
        error_log("Error loading user data: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Development - TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        .vanta-bg {
            min-height: 100vh;
            background: #111827;
        }

        .nav-transparent {
            background-color: rgba(17, 24, 39, 0.9);
            backdrop-filter: blur(10px);
        }

        .hero-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }

        .btn-primary {
            background-color: #f59e0b;
            color: #1e3a8a;
        }

        .btn-primary:hover {
            background-color: #d97706;
        }

        .card-dark {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
        }

        /* Profile Dropdown Styles */
        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            min-width: 180px;
            z-index: 1000;
        }

        .profile-dropdown.active {
            display: block;
        }

        .profile-dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #d1d5db;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .profile-dropdown-item:hover,
        .profile-dropdown-item:focus {
            background: #374151;
            color: #ffffff;
            outline: none;
        }

        .profile-dropdown-item i {
            margin-right: 0.75rem;
            width: 16px;
            height: 16px;
        }

        .profile-avatar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .profile-avatar:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .profile-avatar:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .avatar-initials {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #1e3a8a;
            font-size: 0.875rem;
        }

        .profile-name {
            display: none;
        }

        @media (min-width: 768px) {
            .profile-name {
                display: block;
            }
        }

        .construction-icon {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }

        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(245, 158, 11, 0.5); }
            to { box-shadow: 0 0 30px rgba(245, 158, 11, 0.8); }
        }
    </style>
</head>

<body class="font-['Poppins'] bg-gray-900">
    <div id="vanta-bg" class="vanta-bg hero-bg">
        <!-- Navigation -->
        <nav class="nav-transparent backdrop-blur-md fixed w-full z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-white flex items-center">
                            <i data-feather="navigation" class="mr-2"></i>TravelGO Orbit
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-center space-x-8">
                            <a href="index.php" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Flights</a>
                            <a href="#" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Hotels</a>
                            <a href="#" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Packages</a>
                            <a href="#" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Deals</a>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <!-- User is logged in - Profile Dropdown -->
                                <div class="relative" id="profile-dropdown-container">
                                    <button class="profile-avatar" id="profile-toggle" aria-expanded="false" aria-haspopup="true">
                                        <?php
                                        // Improved avatar handling
                                        $nav_profile_pic = '';
                                        if (!empty($_SESSION['profile_picture']) && file_exists($_SESSION['profile_picture'])) {
                                            $nav_profile_pic = htmlspecialchars($_SESSION['profile_picture']) . '?t=' . time();
                                        } else {
                                            $username = $_SESSION['username'] ?? 'user';
                                            $name = $_SESSION['name'] ?? 'User';
                                            $nav_profile_pic = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=f59e0b&color=1e3a8a&size=32";
                                        }
                                        ?>
                                        <img src="<?= $nav_profile_pic ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-yellow-500"
                                            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name'] ?? 'U') ?>&background=f59e0b&color=1e3a8a&size=32'">
                                        <span class="profile-name">@<?= htmlspecialchars($_SESSION['username'] ?? 'user') ?></span>
                                        <i data-feather="chevron-down" class="w-4 h-4 text-gray-300"></i>
                                    </button>

                                    <div class="profile-dropdown" id="profile-menu" role="menu">
                                        <a href="dashboard.php" class="profile-dropdown-item" role="menuitem">
                                            <i data-feather="user"></i>
                                            Dashboard
                                        </a>
                                        <form method="POST" action="logout.php" class="w-full">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <button type="submit" class="profile-dropdown-item" role="menuitem">
                                                <i data-feather="log-out"></i>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- User is not logged in -->
                                <a href="login.php" class="bg-blue-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:bg-blue-700 transition-colors">
                                    Sign In
                                </a>
                                <a href="register.php" class="bg-[#ca8a04] text-[#2c407f] font-bold px-6 py-2 rounded-full text-sm hover:bg-yellow-500 transition-colors">
                                    Register
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center space-x-2">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Mobile Profile Dropdown -->
                            <div class="relative" id="mobile-profile-dropdown-container">
                                <button class="profile-avatar" id="mobile-profile-toggle" aria-expanded="false" aria-haspopup="true">
                                    <?php
                                    $mobile_profile_pic = !empty($_SESSION['profile_picture']) ?
                                        htmlspecialchars($_SESSION['profile_picture']) . '?t=' . time() :
                                        "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['name'] ?? 'U') . "&background=f59e0b&color=1e3a8a&size=32";
                                    ?>
                                    <img src="<?= $mobile_profile_pic ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-yellow-500">
                                    <i data-feather="chevron-down" class="w-4 h-4 text-gray-300"></i>
                                </button>

                                <div class="profile-dropdown" id="mobile-profile-menu" role="menu">
                                    <a href="dashboard.php" class="profile-dropdown-item" role="menuitem">
                                        <i data-feather="user"></i>
                                        Dashboard
                                    </a>
                                    <form method="POST" action="logout.php" class="w-full">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="profile-dropdown-item" role="menuitem">
                                            <i data-feather="log-out"></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="bg-blue-600 text-white px-3 py-2 rounded text-sm">
                                Sign In
                            </a>
                            <a href="register.php" class="bg-[#ca8a04] text-[#2c407f] font-bold px-3 py-2 rounded text-sm">
                                Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-32 flex flex-col items-center justify-center text-center min-h-screen">
            <div class="card-dark p-12 rounded-2xl max-w-2xl w-full pulse-glow">
                <div class="construction-icon mb-8">
                    <i data-feather="tool" class="w-24 h-24 text-yellow-400 mx-auto"></i>
                </div>
                
                <h1 class="text-4xl font-bold text-white mb-6">Under Development</h1>
                
                <div class="text-xl text-blue-100 mb-8">
                    <p class="mb-4">We're working hard to bring you this feature!</p>
                    <p>Our team is currently building this service to provide you with the best travel experience.</p>
                </div>

                <div class="bg-blue-900/30 border border-blue-700 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-yellow-400 mb-4">What's Coming?</h2>
                    <ul class="text-blue-100 text-left space-y-3">
                        <li class="flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 text-green-400 mr-3"></i>
                            <span>Advanced hotel booking system</span>
                        </li>
                        <li class="flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 text-green-400 mr-3"></i>
                            <span>Exclusive travel packages</span>
                        </li>
                        <li class="flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 text-green-400 mr-3"></i>
                            <span>Special deals and discounts</span>
                        </li>
                        <li class="flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 text-green-400 mr-3"></i>
                            <span>Enhanced user experience</span>
                        </li>
                    </ul>
                </div>

                <div class="text-lg text-gray-300 mb-8">
                    <p>Thank you for your patience. We'll notify you when this feature is ready!</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="index.php" class="btn-primary font-bold py-3 px-8 rounded-lg transition-colors text-center">
                        <i data-feather="home" class="inline mr-2"></i>Back to Home
                    </a>
                    <a href="mailto:support@travelgoorbit.com" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-blue-700 transition-colors text-center">
                        <i data-feather="mail" class="inline mr-2"></i>Contact Support
                    </a>
                </div>

                <div class="mt-8 text-sm text-gray-400">
                    <p>Expected launch: Coming Soon</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Column 1 -->
                    <div>
                        <h3 class="text-xl font-bold mb-4 flex items-center">
                            <i data-feather="navigation" class="mr-2"></i> TravelGO Orbit
                        </h3>
                        <p class="text-gray-300 mb-4">Making travel simple, affordable and unforgettable since 2023.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i data-feather="facebook"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i data-feather="twitter"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i data-feather="instagram"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white">
                                <i data-feather="linkedin"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Company</h3>
                        <ul class="space-y-2">
                            <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                            <li><a href="careers.php" class="text-gray-400 hover:text-white">Careers</a></li>
                            <li><a href="blog.php" class="text-gray-400 hover:text-white">Blog</a></li>
                            <li><a href="press.php" class="text-gray-400 hover:text-white">Press</a></li>
                            <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Column 3 -->
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="help.php" class="text-gray-400 hover:text-white">Help Center</a></li>
                            <li><a href="safety.php" class="text-gray-400 hover:text-white">Safety Information</a></li>
                            <li><a href="cancellation.php" class="text-gray-400 hover:text-white">Cancellation Options</a></li>
                            <li><a href="report.php" class="text-gray-400 hover:text-white">Report Issue</a></li>
                            <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                            <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        </ul>
                    </div>

                    <!-- Column 4 -->
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Download Our App</h3>
                        <div class="space-y-3">
                            <a href="#" class="flex items-center bg-black text-white p-2 rounded-lg w-full max-w-xs">
                                <i data-feather="download" class="mr-2"></i>
                                <div>
                                    <div class="text-xs">Download on the</div>
                                    <div class="font-bold">App Store</div>
                                </div>
                            </a>
                            <a href="#" class="flex items-center bg-black text-white p-2 rounded-lg w-full max-w-xs">
                                <i data-feather="download" class="mr-2"></i>
                                <div>
                                    <div class="text-xs">Get it on</div>
                                    <div class="font-bold">Google Play</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
                    <p>© 2023 TravelGO Orbit. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Initialize Vanta.js globe background with dark theme
        VANTA.GLOBE({
            el: "#vanta-bg",
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 1.00,
            color: 0x3b82f6,
            backgroundColor: 0x111827,
            size: 0.8
        });

        // Initialize feather icons
        feather.replace();

        // Profile Dropdown Functionality
        function initProfileDropdown(toggleId, menuId, containerId) {
            const toggle = document.getElementById(toggleId);
            const menu = document.getElementById(menuId);
            const container = document.getElementById(containerId);

            if (!toggle || !menu) return;

            // Toggle dropdown
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !isExpanded);
                menu.classList.toggle('active', !isExpanded);
            });

            // Close on escape key
            toggle.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    toggle.setAttribute('aria-expanded', 'false');
                    menu.classList.remove('active');
                }
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    toggle.setAttribute('aria-expanded', 'false');
                    menu.classList.remove('active');
                }
            });

            // Handle keyboard navigation in dropdown
            const menuItems = menu.querySelectorAll('.profile-dropdown-item');
            menuItems.forEach((item, index) => {
                item.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const nextItem = menuItems[index + 1] || menuItems[0];
                        nextItem.focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        const prevItem = menuItems[index - 1] || menuItems[menuItems.length - 1];
                        prevItem.focus();
                    } else if (e.key === 'Escape') {
                        toggle.setAttribute('aria-expanded', 'false');
                        menu.classList.remove('active');
                        toggle.focus();
                    }
                });
            });
        }

        // Initialize both desktop and mobile dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            initProfileDropdown('profile-toggle', 'profile-menu', 'profile-dropdown-container');
            initProfileDropdown('mobile-profile-toggle', 'mobile-profile-menu', 'mobile-profile-dropdown-container');
        });
    </script>
</body>

</html>
