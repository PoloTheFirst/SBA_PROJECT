<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
require 'connection.php';

$country_id = $_GET['country_id'] ?? 0;

if ($country_id > 0) {
    $stmt = $pdo->prepare("
        SELECT c.* 
        FROM cities c 
        WHERE c.country_id = ? 
        ORDER BY c.name
    ");
    $stmt->execute([$country_id]);
    $cities = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($cities);
} else {
    echo json_encode([]);
}
?>