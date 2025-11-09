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
    <title>Safety Information | TravelGO Orbit</title>
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
                <a href="overview.php">Overview</a>
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
            <h1>Safety Information</h1>
            <p>Your safety is our top priority at TravelGO Orbit. We work closely with airlines, hotels, and travel partners to ensure that all safety standards and protocols are met for your peace of mind during travel.</p>
            <p>This section provides comprehensive safety information, including travel advisories, health requirements, security measures, and emergency procedures to help you travel confidently and securely.</p>
        </div>
        
        <div class="card">
            <h2>Travel Safety Guidelines</h2>
            <p>Stay informed about current travel safety guidelines, including health protocols, security measures, and destination-specific requirements. We regularly update this information based on official sources and industry standards.</p>
            <p>Before you travel, review the latest safety information for your destination and ensure you have all necessary documentation and preparations in place for a safe and comfortable journey.</p>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-links">
            <a href="about.php">About Us</a>
            <a href="careers.php">Careers</a>
            <a href="blog.php">Blog</a>
            <a href="press.php">Press</a>
            <a href="contact.php">Contact Us</a>
            <a href="safety.php" class="active">Safety Information</a>
            <a href="cancellations.php">Cancellation Options</a>
            <a href="report.php">Report Issue</a>
            <a href="terms.php">Terms & Conditions</a>
            <a href="privacy.php">Privacy Policy</a>
        </div>
        <p style="color: #6b7280; margin: 0;">&copy; 2023 TravelGO Orbit. All rights reserved.</p>
    </footer>
</body>
</html>