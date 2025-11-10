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
    <title>TravelGO Orbit - Your way to GO!!!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        .vanta-bg {
            min-height: 100vh;
            background: #111827;
        }

        .search-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .trip-switch {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Updated color scheme for dark theme */
        .hero-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }

        .nav-transparent {
            background-color: rgba(17, 24, 39, 0.9);
            backdrop-filter: blur(10px);
        }

        .trip-selector {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .trip-indicator {
            background-color: #3b82f6;
        }

        .btn-primary {
            background-color: #f59e0b;
            color: #1e3a8a;
        }

        .btn-primary:hover {
            background-color: #d97706;
        }

        .text-primary {
            color: #ffffff;
        }

        .text-secondary {
            color: #93c5fd;
        }

        .text-accent {
            color: #f59e0b;
        }

        .bg-section {
            background-color: #1f2937;
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

        /* Dark theme adjustments for existing elements */
        .dark-card {
            background: #1f2937;
            border: 1px solid #374151;
        }

        .dark-text {
            color: #ffffff;
        }

        .dark-text-secondary {
            color: #d1d5db;
        }

        .line-clamp-2 {
            overflow: hidden;
            /* WebKit (Chrome, Safari, newer Edge) */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            /* Standard property for future browsers */
            line-clamp: 2;
            box-orient: vertical;
        }

        /* Fallback for browsers that don't support line clamping */
        @supports not ((-webkit-line-clamp: 2) or (line-clamp: 2)) {
            .line-clamp-2 {
                max-height: 3em;
                line-height: 1.5em;
            }
        }

        .location-item {
            transition: all 0.2s ease;
        }

        .location-item:hover {
            background-color: #374151;
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

        /* Custom scrollbar for dropdown */
        #from-dropdown::-webkit-scrollbar,
        #to-dropdown::-webkit-scrollbar {
            width: 6px;
        }

        #from-dropdown::-webkit-scrollbar-track,
        #to-dropdown::-webkit-scrollbar-track {
            background: #374151;
            border-radius: 3px;
        }

        #from-dropdown::-webkit-scrollbar-thumb,
        #to-dropdown::-webkit-scrollbar-thumb {
            background: #6B7280;
            border-radius: 3px;
        }

        #from-dropdown::-webkit-scrollbar-thumb:hover,
        #to-dropdown::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
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
                            <a href="content_page/underdevelop.php" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Hotels</a>
                            <a href="content_page/underdevelop.php" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Packages</a>
                            <a href="content_page/underdevelop.php" class="text-white hover:text-yellow-400 px-3 py-2 rounded-md text-sm font-medium">Deals</a>

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

        <!-- Hero Section -->
        <div class="container mx-auto px-4 py-32 flex flex-col items-center justify-center text-center">
            <h1 class="text-5xl font-bold text-white mb-6">Where will wanderlust take you?</h1>
            <p class="text-xl text-blue-100 mb-10 max-w-2xl">Compare prices from 1000+ airlines and book with confidence</p>

            <!-- Search Card -->
            <div class="search-card p-8 w-full max-w-4xl">
                <!-- Trip Type Switch -->
                <div class="flex trip-selector rounded-lg p-1 mb-8 relative">
                    <div id="trip-indicator" class="trip-indicator absolute h-10 rounded-md transition-all duration-300 w-1/3"></div>
                    <button type="button" data-trip="round" class="trip-switch text-white py-2 px-4 rounded-md flex-1 text-center z-10 font-medium">Round Trip</button>
                    <button type="button" data-trip="oneway" class="trip-switch text-white py-2 px-4 rounded-md flex-1 text-center z-10 font-medium">One Way</button>
                    <button type="button" data-trip="multi" class="trip-switch text-white py-2 px-4 rounded-md flex-1 text-center z-10 font-medium">Multi-City</button>
                </div>

                <!-- Search Form -->
                <form id="search-form" method="GET" class="space-y-6">
                    <input type="hidden" name="trip_type" id="trip_type" value="round">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="from" class="block text-white text-sm font-medium mb-2">From</label>
                            <div class="relative">
                                <i data-feather="map-pin" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                                <input type="text" id="from" name="from" required
                                    class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                                    placeholder="City or Airport">
                                <div id="from-dropdown" class="absolute hidden w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-xl z-50 max-h-80 overflow-y-auto"></div>
                            </div>
                        </div>
                        <div>
                            <label for="to" class="block text-white text-sm font-medium mb-2">To</label>
                            <div class="relative">
                                <i data-feather="map-pin" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                                <input type="text" id="to" name="to" required
                                    class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800"
                                    placeholder="City or Airport">
                                <div id="to-dropdown" class="absolute hidden w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-xl z-50 max-h-80 overflow-y-auto"></div>
                            </div>
                        </div>
                        <div>
                            <label for="departure" class="block text-white text-sm font-medium mb-2">Departure</label>
                            <div class="relative">
                                <i data-feather="calendar" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                                <input type="date" id="departure" name="departure" required
                                    class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800">
                            </div>
                        </div>
                        <div id="return-date-container">
                            <label for="return" class="block text-white text-sm font-medium mb-2">Return</label>
                            <div class="relative">
                                <i data-feather="calendar" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                                <input type="date" id="return" name="return"
                                    class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-800">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="passengers" class="block text-white text-sm font-medium mb-2">Travelers</label>
                            <div class="relative">
                                <i data-feather="users" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                                <select id="passengers" name="passengers" class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none text-gray-800">
                                    <option value="1">1 Adult</option>
                                    <option value="2">2 Adults</option>
                                    <option value="3">3 Adults</option>
                                    <option value="4">4 Adults</option>
                                    <option value="5">Family</option>
                                </select>
                                <i data-feather="chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>
                        <div>
                            <label for="class" class="block text-white text-sm font-medium mb-2">Class</label>
                            <div class="relative">
                                <i data-feather="briefcase" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                                <select id="class" name="class" class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none text-gray-800">
                                    <option value="economy">Economy</option>
                                    <option value="premium">Premium Economy</option>
                                    <option value="business">Business</option>
                                    <option value="first">First Class</option>
                                </select>
                                <i data-feather="chevron-down" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button type="submit" class="btn-primary font-bold py-3 px-8 rounded-lg transition-colors w-full text-lg">
                                Search Flights <i data-feather="search" class="inline ml-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Trending Destinations Section -->
        <div class="bg-section py-16">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-white mb-12 text-center">Trending Destinations</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="trending-destinations">
                    <!-- Dynamic content will be loaded here -->
                </div>

                <div class="text-center mt-12">
                    <button class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-blue-900 font-bold py-3 px-8 rounded-lg transition-all transform hover:scale-105 shadow-lg" onclick="window.location.href='content_page/underdevelop.php';">
                        View All Destinations <i data-feather="arrow-right" class="inline ml-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Promo Video Section -->
        <div class="mt-16">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Explore TravelGO Orbit</h3>
            <div class="video-container mx-auto max-w-4xl">
                <video controls class="w-full rounded-lg shadow-lg">
                    <source src="./assets/TravelGO_Orbits_Promo_Video.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-16 bg-gray-900">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-white mb-12 text-center">Why Choose TravelGO Orbit</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="text-center px-4">
                        <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-feather="award" class="text-yellow-400 w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Best Price Guarantee</h3>
                        <p class="text-gray-300">We guarantee the best prices for your flights and hotels. Found a better deal? We'll match it!</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="text-center px-4">
                        <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-feather="shield" class="text-yellow-400 w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Secure Booking</h3>
                        <p class="text-gray-300">Your personal and payment information is protected with our advanced security measures.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="text-center px-4">
                        <div class="bg-blue-900 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-feather="headphones" class="text-yellow-400 w-8 h-8"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">24/7 Support</h3>
                        <p class="text-gray-300">Our customer service team is available around the clock to assist you with any questions.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="py-16 bg-section">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-white mb-12 text-center">What Our Travelers Say</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Testimonial 1 -->
                    <div class="dark-card p-6 rounded-xl shadow-md">
                        <div class="flex items-center mb-4">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Sarah J." class="w-12 h-12 rounded-full object-cover mr-4">
                            <div>
                                <h4 class="font-bold text-white">Sarah J.</h4>
                                <div class="flex text-yellow-500">
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-300">"Booking our honeymoon through TravelGO Orbit was seamless! The team helped us find the perfect package at an amazing price. We'll definitely use them again!"</p>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="dark-card p-6 rounded-xl shadow-md">
                        <div class="flex items-center mb-4">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" alt="Michael T." class="w-12 h-12 rounded-full object-cover mr-4">
                            <div>
                                <h4 class="font-bold text-white">Michael T.</h4>
                                <div class="flex text-yellow-500">
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                    <i data-feather="star" class="w-4 h-4 fill-current"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-300">"I travel frequently for business and always use TravelGO Orbit. Their customer service is exceptional and they've saved me thousands on flights!"</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="py-16 bg-blue-900 text-white">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold mb-4">Get Travel Deals & Updates</h2>
                <p class="text-blue-100 mb-8 max-w-2xl mx-auto">Subscribe to our newsletter and receive exclusive offers, travel tips, and destination inspiration straight to your inbox.</p>
                <form class="max-w-md mx-auto flex">
                    <input type="email" placeholder="Your email address" class="flex-grow py-3 px-4 rounded-l-lg focus:outline-none text-gray-800">
                    <button type="button" class="btn-primary font-bold py-3 px-6 rounded-r-lg transition-colors" onclick="window.location.href='content_page/underdevelop.php';">
                        Subscribe <i data-feather="send" class="inline ml-2"></i>
                    </button>
                </form>
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
                        <p class="text-gray-300 mb-4">Making travel simple, affordable and unforgettable since 2025.</p>
                        <div class="flex space-x-4">
                            <a href="content_page/underdevelop.php" class="text-gray-400 hover:text-white">
                                <i data-feather="facebook"></i>
                            </a>
                            <a href="content_page/underdevelop.php" class="text-gray-400 hover:text-white">
                                <i data-feather="twitter"></i>      
                            </a>
                            <a href="content_page/underdevelop.php" class="tex  t-gray-400 hover:text-white">
                                <i data-feather="instagram"></i>
                            </a>
                            <a href="content_page/underdevelop.php" class="text-gray-400 hover:text-white">
                                <i data-feather="linkedin"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Company</h3>
                        <ul class="space-y-2">
                            <li><a href="./content_page/about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                            <li><a href="./content_page/careers.php" class="text-gray-400 hover:text-white">Careers</a></li>
                            <li><a href="./content_page/blog.php" class="text-gray-400 hover:text-white">Blog</a></li>
                            <li><a href="./content_page/press.php" class="text-gray-400 hover:text-white">Press</a></li>
                            <li><a href="./content_page/contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Column 3 -->
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="./content_page/help.php" class="text-gray-400 hover:text-white">Help Center</a></li>
                            <li><a href="./content_page/safety.php" class="text-gray-400 hover:text-white">Safety Information</a></li>
                            <li><a href="./content_page/cancellation.php" class="text-gray-400 hover:text-white">Cancellation Options</a></li>
                            <li><a href="./content_page/report.php" class="text-gray-400 hover:text-white">Report Issue</a></li>
                            <li><a href="./content_page/terms.php" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                            <li><a href="./content_page/privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        </ul>
                    </div>

                    <!-- Column 4 -->
                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Download Our App</h3>
                        <div class="space-y-3">
                            <a href="content_page/underdevelop.php" class="flex items-center bg-black text-white p-2 rounded-lg w-full max-w-xs">
                                <i data-feather="download" class="mr-2"></i>
                                <div>
                                    <div class="text-xs">Download on the</div>
                                    <div class="font-bold">App Store</div>
                                </div>
                            </a>
                            <a href="content_page/underdevelop.php" class="flex items-center bg-black text-white p-2 rounded-lg w-full max-w-xs">
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
                    <p>© 2025 TravelGO Orbit. All rights reserved.</p>
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

            // Trip type switch functionality
            const tripButtons = document.querySelectorAll('.trip-switch');
            const tripIndicator = document.getElementById('trip-indicator');
            const returnContainer = document.getElementById('return-date-container');
            const returnInput = document.getElementById('return');

            function updateTripType(tripType) {
                tripButtons.forEach(btn => {
                    btn.classList.remove('text-white', 'font-semibold');
                    btn.classList.add('text-white');
                });

                const activeButton = document.querySelector(`[data-trip="${tripType}"]`);
                activeButton.classList.remove('text-white');
                activeButton.classList.add('text-white', 'font-semibold');

                // Update hidden input
                document.getElementById('trip_type').value = tripType;

                // Move indicator and handle return date
                if (tripType === 'round') {
                    tripIndicator.style.transform = 'translateX(0)';
                    returnContainer.style.display = 'block';
                    returnInput.required = true;
                    document.getElementById('search-form').action = 'search_results_roundtrip.php';
                } else if (tripType === 'oneway') {
                    tripIndicator.style.transform = 'translateX(100%)';
                    returnContainer.style.display = 'none';
                    returnInput.required = false;
                    returnInput.value = '';
                    document.getElementById('search-form').action = 'search_results.php';
                } else {
                    tripIndicator.style.transform = 'translateX(200%)';
                    returnContainer.style.display = 'none';
                    returnInput.required = false;
                    returnInput.value = '';
                    alert('Multi-city search coming soon!');
                    document.getElementById('search-form').action = 'content_page/underdevelop.php';
                }
            }

            tripButtons.forEach(button => {
                button.addEventListener('click', function() {
                    updateTripType(this.dataset.trip);
                });
            });

            // Set default dates
            const today = new Date();
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);

            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            document.getElementById('departure').value = formatDate(nextWeek);
            document.getElementById('departure').min = formatDate(today);

            const returnDate = new Date(nextWeek);
            returnDate.setDate(nextWeek.getDate() + 4);
            document.getElementById('return').value = formatDate(returnDate);
            document.getElementById('return').min = formatDate(nextWeek);

            // Initialize with round trip
            updateTripType('round');

            // Airport/City Search Functionality
            function initLocationSearch() {
                const fromInput = document.getElementById('from');
                const toInput = document.getElementById('to');
                const fromDropdown = document.getElementById('from-dropdown');
                const toDropdown = document.getElementById('to-dropdown');

                // Initialize search for both inputs
                initSearchInput(fromInput, fromDropdown, 'Leaving from');
                initSearchInput(toInput, toDropdown, 'Going to', true);
            }

            function initSearchInput(input, dropdown, placeholder, showAnywhere = false) {
                let isOpen = false;
                let currentFocus = -1;

                input.addEventListener('focus', function() {
                    if (!isOpen) {
                        searchLocations('', dropdown, showAnywhere);
                        dropdown.classList.remove('hidden');
                        isOpen = true;
                    }
                });

                input.addEventListener('input', function(e) {
                    const query = e.target.value;
                    searchLocations(query, dropdown, showAnywhere);
                    dropdown.classList.remove('hidden');
                    isOpen = true;
                    currentFocus = -1;
                });

                input.addEventListener('keydown', function(e) {
                    if (isOpen) {
                        const items = dropdown.querySelectorAll('.location-item');

                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            currentFocus = Math.min(currentFocus + 1, items.length - 1);
                            updateActiveItem(items, currentFocus);
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            currentFocus = Math.max(currentFocus - 1, -1);
                            updateActiveItem(items, currentFocus);
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (currentFocus > -1 && items[currentFocus]) {
                                items[currentFocus].click();
                            }
                        } else if (e.key === 'Escape') {
                            dropdown.classList.add('hidden');
                            isOpen = false;
                            currentFocus = -1;
                        }
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                        isOpen = false;
                        currentFocus = -1;
                    }
                });

                // Update placeholder when focused
                input.addEventListener('focus', function() {
                    input.setAttribute('placeholder', `${placeholder}...`);
                });

                input.addEventListener('blur', function() {
                    input.setAttribute('placeholder', 'City or Airport');
                });
            }

            function searchLocations(query, dropdown, showAnywhere = false) {
                fetch(`search_locations.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = '';

                        if (showAnywhere && query === '') {
                            const anywhereItem = document.createElement('div');
                            anywhereItem.className = 'location-item p-4 hover:bg-gray-700 cursor-pointer border-b border-gray-700';
                            anywhereItem.innerHTML = `
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-white font-semibold">Anywhere</div>
                                        <div class="text-gray-400 text-sm">Explore all destinations</div>
                                    </div>
                                    <div class="text-yellow-400 text-sm">All Around The World</div>
                                </div>
                            `;
                            anywhereItem.addEventListener('click', function() {
                                dropdown.previousElementSibling.value = 'Anywhere';
                                dropdown.classList.add('hidden');
                            });
                            dropdown.appendChild(anywhereItem);
                        }

                        data.forEach(location => {
                            const item = document.createElement('div');
                            item.className = 'location-item p-4 hover:bg-gray-700 cursor-pointer border-b border-gray-700';

                            let displayText = '';
                            if (location.type === 'airport') {
                                displayText = `
                                    <div class="text-white font-semibold">${location.code} - ${location.name}</div>
                                    <div class="text-gray-400 text-sm">${location.city_name}, ${location.country_name}</div>
                                    ${location.distance_from_downtown ? `<div class="text-gray-500 text-xs mt-1">${location.distance_from_downtown}</div>` : ''}
                                `;
                            } else {
                                displayText = `
                                    <div class="text-white font-semibold">${location.name}</div>
                                    <div class="text-gray-400 text-sm">${location.country_name}</div>
                                `;
                            }

                            item.innerHTML = displayText;
                            item.addEventListener('click', function() {
                                const displayName = location.type === 'airport' ?
                                    `${location.code} - ${location.name}` :
                                    location.name;
                                dropdown.previousElementSibling.value = displayName;
                                dropdown.classList.add('hidden');
                            });

                            dropdown.appendChild(item);
                        });

                        if (data.length === 0 && !showAnywhere) {
                            dropdown.innerHTML = '<div class="p-4 text-gray-400 text-center">No locations found</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error searching locations:', error);
                        dropdown.innerHTML = '<div class="p-4 text-red-400 text-center">Error searching locations</div>';
                    });
            }

            function updateActiveItem(items, index) {
                items.forEach(item => item.classList.remove('bg-gray-700', 'text-white'));
                if (index > -1 && items[index]) {
                    items[index].classList.add('bg-gray-700', 'text-white');
                }
            }

            // Load trending destinations
            function loadTrendingDestinations() {
                fetch('get_destinations.php?trending=true')
                    .then(response => response.json())
                    .then(destinations => {
                        const container = document.getElementById('trending-destinations');
                        if (!container) return;

                        container.innerHTML = destinations.map(dest => `
                            <div class="dark-card rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-shadow transform hover:-translate-y-1 cursor-pointer">
                                <div class="relative h-48 overflow-hidden">
                                    <img src="${dest.image_url}" alt="${dest.location_name}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 to-transparent"></div>
                                    <div class="absolute bottom-4 left-4 text-white">
                                        <h3 class="text-xl font-bold">${dest.location_name}</h3>
                                        <p class="text-blue-100">HKD${dest.price} per traveler</p>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-300 font-medium">${dest.country_name}</span>
                                        <button class="text-blue-400 hover:text-yellow-400 transition-colors">
                                            <i data-feather="heart"></i>
                                        </button>
                                    </div>
                                    <p class="text-gray-300 text-sm mt-2 line-clamp-2">${dest.description}</p>
                                </div>
                            </div>
                        `).join('');

                        // Re-initialize feather icons for new content
                        feather.replace();
                    })
                    .catch(error => console.error('Error loading destinations:', error));
            }

            // Form validation
            document.getElementById('search-form').addEventListener('submit', function(e) {
                const from = document.getElementById('from').value;
                const to = document.getElementById('to').value;
                const departure = document.getElementById('departure').value;

                if (!from || !to || !departure) {
                    e.preventDefault();
                    alert('Please fill in all required fields: From, To, and Departure Date');
                    return;
                }

                if (from === to) {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same');
                    return;
                }
            });

            // Initialize when DOM is loaded
            initLocationSearch();
            loadTrendingDestinations();
        });
    </script>
</body>

</html>