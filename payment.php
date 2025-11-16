<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Luhn algorithm validation function
function isValidLuhn($number)
{
    $number = preg_replace('/\D/', '', $number);

    // Check if it's 13-19 digits
    if (!preg_match('/^\d{13,19}$/', $number)) {
        return false;
    }

    $sum = 0;
    $length = strlen($number);
    $parity = $length % 2;

    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$number[$i];

        if ($i % 2 == $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }

        $sum += $digit;
    }

    return ($sum % 10) === 0;
}

// Get user data for pre-filling forms
$user_stmt = $pdo->prepare("
    SELECT u.*, c.name as city_name, co.name as country_name, s.name as state_name
    FROM users u 
    LEFT JOIN cities c ON u.city_id = c.id 
    LEFT JOIN countries co ON c.country_id = co.id 
    LEFT JOIN states s ON u.state_id = s.id
    WHERE u.id = ?
");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();

// Initialize payment session
if (!isset($_SESSION['payment_data'])) {
    $_SESSION['payment_data'] = [];
}

$step = $_GET['step'] ?? 1;
$flight_id = $_GET['flight_id'] ?? ($_SESSION['payment_data']['flight_id'] ?? null);
$flight_type = $_GET['flight_type'] ?? ($_SESSION['payment_data']['flight_type'] ?? 'one_way');
$passengers = $_GET['passengers'] ?? ($_SESSION['payment_data']['passengers'] ?? null);

if ($flight_id && $flight_type === 'round_trip') {
    $flight_stmt = $pdo->prepare("SELECT * FROM round_trip_flights WHERE id = ?");
    $flight_stmt->execute([$flight_id]);
    $flight_details = $flight_stmt->fetch();

    // Calculate total amount (flight price * passengers + 3% tax)
    if ($flight_details) {
        $base_amount = $flight_details['price'] * $passengers;
        $tax_amount = $base_amount * 0.03;
        $total_amount = $base_amount + $tax_amount;

        $_SESSION['payment_data']['base_amount'] = $base_amount;
        $_SESSION['payment_data']['tax_amount'] = $tax_amount;
        $_SESSION['payment_data']['total_amount'] = $total_amount;
        $_SESSION['payment_data']['flight_details'] = $flight_details;
    }
}

// Store basic flight info in session
if ($flight_id) {
    $_SESSION['payment_data']['flight_id'] = $flight_id;
    $_SESSION['payment_data']['flight_type'] = $flight_type;
    $_SESSION['payment_data']['passengers'] = $passengers;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_step') {
        $step_data = $_POST['step_data'] ?? [];

        // Save step data to session
        foreach ($step_data as $key => $value) {
            $_SESSION['payment_data'][$key] = $value;
        }

        // Check if this is the final step (5)
        if ($step == 5) {
            // Process the payment and move to receipt
            if (processPayment()) {
                header("Location: payment.php?step=6");
                exit();
            } else {
                // If payment processing fails, stay on step 5
                header("Location: payment.php?step=5&error=payment_failed");
                exit();
            }
        }

        // For other steps, move to next step
        $step = min($step + 1, 6);
        header("Location: payment.php?step=$step");
        exit();
    } elseif ($action === 'previous_step') {
        $step = max($step - 1, 1);
        header("Location: payment.php?step=$step");
        exit();
    } elseif ($action === 'process_payment') {
        // Process the final payment
        if (processPayment()) {
            header("Location: payment.php?step=6");
            exit();
        } else {
            header("Location: payment.php?step=5&error=payment_failed");
            exit();
        }
    } elseif ($action === 'apply_coupon') {
        // Handle coupon application
        $coupon_code = $_POST['coupon_code'] ?? '';
        $coupon_result = applyCoupon($coupon_code);

        if ($coupon_result['success']) {
            $_SESSION['payment_data']['applied_coupon'] = $coupon_result['coupon'];
            $_SESSION['payment_data']['discount_amount'] = $coupon_result['discount_amount'];
            $_SESSION['success'] = "Coupon applied successfully! Discount: HKD $" . number_format($coupon_result['discount_amount'], 2);
        } else {
            $_SESSION['error'] = $coupon_result['message'];
        }

        header("Location: payment.php?step=5");
        exit();
    } elseif ($action === 'remove_coupon') {
        // Remove applied coupon
        unset($_SESSION['payment_data']['applied_coupon']);
        unset($_SESSION['payment_data']['discount_amount']);
        $_SESSION['success'] = "Coupon removed successfully.";

        header("Location: payment.php?step=5");
        exit();
    }
}

