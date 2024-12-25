<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

if(!isset($_SESSION['user_id'])) {
    $response['error'] = 'Non connecté';
    echo json_encode($response);
    exit;
}

$request_id = $data['request_id'];
$status = $data['status']; // 'accepted' or 'refused'
$message = htmlspecialchars($data['message'] ?? '');

try {
    $pdo->beginTransaction();
    
    // Update request status
    $stmt = $pdo->prepare("UPDATE tutor_requests SET status = ?, response_message = ? WHERE id = ?");
    $stmt->execute([$status, $message, $request_id]);
    
    if($status === 'accepted') {
        // Verify tutor hasn't exceeded max students (4)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tutor_requests WHERE tutor_id = ? AND status = 'accepted'");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        
        if($result['count'] > 4) {
            throw new Exception('Maximum de tutorés atteint');
        }
    }
    
    // Get student email
    $stmt = $pdo->prepare("
        SELECT u.email 
        FROM tutor_requests tr 
        JOIN users u ON tr.student_id = u.id 
        WHERE tr.id = ?
    ");
    $stmt->execute([$request_id]);
    $student = $stmt->fetch();
    
    // Send email to student
    $subject = "Réponse à votre demande de tutorat";
    $msg = "Votre demande de tutorat a été " . ($status === 'accepted' ? 'acceptée' : 'refusée') . ".\n";
    if($message) {
        $msg .= "Message du tuteur : " . $message;
    }
    
    mail($student['email'], $subject, $msg);
    
    $pdo->commit();
    $response['success'] = true;
    
} catch(Exception $e) {
    $pdo->rollBack();
    $response['error'] = $e->getMessage();
}

echo json_encode($response);