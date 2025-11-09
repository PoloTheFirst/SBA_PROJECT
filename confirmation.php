<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

$booking_ref = $_SESSION['booking_reference'] ?? null;

if (!$booking_ref) {
    header("Location: index.php");
    exit();
}

// Clear the booking reference from session
unset($_SESSION['booking_reference']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
            color: #ffffff;
        }
        .card-dark {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="card-dark rounded-lg p-8 text-center">
            <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-feather="check" class="text-white w-10 h-10"></i>
            </div>
            
            <h1 class="text-3xl font-bold mb-4">Booking Confirmed!</h1>
            <p class="text-gray-300 mb-6">Thank you for choosing TravelGO Orbit. Your flight has been successfully booked.</p>
            
            <div class="bg-gray-800 p-6 rounded-lg mb-6">
                <h2 class="text-xl font-semibold mb-4">Booking Reference</h2>
                <div class="text-3xl font-bold text-yellow-400 mb-2"><?= htmlspecialchars($booking_ref) ?></div>
                <p class="text-gray-400 text-sm">Please keep this reference number for your records</p>
            </div>
            
            <div class="space-y-4 mb-8 text-left">
                <div class="flex justify-between">
                    <span class="text-gray-400">Status:</span>
                    <span class="text-green-400 font-semibold">Confirmed</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Booking Date:</span>
                    <span><?= date('F j, Y') ?></span>
                </div>
            </div>
            
            <div class="space-y-4">
                <a href="dashboard.php" class="block bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded transition-colors">
                    View My Bookings
                </a>
                <a href="index.php" class="block bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded transition-colors">
                    Book Another Flight
                </a>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-700">
                <p class="text-gray-400 text-sm">
                    A confirmation email has been sent to your email address with all the details of your booking.
                    Please check your spam folder if you don't see it in your inbox.
                </p>
            </div>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>