<?php
require 'connection.php';

$country_id = $_GET['country_id'] ?? 0;

if ($country_id > 0) {
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as country_name 
        FROM states s 
        JOIN countries c ON s.country_id = c.id 
        WHERE s.country_id = ? 
        ORDER BY s.name
    ");
    $stmt->execute([$country_id]);
    $states = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($states);
} else {
    echo json_encode([]);
}
?>