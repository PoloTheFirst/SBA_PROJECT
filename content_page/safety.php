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
            <h1 class="text-3xl font-bold text-white mb-6">Safety Information</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">Your safety is our top priority at TravelGO Orbit. We work closely with airlines, hotels, and travel partners to ensure that all safety standards and protocols are met for your peace of mind during travel.</p>
                    <p class="mb-4">This section provides comprehensive safety information, including travel advisories, health requirements, security measures, and emergency procedures to help you travel confidently and securely.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Travel Safety Guidelines</h2>
                    <p class="mb-4">Stay informed about current travel safety guidelines, including health protocols, security measures, and destination-specific requirements. We regularly update this information based on official sources and industry standards.</p>
                    <p class="mb-4">Before you travel, review the latest safety information for your destination and ensure you have all necessary documentation and preparations in place for a safe and comfortable journey.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Safety Measures</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Airline safety certifications and standards</li>
                        <li>Health and hygiene protocols</li>
                        <li>Security screening procedures</li>
                        <li>Emergency contact information</li>
                        <li>Travel insurance recommendations</li>
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
