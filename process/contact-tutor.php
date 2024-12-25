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

$student_id = $_SESSION['user_id'];
$tutor_id = $data['tutor_id'];
$message = htmlspecialchars($data['message']);

try {
    $stmt = $pdo->prepare("INSERT INTO tutor_requests (student_id, tutor_id, message, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$student_id, $tutor_id, $message]);
    
    // Get tutor email
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$tutor_id]);
    $tutor = $stmt->fetch();
    
    // Send email
    $to = $tutor['email'];
    $subject = "Nouvelle demande de tutorat";
    $msg = "Vous avez reçu une nouvelle demande de tutorat.\n";
    $msg .= "Message du tutoré : " . $message . "\n\n";
    $msg .= "Pour répondre : https://tutorat.techniques-graphiques.be/my-tutees.php";
    
    mail($to, $subject, $msg);
    
    $response['success'] = true;
    
} catch(PDOException $e) {
    $response['error'] = 'Erreur lors de la demande';
}

echo json_encode($response);