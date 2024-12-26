<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/relationship-queries.php';
require_once 'includes/email/mailer.php';

session_start();

// Verify user is logged in and is a tutor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my-tutees.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $request_id = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (!$request_id || !in_array($action, ['accept', 'reject'])) {
        throw new Exception('Paramètres invalides');
    }

    // Get request details
    $stmt = $db->prepare("
        SELECT tr.*, 
               t.user_id as tutor_user_id,
               te.user_id as tutee_user_id,
               ut.username as tutor_name,
               ut.email as tutor_email,
               ute.username as tutee_name,
               ute.email as tutee_email
        FROM tutoring_relationships tr
        JOIN tutors t ON tr.tutor_id = t.id
        JOIN tutees te ON tr.tutee_id = te.id
        JOIN users ut ON t.user_id = ut.id
        JOIN users ute ON te.user_id = ute.id
        WHERE tr.id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();

    if (!$request) {
        throw new Exception('Demande introuvable');
    }

    // Check if tutor has reached maximum tutees when accepting
    if ($action === 'accept') {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM tutoring_relationships 
            WHERE tutor_id = ? AND status = 'accepted'
        ");
        $stmt->execute([$request['tutor_id']]);
        $result = $stmt->fetch();

        if ($result['count'] >= 4) {
            throw new Exception('Nombre maximum de tutorés atteint');
        }
    }

    // Update request status
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    updateTutoringRequest($db, $request_id, $status, $message);

    // Update tutor's current tutees count if accepted
    if ($action === 'accept') {
        updateTutorCurrentTutees($db, $request['tutor_id']);
    }

    // Send email to tutee
    sendTuteeResponseEmail(
        $request['tutee_email'],
        $request['tutor_name'],
        $status,
        $message
    );

    $db->commit();
    $_SESSION['success_message'] = 'La demande a été ' . ($status === 'accepted' ? 'acceptée' : 'refusée') . ' avec succès.';

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: my-tutees.php');
exit;
?>