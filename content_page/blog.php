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
    <title>Blog | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">TravelGO Orbit Blog</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">Welcome to our travel blog, where we share inspiring stories, travel tips, destination guides, and industry insights. Discover new places and get expert advice to make your travels more enjoyable and memorable.</p>
                    <p class="mb-4">Our blog features articles written by travel experts, destination specialists, and fellow travelers. From hidden gems to popular destinations, we cover everything you need to know to plan your perfect trip.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Featured Articles</h2>
                    <p class="mb-4">Explore our latest articles covering travel trends, packing tips, cultural experiences, and destination highlights. Our blog is regularly updated with fresh content to inspire your next adventure.</p>
                    <p class="mb-4">Whether you're planning a business trip, family vacation, or solo adventure, our blog provides valuable information and inspiration to help you make the most of your travel experiences.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Categories</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Destination Guides</li>
                        <li>Travel Tips & Hacks</li>
                        <li>Cultural Experiences</li>
                        <li>Budget Travel</li>
                        <li>Luxury Travel</li>
                        <li>Adventure Travel</li>
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
