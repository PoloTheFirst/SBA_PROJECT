<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Check if user is logged in and has a valid booking reference
if (!isset($_SESSION['user_id']) || !isset($_SESSION['booking_reference'])) {
    header("Location: login.php");
    exit();
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, u.name as user_name, u.email as user_email,
           COALESCE(rtf.flight_number, 'N/A') as flight_number,
           COALESCE(rtf.airline_name, 'Unknown Airline') as airline_name,
           COALESCE(rtf.origin, 'Unknown') as origin,
           COALESCE(rtf.destination, 'Unknown') as destination,
           COALESCE(rtf.departure_date, 'N/A') as departure_date,
           COALESCE(rtf.return_date, 'N/A') as return_date,
           COALESCE(rtf.departure_time, '00:00:00') as departure_time,
           COALESCE(rtf.arrival_time, '00:00:00') as arrival_time
    FROM bookings b 
    LEFT JOIN round_trip_flights rtf ON b.flight_id = rtf.id 
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.booking_reference = ? AND b.user_id = ?
");
$stmt->execute([$_SESSION['booking_reference'], $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = "Booking not found.";
    header("Location: dashboard.php?view=bookings");
    exit();
}

// Decode JSON data
$flight_details = json_decode($booking['flight_details'] ?? '{}', true);
$passenger_info = json_decode($booking['passenger_info'] ?? '{}', true);
$billing_address = json_decode($booking['billing_address'] ?? '{}', true);

// Get transaction details
$transaction_stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE booking_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$transaction_stmt->execute([$booking['id']]);
$transaction = $transaction_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt | TravelGO Orbit</title>
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
        .print-only {
            display: none;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .card-dark {
                background: white !important;
                color: black !important;
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white py-4 no-print">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <a href="index.php" class="flex items-center space-x-2">
                    <i data-feather="navigation" class="text-yellow-400"></i>
                    <span class="text-xl font-bold">TravelGO Orbit</span>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    <i data-feather="printer" class="w-4 h-4 inline mr-1"></i>
                    Print Receipt
                </button>
                <a href="dashboard.php?view=bookings" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    My Bookings
                </a>
                <a href="index.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 px-4 py-2 rounded text-sm transition-colors font-bold">
                    Book Another Flight
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Success Message -->
        <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-6">
            <div class="flex items-center">
                <i data-feather="check-circle" class="w-6 h-6 mr-2"></i>
                <strong>Booking Confirmed!</strong> Your flight has been successfully booked.
            </div>
        </div>

        <!-- Receipt Header -->
        <div class="card-dark rounded-lg p-8 mb-6 text-center">
            <div class="print-only text-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">TravelGO Orbit</h1>
                <p class="text-gray-600">Flight Booking Receipt</p>
            </div>
            <i data-feather="check-circle" class="w-16 h-16 text-green-400 mx-auto mb-4 no-print"></i>
            <h1 class="text-3xl font-bold text-white mb-2 no-print">Booking Confirmed!</h1>
            <p class="text-gray-300 mb-4 no-print">Your flight has been successfully booked</p>
            
            <div class="bg-gray-800 rounded-lg p-6 inline-block">
                <p class="text-gray-400 text-sm">Booking Reference</p>
                <p class="text-2xl font-bold text-yellow-400"><?= htmlspecialchars($booking['booking_reference']) ?></p>
                <p class="text-gray-400 text-sm mt-2"><?= date('F j, Y \a\t g:i A', strtotime($booking['created_at'])) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Flight Details -->
                <div class="card-dark rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Flight Details</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-4 bg-gray-800 rounded">
                            <div>
                                <p class="text-gray-400">Airline</p>
                                <p class="text-white font-semibold"><?= htmlspecialchars($booking['airline_name']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400">Flight Number</p>
                                <p class="text-white font-semibold"><?= htmlspecialchars($booking['flight_number']) ?></p>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center p-4 bg-gray-800 rounded">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-white"><?= htmlspecialchars($booking['origin']) ?></p>
                                <p class="text-gray-400">Departure</p>
                                <p class="text-white"><?= date('M j, Y', strtotime($booking['departure_date'])) ?></p>
                                <p class="text-white font-semibold"><?= date('g:i A', strtotime($booking['departure_time'])) ?></p>
                            </div>
                            
                            <div class="text-center mx-4">
                                <i data-feather="arrow-right" class="w-8 h-8 text-yellow-400"></i>
                                <p class="text-gray-400 text-sm mt-1"><?= htmlspecialchars($flight_details['duration'] ?? 'N/A') ?></p>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-2xl font-bold text-white"><?= htmlspecialchars($booking['destination']) ?></p>
                                <p class="text-gray-400">Arrival</p>
                                <p class="text-white"><?= date('M j, Y', strtotime($booking['return_date'] ?? $booking['departure_date'])) ?></p>
                                <p class="text-white font-semibold"><?= date('g:i A', strtotime($booking['arrival_time'])) ?></p>
                            </div>
                        </div>

                        <?php if ($booking['flight_type'] === 'round_trip'): ?>
                        <div class="flex justify-between items-center p-4 bg-gray-800 rounded">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-white"><?= htmlspecialchars($booking['destination']) ?></p>
                                <p class="text-gray-400">Return Departure</p>
                                <p class="text-white"><?= date('M j, Y', strtotime($booking['return_date'])) ?></p>
                                <p class="text-white font-semibold"><?= date('g:i A', strtotime($flight_details['flight_data']['return_departure_time'] ?? '00:00:00')) ?></p>
                            </div>
                            
                            <div class="text-center mx-4">
                                <i data-feather="arrow-left" class="w-8 h-8 text-yellow-400"></i>
                                <p class="text-gray-400 text-sm mt-1"><?= htmlspecialchars($flight_details['return_duration'] ?? 'N/A') ?></p>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-2xl font-bold text-white"><?= htmlspecialchars($booking['origin']) ?></p>
                                <p class="text-gray-400">Return Arrival</p>
                                <p class="text-white"><?= date('M j, Y', strtotime($booking['return_date'])) ?></p>
                                <p class="text-white font-semibold"><?= date('g:i A', strtotime($flight_details['flight_data']['return_arrival_time'] ?? '00:00:00')) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Passenger Information -->
                <div class="card-dark rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Passenger Information</h2>
                    <div class="space-y-4">
                        <?php if (!empty($passenger_info)): ?>
                            <?php foreach ($passenger_info as $index => $passenger): ?>
                                <div class="bg-gray-800 rounded p-4">
                                    <h3 class="font-semibold text-yellow-400 mb-2">
                                        Passenger <?= is_numeric(substr($index, -1)) ? substr($index, -1) : $index ?>
                                        <?php if ($index === 'passenger_1' || $index === 0): ?>
                                            <span class="text-sm text-gray-400">(Primary)</span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                        <div><span class="text-gray-400">Name:</span> <span class="text-white"><?= htmlspecialchars($passenger['first_name'] ?? '') ?> <?= htmlspecialchars($passenger['last_name'] ?? '') ?></span></div>
                                        <?php if (isset($passenger['email'])): ?>
                                            <div><span class="text-gray-400">Email:</span> <span class="text-white"><?= htmlspecialchars($passenger['email']) ?></span></div>
                                        <?php endif; ?>
                                        <?php if (isset($passenger['phone'])): ?>
                                            <div><span class="text-gray-400">Phone:</span> <span class="text-white"><?= htmlspecialchars($passenger['phone']) ?></span></div>
                                        <?php endif; ?>
                                        <div><span class="text-gray-400">Gender:</span> <span class="text-white"><?= ucfirst($passenger['gender'] ?? '') ?></span></div>
                                        <?php if (isset($passenger['dob'])): ?>
                                            <div><span class="text-gray-400">Date of Birth:</span> <span class="text-white"><?= date('M j, Y', strtotime($passenger['dob'])) ?></span></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-400">No passenger information available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Booking Summary -->
                <div class="card-dark rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Booking Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status:</span>
                            <span class="px-2 py-1 bg-green-900 text-green-300 rounded text-sm font-semibold">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Flight Type:</span>
                            <span class="text-white"><?= ucfirst(str_replace('_', ' ', $booking['flight_type'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Passengers:</span>
                            <span class="text-white"><?= count($passenger_info) ?> travelers</span>
                        </div>
                        <?php if (isset($flight_details['selected_seats']) && !empty($flight_details['selected_seats'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Selected Seats:</span>
                            <span class="text-white"><?= implode(', ', $flight_details['selected_seats']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="card-dark rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Payment Summary</h2>
                    <div class="space-y-2">
                        <?php if (isset($flight_details['base_amount']) && $flight_details['base_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Flight Cost:</span>
                            <span class="text-white">HKD $<?= number_format($flight_details['base_amount'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($flight_details['tax_amount']) && $flight_details['tax_amount'] > 0): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Taxes & Fees:</span>
                            <span class="text-white">HKD $<?= number_format($flight_details['tax_amount'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($flight_details['seat_charges']) && $flight_details['seat_charges'] > 0): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Seat Selection:</span>
                            <span class="text-white">HKD $<?= number_format($flight_details['seat_charges'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between font-bold text-lg border-t border-gray-600 pt-3 mt-2">
                            <span class="text-white">Total Paid:</span>
                            <span class="text-yellow-400">HKD $<?= number_format($booking['total_amount'], 2) ?></span>
                        </div>
                        
                        <?php if ($transaction): ?>
                        <div class="flex justify-between text-sm mt-3 pt-3 border-t border-gray-600">
                            <span class="text-gray-400">Transaction ID:</span>
                            <span class="text-gray-300"><?= htmlspecialchars($transaction['transaction_id']) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Payment Method:</span>
                            <span class="text-gray-300"><?= ucfirst(str_replace('_', ' ', $booking['payment_method'])) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Payment Status:</span>
                            <span class="text-green-300"><?= ucfirst($transaction['status']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card-dark rounded-lg p-6 no-print">
                    <h2 class="text-xl font-bold mb-4">What's Next?</h2>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <i data-feather="mail" class="w-5 h-5 text-yellow-400 mt-0.5"></i>
                            <div>
                                <p class="font-semibold text-white">Email Confirmation</p>
                                <p class="text-gray-400 text-sm">Check your email for booking details</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-feather="clock" class="w-5 h-5 text-yellow-400 mt-0.5"></i>
                            <div>
                                <p class="font-semibold text-white">Check-in</p>
                                <p class="text-gray-400 text-sm">Online check-in opens 24 hours before departure</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <i data-feather="download" class="w-5 h-5 text-yellow-400 mt-0.5"></i>
                            <div>
                                <p class="font-semibold text-white">Boarding Pass</p>
                                <p class="text-gray-400 text-sm">Download your boarding pass after check-in</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center space-x-4 mt-8 no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded transition-colors">
                <i data-feather="printer" class="w-4 h-4 inline mr-2"></i>
                Print Receipt
            </button>
            <a href="dashboard.php?view=bookings" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded transition-colors">
                <i data-feather="briefcase" class="w-4 h-4 inline mr-2"></i>
                View My Bookings
            </a>
            <a href="index.php" class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded transition-colors">
                <i data-feather="plus" class="w-4 h-4 inline mr-2"></i>
                Book Another Flight
            </a>
        </div>
    </div>

    <script>
        feather.replace();
        
        // Auto-hide success message after 5 seconds
        setTimeout(() => {
            const successMessage = document.querySelector('.bg-green-900');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>