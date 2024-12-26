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

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['relationship_id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$allowed_statuses = ['pending', 'accepted', 'archived'];
if (!in_array($data['status'], $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Update relationship status
    $stmt = $db->prepare("
        UPDATE tutoring_relationships 
        SET status = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $data['status'],
        $data['relationship_id']
    ]);

    if ($result) {
        // If status is accepted, update tutor's current_tutees count
        if ($data['status'] === 'accepted') {
            $stmt = $db->prepare("
                UPDATE tutors t
                SET current_tutees = (
                    SELECT COUNT(*) 
                    FROM tutoring_relationships tr 
                    WHERE tr.tutor_id = t.id 
                    AND tr.status = 'accepted'
                )
                WHERE id = (
                    SELECT tutor_id 
                    FROM tutoring_relationships 
                    WHERE id = ?
                )
            ");
            $stmt->execute([$data['relationship_id']]);
        }

        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update status');
    }

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>