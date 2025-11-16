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
    <title>About Us | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">About TravelGO Orbit</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">TravelGO Orbit is a leading travel platform dedicated to making travel simple, accessible, and enjoyable for everyone. Our mission is to connect people with their dream destinations through innovative technology and exceptional service.</p>
                    <p class="mb-4">Founded with the vision of revolutionizing the travel industry, we partner with airlines, hotels, and travel service providers worldwide to offer comprehensive travel solutions tailored to modern travelers' needs.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Our Story</h2>
                    <p class="mb-4">Since our inception, TravelGO Orbit has been committed to providing seamless travel experiences. We leverage cutting-edge technology to offer real-time flight information, competitive pricing, and personalized travel recommendations.</p>
                    <p class="mb-4">Our team of travel experts and technology innovators work together to ensure that every journey booked through TravelGO Orbit is smooth, secure, and memorable.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Our Mission</h2>
                    <p class="mb-4">We strive to make travel accessible to everyone by providing transparent pricing, flexible booking options, and exceptional customer service. Our goal is to remove the barriers that make travel complicated and stressful.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Why Choose Us</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Comprehensive flight search and booking system</li>
                        <li>Competitive prices and exclusive deals</li>
                        <li>24/7 customer support</li>
                        <li>Secure payment processing</li>
                        <li>User-friendly interface</li>
                        <li>Mobile-responsive design</li>
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
