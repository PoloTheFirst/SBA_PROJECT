<?php
require 'connection.php';

header('Content-Type: application/json');

try {
    $trending = isset($_GET['trending']) ? $_GET['trending'] : false;
    
    if ($trending) {
        $stmt = $pdo->prepare("
            SELECT d.*, l.name as location_name, l.city_name, l.country_name 
            FROM destinations d 
            JOIN locations l ON d.location_id = l.id 
            WHERE d.is_trending = true 
            ORDER BY d.price ASC 
            LIMIT 10
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT d.*, l.name as location_name, l.city_name, l.country_name 
            FROM destinations d 
            JOIN locations l ON d.location_id = l.id 
            ORDER BY d.is_trending DESC, d.price ASC 
            LIMIT 20
        ");
    }
    
    $stmt->execute();
    $destinations = $stmt->fetchAll();
    
    echo json_encode($destinations);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>