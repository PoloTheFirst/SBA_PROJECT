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
    <title>Under Development - TravelGO Orbit</title>
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

    <div class="container mx-auto px-4 py-8 max-w-4xl flex-grow flex items-center justify-center">
        <div class="content-card rounded-lg p-12 w-full pulse-glow">
            <div class="construction-icon mb-8 text-center">
                <i data-feather="tool" class="w-24 h-24 text-yellow-400 mx-auto"></i>
            </div>
            
            <h1 class="text-4xl font-bold text-white mb-6 text-center">Under Development</h1>
            
            <div class="text-xl text-blue-100 mb-8 text-center">
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

            <div class="text-lg text-gray-300 mb-8 text-center">
                <p>Thank you for your patience. We'll notify you when this feature is ready!</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="../index.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-8 rounded-lg transition-colors text-center">
                    <i data-feather="home" class="inline mr-2"></i>Back to Home
                </a>
                <a href="mailto:support@travelgoorbit.com" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-blue-700 transition-colors text-center">
                    <i data-feather="mail" class="inline mr-2"></i>Contact Support
                </a>
            </div>

            <div class="mt-8 text-sm text-gray-400 text-center">
                <p>Expected launch: Coming Soon</p>
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
