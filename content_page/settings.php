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
    <title>Settings | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">Account Settings</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">Customize your TravelGO Orbit experience and manage your account preferences. Update your personal information, communication preferences, and security settings.</p>
                    <p class="mb-4">You can control how you receive notifications, manage your privacy settings, and configure your travel preferences for a personalized booking experience.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Preferences</h2>
                    <p class="mb-4">Adjust your travel preferences, including seat preferences, meal options, and special assistance requirements for your flights.</p>
                    <p class="mb-4">Set your notification preferences to stay informed about flight changes, special offers, and important travel updates that matter to you.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Account Management</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Update personal information</li>
                        <li>Change password and security settings</li>
                        <li>Manage notification preferences</li>
                        <li>Configure privacy settings</li>
                        <li>Set travel preferences</li>
                    </ul>
                </section>
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
