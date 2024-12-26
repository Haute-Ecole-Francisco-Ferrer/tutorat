<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/relationship-queries.php';
require_once 'includes/email/mailer.php';
require_once 'includes/utils/logging.php';

session_start();
debug_log('process-request', "Processing tutoring request");

// Verify user is logged in and is a tutor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    debug_log('process-request', "Authentication failed - redirecting to login");
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log('process-request', "Invalid request method");
    header('Location: my-tutees.php');
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    debug_log('process-request', "Database connection established");
    
    $db->beginTransaction();

    $request_id = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    $message = trim($_POST['message'] ?? '');

    debug_log('process-request', "Request parameters", [
        'request_id' => $request_id,
        'action' => $action,
        'message_length' => strlen($message)
    ]);

    if (!$request_id || !in_array($action, ['accept', 'reject'])) {
        throw new Exception('Paramètres invalides');
    }

    // Get tutor ID
    $tutor_id = getTutorId($db, $_SESSION['user_id']);
    if (!$tutor_id) {
        throw new Exception('Tuteur non trouvé');
    }

    // Get request details and verify ownership
    $request = getRelationshipDetails($db, $request_id, $tutor_id);
    if (!$request) {
        throw new Exception('Demande introuvable ou déjà traitée');
    }

    // Check tutor capacity when accepting
    if ($action === 'accept') {
        checkTutorCapacity($db, $tutor_id);
    }

    // Update request status
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    updateRelationshipStatus($db, $request_id, $status, $message);

    // Update tutor's current tutees count if accepted
    if ($action === 'accept') {
        updateTutorTuteeCount($db, $tutor_id);
    }

    // Send email notification
    sendTuteeResponseEmail(
        $request['tutee_email'],
        $request['tutor_name'],
        $status,
        $message
    );

    $db->commit();
    debug_log('process-request', "Request processed successfully", ['status' => $status]);
    $_SESSION['success_message'] = 'La demande a été ' . ($status === 'accepted' ? 'acceptée' : 'refusée') . ' avec succès.';

} catch (Exception $e) {
    debug_log('process-request', "Error processing request: " . $e->getMessage());
    if (isset($db)) {
        $db->rollBack();
    }
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: my-tutees.php');
exit;
?>