function applyCoupon($coupon_code)
{
    global $pdo;

    // Validate coupon code
    if (empty($coupon_code)) {
        return ['success' => false, 'message' => 'Please enter a coupon code.'];
    }

    // Check if user has already used this coupon
    $used_coupon_stmt = $pdo->prepare("
        SELECT * FROM user_coupons 
        WHERE user_id = ? AND coupon_id IN (SELECT id FROM coupons WHERE code = ?)
    ");
    $used_coupon_stmt->execute([$_SESSION['user_id'], $coupon_code]);

    if ($used_coupon_stmt->fetch()) {
        return ['success' => false, 'message' => 'This coupon has already been used.'];
    }

    // Get coupon details
    $coupon_stmt = $pdo->prepare("
        SELECT * FROM coupons 
        WHERE code = ? AND is_active = 1 AND valid_from <= NOW() AND valid_until >= NOW()
    ");
    $coupon_stmt->execute([$coupon_code]);
    $coupon = $coupon_stmt->fetch();

    if (!$coupon) {
        return ['success' => false, 'message' => 'Invalid or expired coupon code.'];
    }

    // Check if user is eligible (for new user coupons)
    if ($coupon['for_new_users']) {
        $user_bookings_stmt = $pdo->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE user_id = ?");
        $user_bookings_stmt->execute([$_SESSION['user_id']]);
        $user_bookings = $user_bookings_stmt->fetch();

        if ($user_bookings['booking_count'] > 0) {
            return ['success' => false, 'message' => 'This coupon is only for new users.'];
        }
    }

    // Check minimum amount requirement
    $base_amount = $_SESSION['payment_data']['base_amount'] ?? 0;
    if ($base_amount < $coupon['min_amount']) {
        return ['success' => false, 'message' => 'Minimum amount requirement not met for this coupon.'];
    }

    // Calculate discount amount
    $discount_amount = 0;
    if ($coupon['discount_type'] === 'percentage') {
        $discount_amount = $base_amount * ($coupon['discount_value'] / 100);
        // Apply maximum discount limit if set
        if ($coupon['max_discount'] && $discount_amount > $coupon['max_discount']) {
            $discount_amount = $coupon['max_discount'];
        }
    } else {
        $discount_amount = $coupon['discount_value'];
    }

    return [
        'success' => true,
        'coupon' => $coupon,
        'discount_amount' => $discount_amount
    ];
}

function processPayment()
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Validate card number if credit/debit card is used
        if (in_array($_SESSION['payment_data']['payment_method'], ['credit_card', 'debit_card'])) {
            // In a real application, you would validate the actual card number here
            // For demo purposes, we'll just log that we would validate it
            error_log("Card payment method selected - would validate card number in production");
        }

        // Generate booking reference
        $booking_ref = 'TG' . strtoupper(bin2hex(random_bytes(5)));

        // Calculate final total including seat charges and discount
        $base_amount = $_SESSION['payment_data']['base_amount'] ?? 0;
        $tax_amount = $_SESSION['payment_data']['tax_amount'] ?? 0;
        $seat_charges = $_SESSION['payment_data']['seat_charges'] ?? 0;
        $discount_amount = $_SESSION['payment_data']['discount_amount'] ?? 0;

        $final_total = ($base_amount + $tax_amount + $seat_charges) - $discount_amount;
        if ($final_total < 0) $final_total = 0; // Ensure total doesn't go negative

        // Create booking record
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, booking_reference, flight_type, flight_id, flight_details, passenger_info, billing_address, payment_method, total_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
        ");

        $user_id = $_SESSION['user_id'] ?? null;

        // Prepare flight details with seat information and coupon
        $flight_details_json = json_encode([
            'flight_id' => $_SESSION['payment_data']['flight_id'],
            'flight_type' => $_SESSION['payment_data']['flight_type'],
            'flight_data' => $_SESSION['payment_data']['flight_details'] ?? [],
            'passengers' => $_SESSION['payment_data']['passengers'],
            'selected_seats' => $_SESSION['payment_data']['selected_seats'] ?? [],
            'seat_charges' => $_SESSION['payment_data']['seat_charges'] ?? 0,
            'base_amount' => $_SESSION['payment_data']['base_amount'] ?? 0,
            'tax_amount' => $_SESSION['payment_data']['tax_amount'] ?? 0,
            'applied_coupon' => $_SESSION['payment_data']['applied_coupon'] ?? null,
            'discount_amount' => $_SESSION['payment_data']['discount_amount'] ?? 0,
            'duration' => $_SESSION['payment_data']['flight_details']['duration'] ?? '',
            'return_duration' => $_SESSION['payment_data']['flight_details']['return_duration'] ?? ''
        ], JSON_UNESCAPED_UNICODE);

        $passenger_info_json = json_encode($_SESSION['payment_data']['passenger_info'] ?? [], JSON_UNESCAPED_UNICODE);
        $billing_address_json = json_encode($_SESSION['payment_data']['billing_address'] ?? [], JSON_UNESCAPED_UNICODE);

        // Debug logging
        error_log("Processing payment for user: $user_id");
        error_log("Booking reference: $booking_ref");
        error_log("Final total: $final_total");
        error_log("Discount applied: $discount_amount");

        $stmt->execute([
            $user_id,
            $booking_ref,
            $_SESSION['payment_data']['flight_type'],
            $_SESSION['payment_data']['flight_id'],
            $flight_details_json,
            $passenger_info_json,
            $billing_address_json,
            $_SESSION['payment_data']['payment_method'],
            $final_total
        ]);

        $booking_id = $pdo->lastInsertId();
        error_log("Booking created with ID: $booking_id");

        // Create transaction record
        $transaction_id = 'TXN' . strtoupper(bin2hex(random_bytes(8)));
        $stmt = $pdo->prepare("
            INSERT INTO transactions (booking_id, transaction_id, amount, currency, payment_method, status) 
            VALUES (?, ?, ?, 'HKD', ?, 'success')
        ");

        $stmt->execute([
            $booking_id,
            $transaction_id,
            $final_total,
            $_SESSION['payment_data']['payment_method']
        ]);

        error_log("Transaction created: $transaction_id");

        // Record coupon usage if applied
        if (isset($_SESSION['payment_data']['applied_coupon'])) {
            $coupon = $_SESSION['payment_data']['applied_coupon'];
            $coupon_usage_stmt = $pdo->prepare("
        INSERT INTO user_coupons (user_id, coupon_id, booking_id, used_at) 
        VALUES (?, ?, ?, NOW())
    ");
            $coupon_usage_stmt->execute([
                $_SESSION['user_id'],
                $coupon['id'],
                $booking_id
            ]);

            // Also mark the user_offers entry as used
            $update_offer_stmt = $pdo->prepare("
        UPDATE user_offers 
        SET is_used = 1, used_at = NOW(), booking_id = ?
        WHERE user_id = ? AND offer_code = ? AND is_used = 0
    ");
            $update_offer_stmt->execute([
                $booking_id,
                $_SESSION['user_id'],
                $coupon['code']
            ]);

            error_log("Coupon usage recorded for coupon ID: " . $coupon['id']);
        }

        // Update flight seats (for round trip flights)
        if ($_SESSION['payment_data']['flight_type'] === 'round_trip' && isset($_SESSION['payment_data']['flight_id'])) {
            $stmt = $pdo->prepare("
                UPDATE round_trip_flights 
                SET seats_available = seats_available - ? 
                WHERE id = ? AND seats_available >= ?
            ");
            $stmt->execute([
                $_SESSION['payment_data']['passengers'],
                $_SESSION['payment_data']['flight_id'],
                $_SESSION['payment_data']['passengers']
            ]);

            // Check if update was successful
            if ($stmt->rowCount() === 0) {
                throw new Exception("Not enough seats available");
            }
            error_log("Flight seats updated");
        }

        $pdo->commit();
        error_log("Database transaction committed successfully");

        // Store booking reference for receipt page
        $_SESSION['booking_reference'] = $booking_ref;
        $_SESSION['booking_id'] = $booking_id;
        $_SESSION['transaction_id'] = $transaction_id;
        $_SESSION['final_total'] = $final_total;

        // Store complete booking data for receipt
        $_SESSION['booking_data'] = [
            'booking_reference' => $booking_ref,
            'booking_id' => $booking_id,
            'transaction_id' => $transaction_id,
            'total_amount' => $final_total,
            'flight_details' => $_SESSION['payment_data']['flight_details'] ?? [],
            'passenger_info' => $_SESSION['payment_data']['passenger_info'] ?? [],
            'selected_seats' => $_SESSION['payment_data']['selected_seats'] ?? [],
            'booking_date' => date('Y-m-d H:i:s'),
            'base_amount' => $_SESSION['payment_data']['base_amount'] ?? 0,
            'tax_amount' => $_SESSION['payment_data']['tax_amount'] ?? 0,
            'seat_charges' => $_SESSION['payment_data']['seat_charges'] ?? 0,
            'applied_coupon' => $_SESSION['payment_data']['applied_coupon'] ?? null,
            'discount_amount' => $_SESSION['payment_data']['discount_amount'] ?? 0
        ];

        error_log("Payment processed successfully, ready for receipt step");

        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Payment processing error: " . $e->getMessage());
        error_log("Error in file: " . $e->getFile() . " on line: " . $e->getLine());
        $_SESSION['payment_error'] = "Payment processing failed. Please try again. Error: " . $e->getMessage();
        return false;
    }
}

function generateBookingSummary()
{
    // Use session data instead of database query for immediate access
    if (!isset($_SESSION['booking_data'])) {
        return null;
    }

    return $_SESSION['booking_data'];
}

function displayIntegratedReceipt()
{
    // Try to get booking data from session first
    $bookingSummary = generateBookingSummary();

    // If not available, try to reconstruct from payment_data session
    if (!$bookingSummary && isset($_SESSION['payment_data'])) {
        $paymentData = $_SESSION['payment_data'];
        $bookingSummary = [
            'booking_reference' => $_SESSION['booking_reference'] ?? 'N/A',
            'transaction_id' => $_SESSION['transaction_id'] ?? 'N/A',
            'booking_date' => $_SESSION['booking_data']['booking_date'] ?? date('Y-m-d H:i:s'),
            'total_amount' => $_SESSION['final_total'] ?? (($paymentData['total_amount'] ?? 0) + ($paymentData['seat_charges'] ?? 0)),
            'flight_details' => $paymentData['flight_details'] ?? [],
            'passenger_info' => $paymentData['passenger_info'] ?? [],
            'selected_seats' => $paymentData['selected_seats'] ?? [],
            'base_amount' => $paymentData['base_amount'] ?? 0,
            'tax_amount' => $paymentData['tax_amount'] ?? 0,
            'seat_charges' => $paymentData['seat_charges'] ?? 0,
            'applied_coupon' => $paymentData['applied_coupon'] ?? null,
            'discount_amount' => $paymentData['discount_amount'] ?? 0
        ];
    }

    if (!$bookingSummary) {
        return '<div class="bg-red-900 text-white p-4 rounded">Receipt data not available. Please try again.</div>';
    }

    $flight = $bookingSummary['flight_details'];
    $passengers = $bookingSummary['passenger_info'];

    // Handle selected seats if it's a string
    $selectedSeats = $bookingSummary['selected_seats'] ?? [];
    if (is_string($selectedSeats)) {
        $selectedSeats = json_decode($selectedSeats, true) ?? [];
    }
    if (!is_array($selectedSeats)) {
        $selectedSeats = [];
    }

    ob_start(); ?>
    <div class="receipt-container bg-white text-gray-900 rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-green-600">Booking Confirmed!</h2>
            <p class="text-gray-600">Your flight has been successfully booked</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-semibold mb-2">Booking Details</h3>
                <p><strong>Reference:</strong> <?= htmlspecialchars($bookingSummary['booking_reference'] ?? 'N/A') ?></p>
                <p><strong>Transaction ID:</strong> <?= htmlspecialchars($bookingSummary['transaction_id'] ?? 'N/A') ?></p>
                <p><strong>Booking Date:</strong> <?= date('F j, Y g:i A', strtotime($bookingSummary['booking_date'] ?? 'now')) ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-semibold mb-2">Flight Information</h3>
                <p><strong>Airline:</strong> <?= htmlspecialchars($flight['airline_name'] ?? 'Unknown') ?></p>
                <p><strong>Flight:</strong> <?= htmlspecialchars($flight['flight_number'] ?? 'N/A') ?></p>
                <p><strong>Route:</strong> <?= htmlspecialchars($flight['origin'] ?? 'Unknown') ?> → <?= htmlspecialchars($flight['destination'] ?? 'Unknown') ?></p>
                <?php if (isset($flight['departure_date'])): ?>
                    <p><strong>Travel Dates:</strong> <?= date('M j, Y', strtotime($flight['departure_date'])) ?> - <?= date('M j, Y', strtotime($flight['return_date'] ?? $flight['departure_date'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Passenger Information -->
        <div class="bg-gray-50 p-4 rounded mb-6">
            <h3 class="font-semibold mb-2">Passenger Information</h3>
            <?php if (is_array($passengers) && !empty($passengers)): ?>
                <?php foreach ($passengers as $index => $passenger): ?>
                    <div class="mb-4 pb-4 border-b border-gray-300 last:border-b-0 last:mb-0 last:pb-0">
                        <h4 class="font-semibold text-gray-800 mb-2">Passenger <?= $index === 'passenger_1' ? '1 (Primary)' : substr($index, -1) ?></h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <div><span class="text-gray-600">Name:</span> <span class="text-gray-900"><?= htmlspecialchars($passenger['first_name'] ?? '') ?> <?= htmlspecialchars($passenger['last_name'] ?? '') ?></span></div>
                            <?php if (isset($passenger['email'])): ?>
                                <div><span class="text-gray-600">Email:</span> <span class="text-gray-900"><?= htmlspecialchars($passenger['email']) ?></span></div>
                            <?php endif; ?>
                            <?php if (isset($passenger['phone'])): ?>
                                <div><span class="text-gray-600">Phone:</span> <span class="text-gray-900"><?= htmlspecialchars($passenger['phone']) ?></span></div>
                            <?php endif; ?>
                            <div><span class="text-gray-600">Gender:</span> <span class="text-gray-900"><?= ucfirst($passenger['gender'] ?? '') ?></span></div>
                            <?php if (isset($passenger['dob'])): ?>
                                <div><span class="text-gray-600">Date of Birth:</span> <span class="text-gray-900"><?= date('M j, Y', strtotime($passenger['dob'])) ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No passenger information available.</p>
            <?php endif; ?>
        </div>

        <!-- Selected Seats -->
        <?php if (!empty($selectedSeats)): ?>
            <div class="bg-gray-50 p-4 rounded mb-6">
                <h3 class="font-semibold mb-2">Selected Seats</h3>
                <p class="text-gray-900"><?= is_array($selectedSeats) ? implode(', ', $selectedSeats) : htmlspecialchars($selectedSeats) ?></p>
            </div>
        <?php endif; ?>

        <!-- Billing Address -->
        <?php if (isset($_SESSION['payment_data']['billing_address'])): ?>
            <div class="bg-gray-50 p-4 rounded mb-6">
                <h3 class="font-semibold mb-2">Billing Address</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-600">Street:</span> <span class="text-gray-900"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['street']) ?></span></div>
                    <div><span class="text-gray-600">City:</span> <span class="text-gray-900"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['city']) ?></span></div>
                    <div><span class="text-gray-600">State/Province:</span> <span class="text-gray-900"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['state']) ?></span></div>
                    <div><span class="text-gray-600">ZIP/Postal Code:</span> <span class="text-gray-900"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['zip']) ?></span></div>
                    <div class="md:col-span-2"><span class="text-gray-600">Country:</span> <span class="text-gray-900"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['country']) ?></span></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Method -->
        <?php if (isset($_SESSION['payment_data']['payment_method'])): ?>
            <div class="bg-gray-50 p-4 rounded mb-6">
                <h3 class="font-semibold mb-2">Payment Method</h3>
                <p class="text-gray-900"><?= ucfirst(str_replace('_', ' ', $_SESSION['payment_data']['payment_method'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Applied Coupon -->
        <?php if (isset($bookingSummary['applied_coupon'])): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
                <h3 class="font-semibold mb-2 text-green-800">Applied Coupon</h3>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-700 font-semibold"><?= htmlspecialchars($bookingSummary['applied_coupon']['code']) ?></p>
                        <p class="text-green-600 text-sm"><?= htmlspecialchars($bookingSummary['applied_coupon']['description']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-green-700 font-bold">-HKD $<?= number_format($bookingSummary['discount_amount'], 2) ?></p>
                        <p class="text-green-600 text-sm">Discount Applied</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Payment Summary -->
        <div class="bg-gray-50 p-4 rounded mb-6">
            <h3 class="font-semibold mb-2">Payment Summary</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Flight Cost:</span>
                    <span>HKD $<?= number_format($bookingSummary['base_amount'] ?? 0, 2) ?></span>
                </div>
                <?php if (isset($bookingSummary['tax_amount']) && $bookingSummary['tax_amount'] > 0): ?>
                    <div class="flex justify-between">
                        <span>Taxes & Fees (3%):</span>
                        <span>HKD $<?= number_format($bookingSummary['tax_amount'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (($bookingSummary['seat_charges'] ?? 0) > 0): ?>
                    <div class="flex justify-between">
                        <span>Seat Selection Charges:</span>
                        <span>HKD $<?= number_format($bookingSummary['seat_charges'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (($bookingSummary['discount_amount'] ?? 0) > 0): ?>
                    <div class="flex justify-between text-green-600">
                        <span>Coupon Discount:</span>
                        <span>-HKD $<?= number_format($bookingSummary['discount_amount'] ?? 0, 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between font-bold text-lg border-t border-gray-600 pt-3 mt-2">
                    <span>Total Amount:</span>
                    <span class="text-green-600">HKD $<?= number_format($bookingSummary['total_amount'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Important:</strong> Your booking confirmation has been sent to your email.
                        Please present this receipt at check-in.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mt-8 pt-6 border-t">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Print Receipt
            </button>
            <a href="dashboard.php?view=bookings" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                View in Dashboard
            </a>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function generateSeatMap($rows = 10, $cols = 6)
{
    $seats = [];
    $premium_rows = [1, 2]; // First 2 rows are premium
    $occupied_seats = ['1A', '1C', '2B', '3D', '4F', '5A', '7C', '8E']; // Simulate some occupied seats

    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 0; $col < $cols; $col++) {
            $seat_letter = chr(65 + $col); // A, B, C, etc.
            $seat_number = $row . $seat_letter;

            $type = 'available';
            $price = 0;

            if (in_array($seat_number, $occupied_seats)) {
                $type = 'occupied';
            } elseif (in_array($row, $premium_rows)) {
                $type = 'premium';
                $price = 50;
            }

            $seats[] = [
                'number' => $seat_number,
                'type' => $type,
                'price' => $price,
                'row' => $row,
                'col' => $col
            ];
        }
    }

    return $seats;
}

$seat_map = generateSeatMap();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Process | TravelGO Orbit</title>
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

        .progress-step {
            background: rgba(255, 255, 255, 0.1);
        }

        .progress-step.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1e3a8a;
        }

        .progress-step.completed {
            background: #10b981;
            color: white;
        }

        .loading-spinner {
            display: none;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #f59e0b;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        .use-profile-data {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid #3b82f6;
        }

        /* Seat Map Styles */
        .seat {
            width: 40px;
            height: 40px;
            margin: 4px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .seat.available {
            background-color: #f59e0b;
            color: #1e3a8a;
        }

        .seat.available:hover {
            background-color: #d97706;
        }

        .seat.premium {
            background-color: #b45309;
            color: white;
        }

        .seat.premium:hover {
            background-color: #92400e;
        }

        .seat.occupied {
            background-color: #6b7280;
            color: #9ca3af;
            cursor: not-allowed;
            position: relative;
        }

        .seat.occupied::after {
            content: "✕";
            position: absolute;
            font-size: 1.2em;
            color: #ef4444;
        }

        .seat.selected {
            background-color: #10b981;
            color: white;
        }

        .seat-map-container {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            max-width: 300px;
            margin: 0 auto;
        }

        .airplane-cabin {
            background: #374151;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }

        .cabin-label {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: #f59e0b;
        }

        /* Receipt Styles */
        .receipt-container {
            background: white;
            color: #1f2937;
        }

        /* Coupon Styles */
        .coupon-section {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .coupon-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .coupon-input {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            color: #000000;
        }

        .coupon-btn {
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .coupon-btn:hover {
            background: #1d4ed8;
        }

        .coupon-btn.remove {
            background: #dc2626;
        }

        .coupon-btn.remove:hover {
            background: #b91c1c;
        }

        .applied-coupon {
            background: #10b981;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .coupon-error {
            background: #ef4444;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 14px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .receipt-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                background: white !important;
                color: black !important;
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
                <a href="search_results_roundtrip.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    <i data-feather="arrow-left" class="w-4 h-4 inline mr-1"></i>
                    Back to Search
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition-colors">
                        Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Progress Indicator - Updated for 6 steps -->
        <div class="card-dark rounded-lg p-6 mb-8 no-print">
            <div class="flex justify-between items-center">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="flex items-center">
                        <div class="progress-step w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 <?= $i == $step ? 'border-yellow-500 bg-yellow-500 text-blue-900' : ($i < $step ? 'border-green-500 bg-green-500 text-white' : 'border-gray-500 bg-gray-700 text-gray-300') ?>">
                            <?= $i < $step ? '<i data-feather="check"></i>' : $i ?>
                        </div>
                        <?php if ($i < 6): ?>
                            <div class="w-12 h-1 <?= $i < $step ? 'bg-green-500' : 'bg-gray-600' ?> mx-2"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="flex justify-between mt-4 text-sm text-gray-400">
                <span class="<?= $step >= 1 ? 'text-yellow-400 font-semibold' : '' ?>">Passenger</span>
                <span class="<?= $step >= 2 ? 'text-yellow-400 font-semibold' : '' ?>">Billing</span>
                <span class="<?= $step >= 3 ? 'text-yellow-400 font-semibold' : '' ?>">Payment</span>
                <span class="<?= $step >= 4 ? 'text-yellow-400 font-semibold' : '' ?>">Seats</span>
                <span class="<?= $step >= 5 ? 'text-yellow-400 font-semibold' : '' ?>">Confirm</span>
                <span class="<?= $step >= 6 ? 'text-yellow-400 font-semibold' : '' ?>">Receipt</span>
            </div>
        </div>

        <!-- Error Message -->
        <?php if (isset($_SESSION['payment_error'])): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4 no-print">
                <?= $_SESSION['payment_error'] ?>
                <?php unset($_SESSION['payment_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-4 no-print">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4 no-print">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Step Content -->
        <div class="<?= $step == 6 ? '' : 'card-dark rounded-lg p-6 no-print' ?>" id="step-content">
            <?php if ($step < 6): ?>
                <?php if ($step < 5): ?>
                    <form method="POST" id="payment-form">
                        <input type="hidden" name="action" value="save_step">
                    <?php endif; ?>

                    <?php
                    switch ($step) {
                        case 1: ?>
                            <!-- Step 1: Passenger Information -->
                            <h2 class="text-2xl font-bold mb-6">Passenger Information</h2>

                            <!-- Use Profile Data Button -->
                            <?php if (isset($_SESSION['user_id']) && $user): ?>
                                <div class="use-profile-data p-4 rounded-lg mb-6">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="font-semibold text-blue-300">Use your profile information?</h3>
                                            <p class="text-blue-200 text-sm">We can pre-fill your details from your profile</p>
                                        </div>
                                        <button type="button" id="use-profile-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                            Use My Profile Data
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="space-y-4">
                                <?php for ($i = 1; $i <= $passengers; $i++): ?>
                                    <div class="border-b border-gray-700 pb-4 mb-4">
                                        <h3 class="text-lg font-semibold mb-3">
                                            Passenger <?= $i ?>
                                            <?php if ($i == 1): ?>
                                                <span class="text-sm text-yellow-400">(Primary Passenger)</span>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-gray-300 mb-2">First Name</label>
                                                <input type="text" name="step_data[passenger_info][passenger_<?= $i ?>][first_name]"
                                                    value="<?= htmlspecialchars($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['first_name'] ?? '') ?>"
                                                    class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                                <div class="error-message" id="first_name_error_<?= $i ?>">Please enter first name</div>
                                            </div>
                                            <div>
                                                <label class="block text-gray-300 mb-2">Last Name</label>
                                                <input type="text" name="step_data[passenger_info][passenger_<?= $i ?>][last_name]"
                                                    value="<?= htmlspecialchars($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['last_name'] ?? '') ?>"
                                                    class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                                <div class="error-message" id="last_name_error_<?= $i ?>">Please enter last name</div>
                                            </div>

                                            <?php if ($i == 1): ?>
                                                <!-- Primary passenger additional fields -->
                                                <div>
                                                    <label class="block text-gray-300 mb-2">Email</label>
                                                    <input type="email" name="step_data[passenger_info][passenger_<?= $i ?>][email]"
                                                        value="<?= htmlspecialchars($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['email'] ?? '') ?>"
                                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                                    <div class="error-message" id="email_error_<?= $i ?>">Please enter a valid email address</div>
                                                </div>
                                                <div>
                                                    <label class="block text-gray-300 mb-2">Phone</label>
                                                    <input type="tel" name="step_data[passenger_info][passenger_<?= $i ?>][phone]"
                                                        value="<?= htmlspecialchars($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['phone'] ?? '') ?>"
                                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                                    <div class="error-message" id="phone_error_<?= $i ?>">Please enter a valid phone number</div>
                                                </div>
                                                <div>
                                                    <label class="block text-gray-300 mb-2">Gender</label>
                                                    <select name="step_data[passenger_info][passenger_<?= $i ?>][gender]" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                                        <option value="">Select Gender</option>
                                                        <option value="male" <?= ($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                                        <option value="female" <?= ($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                                        <option value="other" <?= ($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                                    </select>
                                                    <div class="error-message" id="gender_error_<?= $i ?>">Please select gender</div>
                                                </div>
                                                <div>
                                                    <label class="block text-gray-300 mb-2">Date of Birth</label>
                                                    <input type="date" name="step_data[passenger_info][passenger_<?= $i ?>][dob]"
                                                        value="<?= htmlspecialchars($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['dob'] ?? '') ?>"
                                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white"
                                                        max="<?= date('Y-m-d', strtotime('-18 years')) ?>" required>
                                                    <div class="error-message" id="dob_error_<?= $i ?>">You must be at least 18 years old</div>
                                                </div>
                                            <?php else: ?>
                                                <!-- Other passengers - only name and gender -->
                                                <div class="md:col-span-2">
                                                    <label class="block text-gray-300 mb-2">Gender</label>
                                                    <select name="step_data[passenger_info][passenger_<?= $i ?>][gender]" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                                        <option value="">Select Gender</option>
                                                        <option value="male" <?= ($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                                        <option value="female" <?= ($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                                        <option value="other" <?= ($_SESSION['payment_data']['passenger_info']['passenger_' . $i]['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                                    </select>
                                                    <div class="error-message" id="gender_error_<?= $i ?>">Please select gender</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php
                            break;

                        case 2: ?>
                            <!-- Step 2: Billing Address -->
                            <h2 class="text-2xl font-bold mb-6">Billing Address</h2>

                            <!-- Use Profile Data Button -->
                            <?php if (isset($_SESSION['user_id']) && $user): ?>
                                <div class="use-profile-data p-4 rounded-lg mb-6">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="font-semibold text-blue-300">Use your profile address?</h3>
                                            <p class="text-blue-200 text-sm">We can pre-fill your billing address from your profile</p>
                                        </div>
                                        <button type="button" id="use-billing-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors">
                                            Use My Address
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-300 mb-2">Street Address</label>
                                        <input type="text" name="step_data[billing_address][street]"
                                            value="<?= htmlspecialchars($_SESSION['payment_data']['billing_address']['street'] ?? ($user['address'] ?? '')) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                        <div class="error-message" id="street_error">Please enter a valid street address</div>
                                    </div>
                                    <div>
                                        <label class="block text-gray-300 mb-2">City</label>
                                        <input type="text" name="step_data[billing_address][city]"
                                            value="<?= htmlspecialchars($_SESSION['payment_data']['billing_address']['city'] ?? ($user['city_name'] ?? '')) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                        <div class="error-message" id="city_error">Please enter a valid city</div>
                                    </div>
                                    <div>
                                        <label class="block text-gray-300 mb-2">State/Province</label>
                                        <input type="text" name="step_data[billing_address][state]" id="billing_state"
                                            value="<?= htmlspecialchars($_SESSION['payment_data']['billing_address']['state'] ?? ($user['state_name'] ?? '')) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                        <div class="error-message" id="state_error">Please enter a state/province</div>
                                    </div>
                                    <div>
                                        <label class="block text-gray-300 mb-2">ZIP/Postal Code</label>
                                        <input type="text" name="step_data[billing_address][zip]"
                                            value="<?= htmlspecialchars($_SESSION['payment_data']['billing_address']['zip'] ?? ($user['postal_code'] ?? '')) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                        <div class="error-message" id="zip_error">Please enter a valid ZIP/postal code</div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-300 mb-2">Country</label>
                                        <input type="text" name="step_data[billing_address][country]"
                                            value="<?= htmlspecialchars($_SESSION['payment_data']['billing_address']['country'] ?? ($user['country_name'] ?? '')) ?>"
                                            class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded text-white" required>
                                        <div class="error-message" id="country_error">Please enter a valid country</div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            break;

                        case 3: ?>
                            <!-- Step 3: Payment Method -->
                            <h2 class="text-2xl font-bold mb-6">Payment Method</h2>
                            <div class="space-y-4">
                                <div class="bg-yellow-900 border border-yellow-700 text-yellow-200 px-4 py-3 rounded mb-4">
                                    <strong>Demo Notice:</strong> This is a simulation. No real payment will be processed.
                                </div>

                                <div class="space-y-3">
                                    <label class="flex items-center space-x-3 p-4 border border-gray-600 rounded hover:bg-gray-800 cursor-pointer">
                                        <input type="radio" name="step_data[payment_method]" value="credit_card"
                                            <?= ($_SESSION['payment_data']['payment_method'] ?? '') === 'credit_card' ? 'checked' : '' ?> class="text-yellow-500" required>
                                        <i data-feather="credit-card" class="text-gray-400"></i>
                                        <span>Credit Card</span>
                                    </label>

                                    <label class="flex items-center space-x-3 p-4 border border-gray-600 rounded hover:bg-gray-800 cursor-pointer">
                                        <input type="radio" name="step_data[payment_method]" value="debit_card"
                                            <?= ($_SESSION['payment_data']['payment_method'] ?? '') === 'debit_card' ? 'checked' : '' ?> class="text-yellow-500">
                                        <i data-feather="credit-card" class="text-gray-400"></i>
                                        <span>Debit Card</span>
                                    </label>

                                    <label class="flex items-center space-x-3 p-4 border border-gray-600 rounded hover:bg-gray-800 cursor-pointer">
                                        <input type="radio" name="step_data[payment_method]" value="paypal"
                                            <?= ($_SESSION['payment_data']['payment_method'] ?? '') === 'paypal' ? 'checked' : '' ?> class="text-yellow-500">
                                        <i data-feather="dollar-sign" class="text-gray-400"></i>
                                        <span>PayPal</span>
                                    </label>
                                </div>
                                <div class="error-message" id="payment_error">Please select a payment method</div>

                                <!-- Credit Card Details (shown only when credit/debit card is selected) -->
                                <div id="card-details" class="mt-4 p-4 bg-gray-800 rounded-lg hidden">
                                    <h3 class="text-lg font-semibold mb-4">Card Details</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-gray-300 mb-2">Card Number</label>
                                            <input type="text" id="card_number"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                                                placeholder="1234 5678 9012 3456" maxlength="19">
                                            <div class="error-message" id="card_number_error">Please enter a valid card number</div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 mb-2">Cardholder Name</label>
                                            <input type="text" id="card_name"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                                                placeholder="John Doe">
                                            <div class="error-message" id="card_name_error">Please enter cardholder name</div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 mb-2">Expiry Date</label>
                                            <input type="text" id="card_expiry"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                                                placeholder="MM/YY" maxlength="5">
                                            <div class="error-message" id="card_expiry_error">Please enter a valid expiry date</div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-300 mb-2">CVV</label>
                                            <input type="text" id="card_cvv"
                                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white"
                                                placeholder="123" maxlength="4">
                                            <div class="error-message" id="card_cvv_error">Please enter a valid CVV</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            break;

                        case 4: ?>
                            <!-- Step 4: Seat Selection -->
                            <h2 class="text-2xl font-bold mb-6">Seat Selection</h2>
                            <div class="bg-blue-900 border border-blue-700 text-blue-200 px-4 py-3 rounded mb-6">
                                <strong>Instructions:</strong> Please select <?= $passengers ?> seat(s) for your flight.
                                Premium seats (dark yellow) cost an extra HKD 50 per seat.
                            </div>

                            <div class="airplane-cabin">
                                <div class="cabin-label">Business Class (Rows 1-2)</div>
                                <div class="seat-map-container">
                                    <?php
                                    // Handle selected seats properly to avoid array error
                                    $selected_seats = $_SESSION['payment_data']['selected_seats'] ?? [];
                                    if (is_string($selected_seats)) {
                                        $selected_seats = json_decode($selected_seats, true) ?? [];
                                    }
                                    if (!is_array($selected_seats)) {
                                        $selected_seats = [];
                                    }

                                    for ($row = 1; $row <= 2; $row++): ?>
                                        <?php for ($col = 0; $col < 6; $col++): ?>
                                            <?php
                                            $seat = $seat_map[($row - 1) * 6 + $col];
                                            $seat_class = 'seat ' . $seat['type'];
                                            if (in_array($seat['number'], $selected_seats)) {
                                                $seat_class .= ' selected';
                                            }
                                            ?>
                                            <div class="<?= $seat_class ?>"
                                                data-seat="<?= $seat['number'] ?>"
                                                data-price="<?= $seat['price'] ?>"
                                                onclick="selectSeat(this)">
                                                <?= $seat['number'] ?>
                                            </div>
                                        <?php endfor; ?>
                                    <?php endfor; ?>
                                </div>

                                <div class="cabin-label mt-8">Economy Class (Rows 3-10)</div>
                                <div class="seat-map-container">
                                    <?php for ($row = 3; $row <= 10; $row++): ?>
                                        <?php for ($col = 0; $col < 6; $col++): ?>
                                            <?php
                                            $seat = $seat_map[($row - 1) * 6 + $col];
                                            $seat_class = 'seat ' . $seat['type'];
                                            if (in_array($seat['number'], $selected_seats)) {
                                                $seat_class .= ' selected';
                                            }
                                            ?>
                                            <div class="<?= $seat_class ?>"
                                                data-seat="<?= $seat['number'] ?>"
                                                data-price="<?= $seat['price'] ?>"
                                                onclick="selectSeat(this)">
                                                <?= $seat['number'] ?>
                                            </div>
                                        <?php endfor; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Seat Legend -->
                            <div class="flex justify-center space-x-6 mt-6 text-sm">
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                    <span class="text-gray-300">Available (HKD 0)</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-yellow-700 rounded"></div>
                                    <span class="text-gray-300">Premium (HKD 50)</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                                    <span class="text-gray-300">Selected</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-gray-600 rounded relative">
                                        <span class="absolute inset-0 flex items-center justify-center text-red-500 text-xs">✕</span>
                                    </div>
                                    <span class="text-gray-300">Occupied</span>
                                </div>
                            </div>

                            <!-- Selected Seats Summary -->
                            <div class="mt-6 p-4 bg-gray-800 rounded-lg">
                                <h3 class="font-semibold mb-3">Selected Seats</h3>
                                <div id="selected-seats-list" class="text-gray-300 mb-2">
                                    <?php
                                    echo !empty($selected_seats) ? implode(', ', $selected_seats) : 'No seats selected';
                                    ?>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400">Extra Charges:</span>
                                    <span class="text-yellow-400 font-semibold" id="seat-charges-amount">
                                        HKD $<?= number_format($_SESSION['payment_data']['seat_charges'] ?? 0, 2) ?>
                                    </span>
                                </div>
                                <input type="hidden" name="step_data[selected_seats]" id="selected-seats-input"
                                    value="<?= htmlspecialchars(json_encode($selected_seats)) ?>">
                                <input type="hidden" name="step_data[seat_charges]" id="seat-charges-input"
                                    value="<?= $_SESSION['payment_data']['seat_charges'] ?? 0 ?>">
                            </div>
                        <?php
                            break;

                        case 5: ?>
                            <!-- Step 5: Confirmation -->
                            <h2 class="text-2xl font-bold mb-6">Confirm Your Booking</h2>
                            <div class="space-y-6">
                                <!-- Coupon Section -->
                                <div class="coupon-section">
                                    <h3 class="text-xl font-bold text-blue-900 mb-4">Apply Coupon Code</h3>

                                    <?php if (isset($_SESSION['payment_data']['applied_coupon'])):
                                        $applied_coupon = $_SESSION['payment_data']['applied_coupon'];
                                        $discount_amount = $_SESSION['payment_data']['discount_amount'] ?? 0;
                                    ?>
                                        <div class="applied-coupon">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <h4 class="font-bold">Coupon Applied</h4>
                                                    <p class="text-sm"><?= htmlspecialchars($applied_coupon['description']) ?></p>
                                                    <p class="text-sm font-mono">Code: <?= htmlspecialchars($applied_coupon['code']) ?></p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="font-bold text-lg">-HKD $<?= number_format($discount_amount, 2) ?></p>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="action" value="remove_coupon">
                                                        <button type="submit" class="coupon-btn remove text-sm mt-2">
                                                            Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST" class="coupon-input-group">
                                            <input type="hidden" name="action" value="apply_coupon">
                                            <input type="text" name="coupon_code" class="coupon-input"
                                                placeholder="Enter coupon code (e.g., WELCOME15)"
                                                value="<?= htmlspecialchars($_POST['coupon_code'] ?? '') ?>"
                                                required>
                                            <button type="submit" class="coupon-btn">Apply Coupon</button>
                                        </form>
                                        <p class="text-blue-900 text-sm">
                                            <i data-feather="info" class="w-4 h-4 inline mr-1"></i>
                                            New users can use code <strong>WELCOME15</strong> for 15% off first flight!
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Flight Summary -->
                                <?php if (isset($_SESSION['payment_data']['flight_details'])):
                                    $flight = $_SESSION['payment_data']['flight_details'];
                                ?>
                                    <div class="bg-gray-800 p-6 rounded">
                                        <h3 class="text-lg font-semibold mb-4">Flight Details</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-gray-400">Airline</p>
                                                <p class="text-white font-semibold"><?= htmlspecialchars($flight['airline_name']) ?> (<?= htmlspecialchars($flight['airline_code']) ?>)</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-400">Flight Number</p>
                                                <p class="text-white font-semibold"><?= htmlspecialchars($flight['flight_number']) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-400">Route</p>
                                                <p class="text-white font-semibold"><?= htmlspecialchars($flight['origin']) ?> → <?= htmlspecialchars($flight['destination']) ?></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-400">Travel Dates</p>
                                                <p class="text-white font-semibold">
                                                    <?= date('M j, Y', strtotime($flight['departure_date'])) ?> - <?= date('M j, Y', strtotime($flight['return_date'])) ?>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-gray-400">Passengers</p>
                                                <p class="text-white font-semibold"><?= $passengers ?> <?= $passengers > 1 ? 'travelers' : 'traveler' ?></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-400">Flight Type</p>
                                                <p class="text-white font-semibold"><?= ucfirst(str_replace('_', ' ', $flight_type)) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Selected Seats -->
                                <?php
                                // Handle selected seats properly
                                $selected_seats = $_SESSION['payment_data']['selected_seats'] ?? [];
                                if (is_string($selected_seats)) {
                                    $selected_seats = json_decode($selected_seats, true) ?? [];
                                }
                                if (!is_array($selected_seats)) {
                                    $selected_seats = [];
                                }
                                if (!empty($selected_seats)): ?>
                                    <div class="bg-gray-800 p-6 rounded">
                                        <h3 class="text-lg font-semibold mb-4">Selected Seats</h3>
                                        <p class="text-white font-semibold"><?= implode(', ', $selected_seats) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Passenger Info -->
                                <div class="bg-gray-800 p-6 rounded">
                                    <h3 class="text-lg font-semibold mb-4">Passenger Information</h3>
                                    <?php if (isset($_SESSION['payment_data']['passenger_info'])): ?>
                                        <?php foreach ($_SESSION['payment_data']['passenger_info'] as $index => $passenger): ?>
                                            <div class="mb-4 pb-4 border-b border-gray-700 last:border-b-0 last:mb-0 last:pb-0">
                                                <h4 class="font-semibold text-yellow-400 mb-2">Passenger <?= substr($index, -1) ?></h4>
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
                                    <?php endif; ?>
                                </div>

                                <!-- Billing Address -->
                                <div class="bg-gray-800 p-6 rounded">
                                    <h3 class="text-lg font-semibold mb-4">Billing Address</h3>
                                    <?php if (isset($_SESSION['payment_data']['billing_address'])): ?>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div><span class="text-gray-400">Street:</span> <span class="text-white"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['street']) ?></span></div>
                                            <div><span class="text-gray-400">City:</span> <span class="text-white"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['city']) ?></span></div>
                                            <div><span class="text-gray-400">State/Province:</span> <span class="text-white"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['state']) ?></span></div>
                                            <div><span class="text-gray-400">ZIP/Postal Code:</span> <span class="text-white"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['zip']) ?></span></div>
                                            <div class="md:col-span-2"><span class="text-gray-400">Country:</span> <span class="text-white"><?= htmlspecialchars($_SESSION['payment_data']['billing_address']['country']) ?></span></div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Payment Method -->
                                <div class="bg-gray-800 p-6 rounded">
                                    <h3 class="text-lg font-semibold mb-4">Payment Method</h3>
                                    <p class="text-white"><?= ucfirst(str_replace('_', ' ', $_SESSION['payment_data']['payment_method'] ?? '')) ?></p>
                                </div>

                                <!-- Order Summary -->
                                <div class="bg-gray-800 p-6 rounded">
                                    <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-300">Flight Cost (<?= $passengers ?> <?= $passengers > 1 ? 'travelers' : 'traveler' ?>):</span>
                                            <span class="text-white">HKD $<?= number_format($_SESSION['payment_data']['base_amount'] ?? 0, 2) ?></span>
                                        </div>
                                        <?php if (isset($_SESSION['payment_data']['tax_amount']) && $_SESSION['payment_data']['tax_amount'] > 0): ?>
                                            <div class="flex justify-between">
                                                <span class="text-gray-300">Taxes & Fees (3%):</span>
                                                <span class="text-white">HKD $<?= number_format($_SESSION['payment_data']['tax_amount'] ?? 0, 2) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (($_SESSION['payment_data']['seat_charges'] ?? 0) > 0): ?>
                                            <div class="flex justify-between">
                                                <span class="text-gray-300">Seat Selection Charges:</span>
                                                <span class="text-white">HKD $<?= number_format($_SESSION['payment_data']['seat_charges'] ?? 0, 2) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['payment_data']['discount_amount']) && $_SESSION['payment_data']['discount_amount'] > 0): ?>
                                            <div class="flex justify-between text-green-400">
                                                <span class="text-gray-300">Coupon Discount:</span>
                                                <span class="font-semibold">-HKD $<?= number_format($_SESSION['payment_data']['discount_amount'] ?? 0, 2) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex justify-between font-bold text-lg border-t border-gray-600 pt-3 mt-2">
                                            <?php
                                            $final_total = (($_SESSION['payment_data']['base_amount'] ?? 0) + ($_SESSION['payment_data']['tax_amount'] ?? 0) + ($_SESSION['payment_data']['seat_charges'] ?? 0)) - ($_SESSION['payment_data']['discount_amount'] ?? 0);
                                            if ($final_total < 0) $final_total = 0;
                                            ?>
                                            <span class="text-white">Total Amount:</span>
                                            <span class="text-yellow-400">HKD $<?= number_format($final_total, 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                            break;
                    }
                    ?>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8 pt-6 border-t border-gray-700">
                        <?php if ($step > 1): ?>
                            <?php if ($step < 5): ?>
                                <button type="submit" name="action" value="previous_step"
                                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded transition-colors">
                                    <i data-feather="arrow-left" class="w-4 h-4 inline mr-2"></i>
                                    Previous
                                </button>
                            <?php else: ?>
                                <a href="payment.php?step=<?= $step - 1 ?>" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded transition-colors">
                                    <i data-feather="arrow-left" class="w-4 h-4 inline mr-2"></i>
                                    Previous
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div></div> <!-- Empty spacer -->
                        <?php endif; ?>

                        <?php if ($step < 5): ?>
                            <button type="submit" name="action" value="save_step"
                                class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded transition-colors">
                                Next
                                <i data-feather="arrow-right" class="w-4 h-4 inline ml-2"></i>
                            </button>
                        <?php else: ?>
                            <!-- Separate form for final payment processing -->
                            <form method="POST" id="final-payment-form" action="payment.php?step=5">
                                <input type="hidden" name="action" value="process_payment">
                                <button type="submit" id="complete-booking"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded transition-colors">
                                    <div class="loading-spinner" id="loading-spinner"></div>
                                    <span id="button-text">Complete Booking</span>
                                    <i data-feather="check" class="w-4 h-4 inline ml-2"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($step < 5): ?>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <!-- Step 6: Receipt -->
                <?= displayIntegratedReceipt(); ?>

                <div class="flex justify-center mt-8 no-print">
                    <a href="dashboard.php?view=bookings"
                        class="bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-6 rounded transition-colors">
                        Go to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        feather.replace();

        // Luhn algorithm validation
        function luhnCheck(cardNumber) {
            let sum = 0;
            let shouldDouble = false;

            // Loop through values from the right to left
            for (let i = cardNumber.length - 1; i >= 0; i--) {
                let digit = parseInt(cardNumber.charAt(i));

                if (shouldDouble) {
                    digit *= 2;
                    if (digit > 9) {
                        digit -= 9;
                    }
                }

                sum += digit;
                shouldDouble = !shouldDouble;
            }

            return (sum % 10) === 0;
        }

        function isValidCardNumber(number) {
            // Remove all non-digit characters
            const cleaned = number.replace(/\D/g, '');

            // Check if it's 13-19 digits (standard card length)
            if (!/^\d{13,19}$/.test(cleaned)) {
                return false;
            }

            // Apply Luhn algorithm
            return luhnCheck(cleaned);
        }

        // Use Profile Data for Passenger Information
        document.getElementById('use-profile-btn')?.addEventListener('click', function() {
            <?php if (isset($user)): ?>
                // Fill first passenger with user data
                const firstName = '<?= addslashes($user['name'] ?? '') ?>'.split(' ')[0] || '';
                const lastName = '<?= addslashes($user['name'] ?? '') ?>'.split(' ').slice(1).join(' ') || '';

                document.querySelector('input[name="step_data[passenger_info][passenger_1][first_name]"]').value = firstName;
                document.querySelector('input[name="step_data[passenger_info][passenger_1][last_name]"]').value = lastName;
                document.querySelector('input[name="step_data[passenger_info][passenger_1][email]"]').value = '<?= addslashes($user['email'] ?? '') ?>';
                document.querySelector('input[name="step_data[passenger_info][passenger_1][phone]"]').value = '<?= addslashes($user['phone'] ?? '') ?>';
                document.querySelector('select[name="step_data[passenger_info][passenger_1][gender]"]').value = '<?= addslashes($user['gender'] ?? 'male') ?>';
                document.querySelector('input[name="step_data[passenger_info][passenger_1][dob]"]').value = '<?= addslashes($user['date_of_birth'] ?? '') ?>';
            <?php endif; ?>
        });

        // Use Profile Data for Billing Address
        document.getElementById('use-billing-btn')?.addEventListener('click', function() {
            <?php if (isset($user)): ?>
                document.querySelector('input[name="step_data[billing_address][street]"]').value = '<?= addslashes($user['address'] ?? '') ?>';
                document.querySelector('input[name="step_data[billing_address][city]"]').value = '<?= addslashes($user['city_name'] ?? '') ?>';

                const stateField = document.getElementById('billing_state');
                if (stateField) {
                    stateField.value = '<?= addslashes($user['state_name'] ?? '') ?>';
                }

                document.querySelector('input[name="step_data[billing_address][zip]"]').value = '<?= addslashes($user['postal_code'] ?? '') ?>';
                document.querySelector('input[name="step_data[billing_address][country]"]').value = '<?= addslashes($user['country_name'] ?? '') ?>';
            <?php endif; ?>
        });

        // Seat Selection Functionality
        function selectSeat(element) {
            const seatNumber = element.getAttribute('data-seat');
            const seatPrice = parseInt(element.getAttribute('data-price'));
            const maxSeats = <?= $passengers ?>;

            // Get current selected seats from hidden input
            const selectedSeatsInput = document.getElementById('selected-seats-input');
            let selectedSeats = JSON.parse(selectedSeatsInput.value || '[]');
            let seatCharges = parseInt(document.getElementById('seat-charges-input').value) || 0;

            // Check if seat is already selected
            const seatIndex = selectedSeats.indexOf(seatNumber);

            if (seatIndex > -1) {
                // Deselect seat
                selectedSeats.splice(seatIndex, 1);
                seatCharges -= seatPrice;
                element.classList.remove('selected');
                element.classList.add(element.getAttribute('data-price') > 0 ? 'premium' : 'available');
            } else {
                // Check if we can select more seats
                if (selectedSeats.length >= maxSeats) {
                    alert(`You can only select ${maxSeats} seat(s) for ${maxSeats} passenger(s).`);
                    return;
                }

                // Select seat
                selectedSeats.push(seatNumber);
                seatCharges += seatPrice;
                element.classList.remove('available', 'premium');
                element.classList.add('selected');
            }

            // Update UI
            document.getElementById('selected-seats-list').textContent = selectedSeats.join(', ') || 'No seats selected';
            document.getElementById('seat-charges-amount').textContent = `HKD $${seatCharges.toFixed(2)}`;

            // Update hidden inputs
            selectedSeatsInput.value = JSON.stringify(selectedSeats);
            document.getElementById('seat-charges-input').value = seatCharges;
        }

        // Form validation for steps 1-4 - ONLY for next button, not previous
        document.getElementById('payment-form')?.addEventListener('submit', function(e) {
            const currentStep = <?= $step ?>;
            const action = e.submitter?.value || '';

            // Skip validation for previous step
            if (action === 'previous_step') {
                return true; // Allow form submission without validation
            }

            // Only validate when going to next step
            if (action !== 'save_step') {
                return true;
            }

            let isValid = true;

            // Step-specific validation
            if (currentStep === 1) {
                // Validate passenger information
                const passengerCount = <?= $passengers ?>;
                for (let i = 1; i <= passengerCount; i++) {
                    const firstName = document.querySelector(`input[name="step_data[passenger_info][passenger_${i}][first_name]"]`);
                    const lastName = document.querySelector(`input[name="step_data[passenger_info][passenger_${i}][last_name]"]`);
                    const gender = document.querySelector(`select[name="step_data[passenger_info][passenger_${i}][gender]"]`);

                    if (!firstName.value.trim()) {
                        document.getElementById(`first_name_error_${i}`).style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById(`first_name_error_${i}`).style.display = 'none';
                    }

                    if (!lastName.value.trim()) {
                        document.getElementById(`last_name_error_${i}`).style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById(`last_name_error_${i}`).style.display = 'none';
                    }

                    if (!gender.value) {
                        document.getElementById(`gender_error_${i}`).style.display = 'block';
                        isValid = false;
                    } else {
                        document.getElementById(`gender_error_${i}`).style.display = 'none';
                    }

                    // For first passenger only, validate additional fields
                    if (i === 1) {
                        const email = document.querySelector(`input[name="step_data[passenger_info][passenger_${i}][email]"]`);
                        const phone = document.querySelector(`input[name="step_data[passenger_info][passenger_${i}][phone]"]`);
                        const dob = document.querySelector(`input[name="step_data[passenger_info][passenger_${i}][dob]"]`);

                        if (!email.value.trim() || !isValidEmail(email.value)) {
                            document.getElementById(`email_error_${i}`).style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById(`email_error_${i}`).style.display = 'none';
                        }

                        if (!phone.value.trim() || !isValidPhone(phone.value)) {
                            document.getElementById(`phone_error_${i}`).style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById(`phone_error_${i}`).style.display = 'none';
                        }

                        if (!dob.value) {
                            document.getElementById(`dob_error_${i}`).style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById(`dob_error_${i}`).style.display = 'none';
                        }
                    }
                }
            } else if (currentStep === 2) {
                // Validate billing address
                const street = document.querySelector('input[name="step_data[billing_address][street]"]');
                const city = document.querySelector('input[name="step_data[billing_address][city]"]');
                const state = document.getElementById('billing_state');
                const zip = document.querySelector('input[name="step_data[billing_address][zip]"]');
                const country = document.querySelector('input[name="step_data[billing_address][country]"]');

                if (!street.value.trim()) {
                    document.getElementById('street_error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('street_error').style.display = 'none';
                }

                if (!city.value.trim()) {
                    document.getElementById('city_error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('city_error').style.display = 'none';
                }

                if (!state.value.trim()) {
                    document.getElementById('state_error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('state_error').style.display = 'none';
                }

                if (!zip.value.trim()) {
                    document.getElementById('zip_error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('zip_error').style.display = 'none';
                }

                if (!country.value.trim()) {
                    document.getElementById('country_error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('country_error').style.display = 'none';
                }
            } else if (currentStep === 3) {
                // Validate payment method
                const paymentMethod = document.querySelector('input[name="step_data[payment_method]"]:checked');
                if (!paymentMethod) {
                    document.getElementById('payment_error').style.display = 'block';
                    isValid = false;
                } else {
                    document.getElementById('payment_error').style.display = 'none';

                    // If credit/debit card is selected, validate card details
                    if (paymentMethod.value === 'credit_card' || paymentMethod.value === 'debit_card') {
                        const cardNumber = document.getElementById('card_number');
                        const cardName = document.getElementById('card_name');
                        const cardExpiry = document.getElementById('card_expiry');
                        const cardCvv = document.getElementById('card_cvv');

                        if (!isValidCardNumber(cardNumber.value)) {
                            document.getElementById('card_number_error').style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById('card_number_error').style.display = 'none';
                        }

                        if (!cardName.value.trim()) {
                            document.getElementById('card_name_error').style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById('card_name_error').style.display = 'none';
                        }

                        if (!isValidExpiry(cardExpiry.value)) {
                            document.getElementById('card_expiry_error').style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById('card_expiry_error').style.display = 'none';
                        }

                        if (!isValidCVV(cardCvv.value)) {
                            document.getElementById('card_cvv_error').style.display = 'block';
                            isValid = false;
                        } else {
                            document.getElementById('card_cvv_error').style.display = 'none';
                        }
                    }
                }
            } else if (currentStep === 4) {
                // Validate seat selection
                const selectedSeats = JSON.parse(document.getElementById('selected-seats-input').value || '[]');
                if (selectedSeats.length !== <?= $passengers ?>) {
                    alert(`Please select exactly <?= $passengers ?> seat(s) for <?= $passengers ?> passenger(s).`);
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Complete booking form submission handler for step 5
        document.getElementById('final-payment-form')?.addEventListener('submit', function(e) {
            const button = document.getElementById('complete-booking');
            const spinner = document.getElementById('loading-spinner');
            const buttonText = document.getElementById('button-text');

            if (button && spinner && buttonText) {
                spinner.style.display = 'block';
                buttonText.textContent = 'Processing...';
                button.disabled = true;
            }

            // Allow form to submit naturally - don't prevent default
            console.log('Complete booking clicked, form submitting...');
        });

        // Helper validation functions
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function isValidPhone(phone) {
            const re = /^[\+]?[1-9][\d]{0,15}$/;
            return re.test(phone.replace(/\D/g, ''));
        }

        function isValidExpiry(expiry) {
            const re = /^(0[1-9]|1[0-2])\/?([0-9]{2})$/;
            if (!re.test(expiry)) return false;

            const [month, year] = expiry.split('/');
            const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
            const currentDate = new Date();

            return expiryDate > currentDate;
        }

        function isValidCVV(cvv) {
            const re = /^[0-9]{3,4}$/;
            return re.test(cvv);
        }

        // Show/hide card details based on payment method selection
        document.querySelectorAll('input[name="step_data[payment_method]"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const cardDetails = document.getElementById('card-details');
                if (this.value === 'credit_card' || this.value === 'debit_card') {
                    cardDetails.classList.remove('hidden');
                } else {
                    cardDetails.classList.add('hidden');
                }
            });
        });

        // Format card number input
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });

        // Format expiry date input
        document.getElementById('card_expiry')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // Real-time card number validation
        document.getElementById('card_number')?.addEventListener('blur', function(e) {
            const cardNumber = e.target.value;
            const errorElement = document.getElementById('card_number_error');

            if (cardNumber && !isValidCardNumber(cardNumber)) {
                errorElement.textContent = 'Please enter a valid card number';
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });
    </script>
</body>

</html>