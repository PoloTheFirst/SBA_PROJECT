<?php
require 'connection.php';

header('Content-Type: application/json');

if (isset($_GET['q'])) {
    $query = $_GET['q'];
    
    try {
        if ($query === '') {
            // Return popular locations when query is empty
            $stmt = $pdo->prepare("
                SELECT * FROM locations 
                WHERE type IN ('airport', 'city')
                ORDER BY 
                    CASE 
                        WHEN name LIKE 'Hong Kong%' THEN 1
                        WHEN name LIKE 'Tokyo%' THEN 2
                        WHEN name LIKE 'New York%' THEN 3
                        WHEN name LIKE 'London%' THEN 4
                        WHEN name LIKE 'Paris%' THEN 5
                        WHEN type = 'city' THEN 6
                        ELSE 7
                    END,
                    name
                LIMIT 20
            ");
            $stmt->execute();
        } else {
            // Search for matching locations with better ranking
            $stmt = $pdo->prepare("
                SELECT * FROM locations 
                WHERE name LIKE ? OR code LIKE ? OR city_name LIKE ? OR country_name LIKE ?
                ORDER BY 
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN code LIKE ? THEN 2
                        WHEN city_name LIKE ? THEN 3
                        WHEN country_name LIKE ? THEN 4
                        ELSE 5
                    END,
                    CASE WHEN type = 'city' THEN 1 ELSE 2 END,
                    name
                LIMIT 20
            ");
            $searchTerm = "%$query%";
            $exactTerm = "$query%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $exactTerm, $exactTerm, $exactTerm, $exactTerm]);
        }
        
        $results = $stmt->fetchAll();
        echo json_encode($results);
        
    } catch (PDOException $e) {
        error_log("Search locations error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode([]);
}
?>