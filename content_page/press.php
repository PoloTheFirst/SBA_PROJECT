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
    <title>Press | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">Press Room</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">Welcome to the TravelGO Orbit press room. Here you'll find our latest news, press releases, media resources, and company announcements. Stay updated with our latest developments and industry achievements.</p>
                    <p class="mb-4">We're committed to transparency and keeping our stakeholders informed about our growth, innovations, and contributions to the travel industry.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Media Resources</h2>
                    <p class="mb-4">Access our media kit, company logos, executive bios, and other resources for journalists and media professionals. We're available to provide interviews, expert commentary, and additional information about our services.</p>
                    <p class="mb-4">For media inquiries, please contact our press office through the contact information provided. We're happy to assist with story ideas, interviews, and background information about TravelGO Orbit.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Press Contact</h2>
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <p class="mb-2"><strong>Email:</strong> press@travelgorbit.com</p>
                        <p class="mb-2"><strong>Phone:</strong> +852 1234 5678</p>
                        <p><strong>Address:</strong> TravelGO Orbit Press Office, 123 Travel Street, Hong Kong SAR</p>
                    </div>
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
