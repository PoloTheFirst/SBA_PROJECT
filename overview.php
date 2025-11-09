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
    <title>Overview | TravelGO Orbit</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #1f2937;
            padding: 1rem 2rem;
            border-bottom: 1px solid #374151;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .nav-links a {
            color: #d1d5db;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .nav-links a:hover {
            color: #ffffff;
            background-color: #374151;
        }
        .nav-links a.active {
            color: #f59e0b;
            background-color: #374151;
        }
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .footer {
            background-color: #1f2937;
            padding: 2rem;
            text-align: center;
            border-top: 1px solid #374151;
            margin-top: 4rem;
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
        }
        .footer-links a:hover {
            color: #ffffff;
        }
        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                gap: 1rem;
            }
            .nav-links {
                justify-content: center;
            }
            .main-content {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <h1 style="margin: 0; color: #f59e0b;">TravelGO Orbit</h1>
            </div>
            <div class="nav-links">
                <a href="overview.php" class="active">Overview</a>
                <a href="bookings.php">Bookings</a>
                <a href="messages.php">Messages</a>
                <a href="payments.php">Payments</a>
                <a href="settings.php">Settings</a>
                <a href="help.php">Help Center</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="2fa_setup.php">2FA Setup</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="card">
            <h1>Overview</h1>
            <p>Welcome to your TravelGO Orbit dashboard overview. This page provides a summary of your recent travel activities, upcoming trips, and account information.</p>
            <p>From here, you can quickly access your bookings, manage your payments, update your settings, and get help with any travel-related questions. Use the navigation menu above to explore different sections of your account.</p>
        </div>
        
        <div class="card">
            <h2>Recent Activity</h2>
            <p>Your recent travel activity will appear here. This includes upcoming flights, recent bookings, and important notifications about your trips.</p>
            <p>When you have active bookings, you'll see details about your departure times, flight status, and any necessary actions you need to take before your trip.</p>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-links">
            <a href="about.php">About Us</a>
            <a href="careers.php">Careers</a>
            <a href="blog.php">Blog</a>
            <a href="press.php">Press</a>
            <a href="contact.php">Contact Us</a>
            <a href="safety.php">Safety Information</a>
            <a href="cancellations.php">Cancellation Options</a>
            <a href="report.php">Report Issue</a>
            <a href="terms.php">Terms & Conditions</a>
            <a href="privacy.php">Privacy Policy</a>
        </div>
        <p style="color: #6b7280; margin: 0;">&copy; 2023 TravelGO Orbit. All rights reserved.</p>
    </footer>
</body>
</html>