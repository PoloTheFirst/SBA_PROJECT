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
    <title>Cancellation Options | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">Cancellation Options</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">We understand that plans can change. TravelGO Orbit offers flexible cancellation options to accommodate your needs. This section provides information about our cancellation policies, fees, and procedures.</p>
                    <p class="mb-4">Review your booking details to understand the specific cancellation terms that apply to your reservation. Different airlines and travel providers may have varying policies and deadlines.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Cancellation Policies</h2>
                    <p class="mb-4">Learn about our standard cancellation policies, including timeframes for free cancellations, applicable fees, and refund processing times. We strive to provide transparent information to help you make informed decisions.</p>
                    <p class="mb-4">For bookings affected by exceptional circumstances such as weather events or travel restrictions, special cancellation options may be available. Check this section for updates on flexible booking policies.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Refund Process</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Refunds are processed according to the airline's policy</li>
                        <li>Processing time typically ranges from 7-14 business days</li>
                        <li>Refunds are issued to the original payment method</li>
                        <li>Service fees may apply depending on the fare type</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">How to Cancel</h2>
                    <p class="mb-4">You can cancel your booking through your account dashboard or by contacting our customer support team. Make sure to review the cancellation terms before proceeding.</p>
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
