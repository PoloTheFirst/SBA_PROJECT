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
    <title>Report Issue | TravelGO Orbit</title>
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
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex flex-col">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <a href="../index.php" class="flex items-center space-x-2">
                    <i data-feather="navigation" class="text-yellow-400"></i>
                    <span class="text-xl font-bold">TravelGO Orbit</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl flex-grow">
        <div class="content-card rounded-lg p-8">
            <h1 class="text-3xl font-bold text-white mb-6">Report an Issue</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">If you've encountered any issues with your booking, travel experience, or our services, we want to know about it. Use this section to report problems and help us improve our services.</p>
                    <p class="mb-4">We take all reports seriously and are committed to resolving issues promptly. Your feedback helps us enhance the travel experience for all our customers.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Issue Categories</h2>
                    <p class="mb-4">Report issues related to bookings, payments, customer service, website functionality, or any other concerns about your TravelGO Orbit experience. We categorize reports to ensure they reach the appropriate team for resolution.</p>
                    <p class="mb-4">For urgent matters affecting ongoing travel, please contact our emergency support line immediately. For non-urgent issues, you can use our online reporting system, and we'll respond within 24-48 hours.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">How to Report</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Fill out the issue report form</li>
                        <li>Provide detailed information about the problem</li>
                        <li>Include relevant booking or transaction details</li>
                        <li>Attach any supporting documents or screenshots</li>
                        <li>Submit your report for review</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; 2023 TravelGO Orbit. All rights reserved.</p>
        </div>
    </footer>

    <script>
        feather.replace();
    </script>
</body>
</html>
