<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Get search parameters
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$departure = $_GET['departure'] ?? '';
$return = $_GET['return'] ?? '';
$passengers = $_GET['passengers'] ?? '1';
$class = $_GET['class'] ?? 'economy';

// Search for round trip flights in database
try {
    $stmt = $pdo->prepare("
        SELECT * FROM round_trip_flights 
        WHERE origin LIKE ? AND destination LIKE ? 
        AND departure_date = ? AND return_date = ?
        AND class = ? AND seats_available >= ?
        ORDER BY price ASC
    ");
    $stmt->execute([
        "%$from%",
        "%$to%", 
        $departure,
        $return,
        $class,
        $passengers
    ]);
    $flights = $stmt->fetchAll();
    
    // If no results found in DB, generate mock data
    if (empty($flights)) {
        $flights = generateMockRoundTripFlights($from, $to, $departure, $return, $passengers, $class);
    }
    
} catch (PDOException $e) {
    error_log("Round trip search error: " . $e->getMessage());
    $flights = generateMockRoundTripFlights($from, $to, $departure, $return, $passengers, $class);
}

function generateMockRoundTripFlights($from, $to, $departureDate, $returnDate, $passengers, $class) {
    $flights = [];
    $airlines = [
        ['code' => 'CX', 'name' => 'Cathay Pacific'],
        ['code' => 'JL', 'name' => 'Japan Airlines'],
        ['code' => 'NH', 'name' => 'All Nippon Airways'],
        ['code' => 'TG', 'name' => 'Thai Airways'],
        ['code' => 'SQ', 'name' => 'Singapore Airlines'],
        ['code' => 'EK', 'name' => 'Emirates']
    ];
    
    $basePrices = [
        'economy' => 2500,
        'premium' => 4000,
        'business' => 6000,
        'first' => 9000
    ];
    
    for ($i = 0; $i < 6; $i++) {
        $airline = $airlines[array_rand($airlines)];
        $departureTime = rand(6, 22) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $duration = rand(1, 8);
        $arrivalTime = date('H:i', strtotime($departureTime . ' + ' . $duration . ' hours'));
        
        $returnDepartureTime = rand(6, 22) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $returnDuration = rand(1, 8);
        $returnArrivalTime = date('H:i', strtotime($returnDepartureTime . ' + ' . $returnDuration . ' hours'));
        
        $basePrice = $basePrices[$class] * $passengers;
        $price = $basePrice + rand(-200, 400);
        
        $flights[] = [
            'id' => $i + 1,
            'flight_number' => $airline['code'] . rand(100, 999),
            'airline_code' => $airline['code'],
            'airline_name' => $airline['name'],
            'origin' => $from,
            'destination' => $to,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'return_departure_time' => $returnDepartureTime,
            'return_arrival_time' => $returnArrivalTime,
            'duration' => $duration . 'h ' . rand(0, 59) . 'm',
            'return_duration' => $returnDuration . 'h ' . rand(0, 59) . 'm',
            'stops' => rand(0, 1),
            'return_stops' => rand(0, 1),
            'price' => $price,
            'class' => $class,
            'seats_available' => rand(5, 50)
        ];
    }
    
    // Sort by price
    usort($flights, function($a, $b) {
        return $a['price'] - $b['price'];
    });
    
    return $flights;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Round Trip Flight Results | TravelGO Orbit</title>
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
        .select-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            transition: all 0.3s ease;
        }
        .select-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
        }
        .flight-leg {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 16px;
            margin: 8px 0;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i data-feather="navigation" class="text-yellow-400"></i>
                <span class="text-xl font-bold">TravelGO Orbit</span>
            </div>
            <a href="index.php" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-blue-900 font-bold py-2 px-6 rounded-lg transition-all transform hover:scale-105 flex items-center space-x-2 shadow-lg">
                <i data-feather="arrow-left" class="w-4 h-4"></i>
                <span>New Search</span>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Search Summary -->
        <div class="card-dark rounded-lg p-6 mb-8">
            <h1 class="text-2xl font-bold mb-4">Round Trip Flight Results</h1>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">Route:</span>
                    <span class="font-semibold"><?= htmlspecialchars($from) ?> ↔ <?= htmlspecialchars($to) ?></span>
                </div>
                <div>
                    <span class="text-gray-400">Departure:</span>
                    <span class="font-semibold"><?= htmlspecialchars($departure) ?></span>
                </div>
                <div>
                    <span class="text-gray-400">Return:</span>
                    <span class="font-semibold"><?= htmlspecialchars($return) ?></span>
                </div>
                <div>
                    <span class="text-gray-400">Passengers:</span>
                    <span class="font-semibold"><?= htmlspecialchars($passengers) ?> • <?= htmlspecialchars(ucfirst($class)) ?></span>
                </div>
            </div>
        </div>

        <!-- Flight Results -->
        <div class="space-y-6">
            <?php foreach ($flights as $flight): ?>
            <div class="card-dark rounded-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-700 hover:border-yellow-500/30">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                    <!-- Flight Info -->
                    <div class="flex-1 mb-6 lg:mb-0 lg:pr-8">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="bg-blue-900 px-3 py-1 rounded-full text-sm font-semibold">
                                <?= $flight['airline_code'] ?>
                            </div>
                            <span class="font-semibold text-lg"><?= $flight['airline_name'] ?></span>
                            <span class="text-gray-400"><?= $flight['flight_number'] ?></span>
                        </div>
                        
                        <!-- Outbound Flight -->
                        <div class="flight-leg mb-4">
                            <div class="text-sm text-gray-400 mb-2 font-semibold">Outbound • <?= $flight['departure_date'] ?></div>
                            <div class="flex items-center justify-between space-x-4">
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-white mb-1"><?= $flight['departure_time'] ?></div>
                                    <div class="text-sm text-gray-400 font-medium"><?= htmlspecialchars($from) ?></div>
                                </div>
                                
                                <div class="flex-1 text-center px-4">
                                    <div class="text-sm text-gray-400 mb-2"><?= $flight['duration'] ?></div>
                                    <div class="flex items-center justify-center">
                                        <div class="w-8 h-1 bg-gray-600 rounded-full"></div>
                                        <i data-feather="airplane" class="mx-2 text-gray-400 w-5 h-5 transform rotate-45"></i>
                                        <div class="flex-1 h-1 bg-gray-600 rounded-full"></div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2 font-medium">
                                        <?= $flight['stops'] === 0 ? 'Nonstop' : $flight['stops'] . ' stop' . ($flight['stops'] > 1 ? 's' : '') ?>
                                    </div>
                                </div>
                                
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-white mb-1"><?= $flight['arrival_time'] ?></div>
                                    <div class="text-sm text-gray-400 font-medium"><?= htmlspecialchars($to) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Return Flight -->
                        <div class="flight-leg">
                            <div class="text-sm text-gray-400 mb-2 font-semibold">Return • <?= $flight['return_date'] ?></div>
                            <div class="flex items-center justify-between space-x-4">
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-white mb-1"><?= $flight['return_departure_time'] ?></div>
                                    <div class="text-sm text-gray-400 font-medium"><?= htmlspecialchars($to) ?></div>
                                </div>
                                
                                <div class="flex-1 text-center px-4">
                                    <div class="text-sm text-gray-400 mb-2"><?= $flight['return_duration'] ?></div>
                                    <div class="flex items-center justify-center">
                                        <div class="w-8 h-1 bg-gray-600 rounded-full"></div>
                                        <i data-feather="airplane" class="mx-2 text-gray-400 w-5 h-5 transform rotate-45"></i>
                                        <div class="flex-1 h-1 bg-gray-600 rounded-full"></div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2 font-medium">
                                        <?= $flight['return_stops'] === 0 ? 'Nonstop' : $flight['return_stops'] . ' stop' . ($flight['return_stops'] > 1 ? 's' : '') ?>
                                    </div>
                                </div>
                                
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-white mb-1"><?= $flight['return_arrival_time'] ?></div>
                                    <div class="text-sm text-gray-400 font-medium"><?= htmlspecialchars($from) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price and Action -->
                    <div class="flex flex-col items-center space-y-4 border-t lg:border-t-0 lg:border-l border-gray-700 pt-6 lg:pt-0 lg:pl-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-400 mb-1">
                                $<?= number_format($flight['price']) ?>
                            </div>
                            <div class="text-sm text-gray-400">for <?= $passengers ?> passenger<?= $passengers > 1 ? 's' : '' ?></div>
                            <div class="text-xs text-green-400 mt-1">
                                <?= $flight['seats_available'] ?> seats available
                            </div>
                        </div>
                        <form action="payment.php" method="GET" class="w-full">
                            <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                            <input type="hidden" name="flight_type" value="round_trip">
                            <input type="hidden" name="passengers" value="<?= $passengers ?>">
                            <button type="submit" class="select-btn text-blue-900 font-bold py-3 px-8 rounded-lg w-full">
                                Select & Continue
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Message -->
        <?php if (empty($flights)): ?>
        <div class="card-dark rounded-lg p-12 text-center">
            <i data-feather="search" class="w-20 h-20 text-gray-400 mx-auto mb-6"></i>
            <h3 class="text-2xl font-bold mb-4">No round trip flights found</h3>
            <p class="text-gray-400 mb-6 text-lg">Try adjusting your search criteria or dates</p>
            <a href="index.php" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-blue-900 font-bold py-3 px-8 rounded-lg transition-all transform hover:scale-105 inline-block shadow-lg">
                New Search
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>