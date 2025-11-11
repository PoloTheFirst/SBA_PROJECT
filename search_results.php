<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
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

// Mock flight data generator
function generateMockFlights($from, $to, $departureDate, $passengers, $class) {
    $flights = [];
    $airlines = [
        ['code' => 'AA', 'name' => 'American Airlines'],
        ['code' => 'UA', 'name' => 'United Airlines'],
        ['code' => 'DL', 'name' => 'Delta Air Lines'],
        ['code' => 'BA', 'name' => 'British Airways'],
        ['code' => 'LH', 'name' => 'Lufthansa'],
        ['code' => 'EK', 'name' => 'Emirates'],
        ['code' => 'SQ', 'name' => 'Singapore Airlines'],
        ['code' => 'CX', 'name' => 'Cathay Pacific']
    ];
    
    $basePrices = [
        'economy' => 300,
        'premium' => 500,
        'business' => 800,
        'first' => 1200
    ];
    
    for ($i = 0; $i < 5; $i++) {
        $airline = $airlines[array_rand($airlines)];
        $departureTime = rand(6, 22) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $duration = rand(1, 12);
        $arrivalTime = date('H:i', strtotime($departureTime . ' + ' . $duration . ' hours'));
        
        $basePrice = $basePrices[$class] * $passengers;
        $price = $basePrice + rand(-50, 100);
        
        $flights[] = [
            'airline' => $airline,
            'flight_number' => $airline['code'] . rand(100, 999),
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'duration' => $duration . 'h ' . rand(0, 59) . 'm',
            'price' => $price,
            'stops' => rand(0, 2)
        ];
    }
    
    // Sort by price
    usort($flights, function($a, $b) {
        return $a['price'] - $b['price'];
    });
    
    return $flights;
}

$flights = generateMockFlights($from, $to, $departure, $passengers, $class);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search Results | TravelGO Orbit</title>
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
                <span>Go Back</span>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Search Summary -->
        <div class="card-dark rounded-lg p-6 mb-8">
            <h1 class="text-2xl font-bold mb-4">Flight Search Results</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">Route:</span>
                    <span class="font-semibold"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></span>
                </div>
                <div>
                    <span class="text-gray-400">Departure:</span>
                    <span class="font-semibold"><?= htmlspecialchars($departure) ?></span>
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
                                <?= $flight['airline']['code'] ?>
                            </div>
                            <span class="font-semibold text-lg"><?= $flight['airline']['name'] ?></span>
                            <span class="text-gray-400"><?= $flight['flight_number'] ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between space-x-4">
                            <!-- Departure -->
                            <div class="text-center flex-1">
                                <div class="text-2xl font-bold text-white mb-1"><?= $flight['departure_time'] ?></div>
                                <div class="text-sm text-gray-400 font-medium"><?= htmlspecialchars($from) ?></div>
                            </div>
                            
                            <!-- Flight Path -->
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
                            
                            <!-- Arrival -->
                            <div class="text-center flex-1">
                                <div class="text-2xl font-bold text-white mb-1"><?= $flight['arrival_time'] ?></div>
                                <div class="text-sm text-gray-400 font-medium"><?= htmlspecialchars($to) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price and Action -->
                    <div class="flex flex-col items-center space-y-4 border-t lg:border-t-0 lg:border-l border-gray-700 pt-6 lg:pt-0 lg:pl-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-400 mb-1">
                                $<?= number_format($flight['price']) ?>
                            </div>
                            <div class="text-sm text-gray-400">per person</div>
                        </div>
                        <button class="select-btn text-blue-900 font-bold py-3 px-8 rounded-lg w-full lg:w-auto">
                            Select Flight
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Message -->
        <?php if (empty($flights)): ?>
        <div class="card-dark rounded-lg p-12 text-center">
            <i data-feather="search" class="w-20 h-20 text-gray-400 mx-auto mb-6"></i>
            <h3 class="text-2xl font-bold mb-4">No flights found</h3>
            <p class="text-gray-400 mb-6 text-lg">Try adjusting your search criteria or dates</p>
            <a href="index.php" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-blue-900 font-bold py-3 px-8 rounded-lg transition-all transform hover:scale-105 inline-block shadow-lg">
                New Search
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        feather.replace();
        
        // Add select flight functionality
        document.querySelectorAll('.select-btn').forEach(button => {
            button.addEventListener('click', function() {
                const flightCard = this.closest('.card-dark');
                const airline = flightCard.querySelector('span.font-semibold').textContent;
                const price = flightCard.querySelector('.text-yellow-400').textContent;
                const flightNumber = flightCard.querySelector('span.text-gray-400').textContent;
                
                // Add visual feedback
                this.classList.add('bg-green-500', 'text-white');
                this.innerHTML = '<i data-feather="check" class="w-4 h-4 mr-2"></i>Selected';
                feather.replace();
                
                setTimeout(() => {
                    alert(`Selected ${airline} flight ${flightNumber} for ${price}`);
                    // In a real application, this would redirect to booking page
                }, 500);
            });
        });
    </script>
</body>
</html>