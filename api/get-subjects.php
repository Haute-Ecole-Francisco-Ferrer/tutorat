<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$department_id = filter_var($_GET['department_id'] ?? null, FILTER_VALIDATE_INT);

if (!$department_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid department ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT id, name 
        FROM subjects 
        WHERE department_id = ?
        ORDER BY name
    ");
    $stmt->execute([$department_id]);
    
    echo json_encode($stmt->fetchAll());
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}