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
    <title>Payments | TravelGO Orbit</title>
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
            <h1 class="text-3xl font-bold text-white mb-6">Payment Methods</h1>
            
            <div class="space-y-6 text-gray-300">
                <section>
                    <p class="mb-4">Manage your payment options and view your transaction history. This section allows you to add, edit, or remove payment methods for faster bookings.</p>
                    <p class="mb-4">You can review past transactions, download receipts, and set up preferred payment methods for future travel purchases.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Transaction History</h2>
                    <p class="mb-4">Your complete payment history will be displayed here, including successful transactions, refunds, and any pending payments.</p>
                    <p class="mb-4">Each transaction record includes booking details, payment method, amount paid, and transaction status for your reference.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">Accepted Payment Methods</h2>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Credit Cards (Visa, Mastercard, American Express)</li>
                        <li>Debit Cards</li>
                        <li>PayPal</li>
                        <li>Bank Transfers</li>
                        <li>Digital Wallets</li>
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
