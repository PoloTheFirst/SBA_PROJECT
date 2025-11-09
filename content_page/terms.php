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
    <title>Terms of Service | TravelGO Orbit</title>
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
                <a href="privacy.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    Privacy Policy
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl flex-grow">
        <div class="content-card rounded-lg p-8">
            <h1 class="text-3xl font-bold text-white mb-6">Terms of Service</h1>
            <p class="text-gray-300 mb-6">Last updated: <?= date('F j, Y') ?></p>

            <div class="space-y-6 text-gray-300">
                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">1. Acceptance of Terms</h2>
                    <p class="mb-4">By accessing and using TravelGO Orbit ("the Service"), you accept and agree to be bound by the terms and provisions of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">2. Age Requirement</h2>
                    <p class="mb-4">You must be at least <strong class="text-yellow-400">18 years of age</strong> to use our Service. By using this Service, you represent and warrant that you are at least 18 years old. If you are under 18, you may not use the Service under any circumstances.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">3. User Accounts</h2>
                    <p class="mb-4">When you create an account with us, you must provide accurate, complete, and current information. You are responsible for safeguarding your account credentials and for all activities that occur under your account.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">4. Booking and Payments</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>All flight bookings are subject to availability</li>
                        <li>Prices are subject to change without notice</li>
                        <li>Payment must be completed to confirm bookings</li>
                        <li>Cancellation and refund policies vary by airline</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">5. Prohibited Activities</h2>
                    <p class="mb-4">You may not use our Service for any illegal or unauthorized purpose including:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Violating any laws in your jurisdiction</li>
                        <li>Creating fake bookings or reservations</li>
                        <li>Attempting to access others' accounts</li>
                        <li>Using the service for fraudulent activities</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">6. Intellectual Property</h2>
                    <p class="mb-4">The Service and its original content, features, and functionality are owned by TravelGO Orbit and are protected by international copyright, trademark, and other intellectual property laws.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">7. Termination</h2>
                    <p class="mb-4">We may terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms of Service or is harmful to other users, us, or third parties, or for any other reason.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">8. Limitation of Liability</h2>
                    <p class="mb-4">TravelGO Orbit shall not be liable for any indirect, incidental, special, consequential or punitive damages resulting from your use of or inability to use the service.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">9. Changes to Terms</h2>
                    <p class="mb-4">We reserve the right to modify these terms at any time. We will provide notice of significant changes through the Service. Continued use after changes constitutes acceptance of the new terms.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">10. Contact Information</h2>
                    <p class="mb-4">If you have any questions about these Terms, please contact us at:</p>
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <p><strong>Email:</strong> legal@travelgorbit.com</p>
                        <p><strong>Address:</strong> TravelGO Orbit Legal Department, 123 Travel Street, Hong Kong SAR</p>
                    </div>
                </section>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700">
                <p class="text-gray-400 text-sm">
                    By using our Service, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                </p>
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