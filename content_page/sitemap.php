<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap - TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
        }
        .content-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        a {
            color: #93c5fd;
            text-decoration: none;
        }
        a:hover {
            color: #3b82f6;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex flex-col">
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
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl flex-grow">
        <div class="content-card rounded-lg p-8 w-full">
            <h1 class="text-4xl font-bold text-white mb-6">Sitemap</h1>
            <div class="space-y-8">
                <div>
                    <h2 class="text-2xl font-bold text-yellow-400 mb-4">Main Pages</h2>
                    <ul class="text-lg text-blue-100 space-y-2">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../dashboard.php">Dashboard</a></li>
                        <li><a href="../login.php">Login</a></li>
                        <li><a href="../register.php">Register</a></li>
                        <li><a href="../forgot_password.php">Forgot Password</a></li>
                        <li><a href="../logout.php">Logout</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-yellow-400 mb-4">Search Pages</h2>
                    <ul class="text-lg text-blue-100 space-y-2">
                        <li><a href="../content_page/underdevelop.php">Search Results</a></li>
                        <li><a href="../search_results_roundtrip.php">Search Results Roundtrip</a></li>
                    </ul>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-yellow-400 mb-4">Content Pages</h2>
                    <ul class="text-lg text-blue-100 space-y-2">
                        <li><a href="../content_page/about.php">About Us</a></li>
                        <li><a href="../content_page/blog.php">Blog</a></li>
                        <li><a href="../content_page/bookings.php">Bookings</a></li>
                        <li><a href="../content_page/cancellation.php">Cancellation Policy</a></li>
                        <li><a href="../content_page/careers.php">Careers</a></li>
                        <li><a href="../content_page/confirmation.php">Confirmation</a></li>
                        <li><a href="../content_page/contact.php">Contact Us</a></li>
                        <li><a href="../content_page/help.php">Help</a></li>
                        <li><a href="../content_page/messages.php">Messages</a></li>
                        <li><a href="../content_page/overview.php">Overview</a></li>
                        <li><a href="../content_page/payments.php">Payments</a></li>
                        <li><a href="../content_page/press.php">Press</a></li>
                        <li><a href="../content_page/privacy.php">Privacy Policy</a></li>
                        <li><a href="../content_page/report.php">Report</a></li>
                        <li><a href="../content_page/safety.php">Safety</a></li>
                        <li><a href="../content_page/settings.php">Settings</a></li>
                        <li><a href="../content_page/terms.php">Terms and Conditions</a></li>
                        <li><a href="../content_page/underdevelop.php">Under Development</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; 2025 TravelGO Orbit. All rights reserved.</p>
        </div>
    </footer>

    <script>
        feather.replace();
    </script>
</body>
</html>