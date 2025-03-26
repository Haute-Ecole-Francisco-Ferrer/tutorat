<?php
require_once '../../config/database.php';
require_once '../../includes/auth/admin-auth.php';

session_start();
header('Content-Type: application/json');

// Verify admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get admin's department
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT department_id FROM admins WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get subjects for admin's department
    $stmt = $db->prepare("
        SELECT * FROM subjects 
        WHERE department_id = ? 
        ORDER BY name
    ");
    $stmt->execute([$admin['department_id']]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name']) || empty(trim($data['name']))) {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required']);
        exit;
    }

    try {
        // Check if subject already exists in this department
        $stmt = $db->prepare("
            SELECT id FROM subjects 
            WHERE name = ? AND department_id = ?
        ");
        $stmt->execute([$data['name'], $admin['department_id']]);
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Subject already exists']);
            exit;
        }

        // Add new subject
        $stmt = $db->prepare("
            INSERT INTO subjects (name, department_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$data['name'], $admin['department_id']]);
        
        echo json_encode([
            'id' => $db->lastInsertId(),
            'name' => $data['name'],
            'department_id' => $admin['department_id']
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['name']) || empty(trim($data['name']))) {
        http_response_code(400);
        echo json_encode(['error' => 'ID and name are required']);
        exit;
    }

    try {
        // Verify subject belongs to admin's department
        $stmt = $db->prepare("
            SELECT id FROM subjects 
            WHERE id = ? AND department_id = ?
        ");
        $stmt->execute([$data['id'], $admin['department_id']]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Subject not found']);
            exit;
        }

        // Update subject
        $stmt = $db->prepare("
            UPDATE subjects 
            SET name = ? 
            WHERE id = ? AND department_id = ?
        ");
        $stmt->execute([$data['name'], $data['id'], $admin['department_id']]);
        
        echo json_encode([
            'id' => $data['id'],
            'name' => $data['name'],
            'department_id' => $admin['department_id']
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    try {
        // Verify subject belongs to admin's department
        $stmt = $db->prepare("
            SELECT id FROM subjects 
            WHERE id = ? AND department_id = ?
        ");
        $stmt->execute([$id, $admin['department_id']]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Subject not found']);
            exit;
        }

        // Check if subject is being used
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM tutor_subjects 
            WHERE subject_id = ?
        ");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Subject is in use']);
            exit;
        }

        // Delete subject
        $stmt = $db->prepare("
            DELETE FROM subjects 
            WHERE id = ? AND department_id = ?
        ");
        $stmt->execute([$id, $admin['department_id']]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);