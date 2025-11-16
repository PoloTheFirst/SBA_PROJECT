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
    <title>Careers | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">Careers at TravelGO Orbit</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">Join our dynamic team and help shape the future of travel. At TravelGO Orbit, we're looking for passionate individuals who share our vision of making travel accessible and enjoyable for everyone.</p>
                    <p class="mb-4">We offer exciting career opportunities in technology, customer service, marketing, operations, and more. Join us in our mission to revolutionize the travel industry through innovation and exceptional service.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Current Openings</h2>
                    <p class="mb-4">Explore our current job opportunities and find a position that matches your skills and career aspirations. We're constantly growing and looking for talented professionals to join our team.</p>
                    <p class="mb-4">At TravelGO Orbit, we value diversity, innovation, and collaboration. We offer competitive compensation, professional development opportunities, and a dynamic work environment.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Why Work With Us</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Competitive salary and benefits package</li>
                        <li>Flexible work arrangements</li>
                        <li>Professional development opportunities</li>
                        <li>Collaborative and inclusive culture</li>
                        <li>Travel perks and discounts</li>
                        <li>Innovative technology stack</li>
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
