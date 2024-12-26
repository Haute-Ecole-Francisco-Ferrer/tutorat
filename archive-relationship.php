<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug logging function
function debug_log($message, $data = null) {
    error_log("DEBUG [archive-relationship]: " . $message);
    if ($data !== null) {
        error_log("DATA: " . print_r($data, true));
    }
}

debug_log("Script started");

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/relationship-queries.php';
require_once 'includes/email/mailer.php';

session_start();
debug_log("Session data", $_SESSION);

// Verify user is logged in and is a tutor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    debug_log("Authentication failed - redirecting to login");
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header('Location: my-tutees.php');
    exit;
}

debug_log("POST data", $_POST);

try {
    $db = Database::getInstance()->getConnection();
    debug_log("Database connection established");
    
    $db->beginTransaction();
    debug_log("Transaction started");

    $relationship_id = filter_var($_POST['relationship_id'] ?? null, FILTER_VALIDATE_INT);
    $archive_reason = trim($_POST['archive_reason'] ?? '');

    debug_log("Parsed input", [
        'relationship_id' => $relationship_id,
        'archive_reason' => $archive_reason
    ]);

    if (!$relationship_id || empty($archive_reason)) {
        throw new Exception('Paramètres invalides');
    }

    // Get tutor ID
    $stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tutor = $stmt->fetch();
    $tutor_id = $tutor ? $tutor['id'] : null;

    debug_log("Tutor ID retrieved", ['tutor_id' => $tutor_id]);

    if (!$tutor_id) {
        throw new Exception('Tuteur non trouvé');
    }

    // Get relationship details and verify ownership
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
        WHERE tr.id = ? AND tr.tutor_id = ? AND tr.status = 'accepted'
    ");
    $stmt->execute([$relationship_id, $tutor_id]);
    $relationship = $stmt->fetch();

    debug_log("Relationship details retrieved", [
        'found' => !empty($relationship),
        'relationship' => $relationship
    ]);

    if (!$relationship) {
        throw new Exception('Relation de tutorat introuvable ou non autorisée');
    }

    // Archive the relationship
    debug_log("Archiving relationship");
    $stmt = $db->prepare("
        UPDATE tutoring_relationships 
        SET status = 'archived',
            archive_reason = ?,
            archived_at = NOW()
        WHERE id = ? AND tutor_id = ?
    ");
    $result = $stmt->execute([$archive_reason, $relationship_id, $tutor_id]);
    debug_log("Archive update result", ['success' => $result, 'rows_affected' => $stmt->rowCount()]);

    // Update tutor's current tutees count
    debug_log("Updating tutor's tutee count");
    $stmt = $db->prepare("
        UPDATE tutors 
        SET current_tutees = (
            SELECT COUNT(*) 
            FROM tutoring_relationships 
            WHERE tutor_id = ? 
            AND status = 'accepted'
        )
        WHERE id = ?
    ");
    $result = $stmt->execute([$tutor_id, $tutor_id]);
    debug_log("Tutee count update result", ['success' => $result, 'rows_affected' => $stmt->rowCount()]);

    // Send email notification to tutee
    debug_log("Sending email notification");
    $subject = "Fin de la relation de tutorat";
    $message = "Bonjour,\n\n";
    $message .= "Votre tuteur {$relationship['tutor_name']} a mis fin à la relation de tutorat.\n\n";
    $message .= "Raison : {$archive_reason}\n\n";
    $message .= "Vous pouvez rechercher un nouveau tuteur sur la plateforme.\n\n";
    $message .= "Cordialement,\nL'équipe de la plateforme de tutorat";
    
    $email_result = mail($relationship['tutee_email'], $subject, $message);
    debug_log("Email sending result", ['success' => $email_result]);

    $db->commit();
    debug_log("Transaction committed successfully");
    $_SESSION['success_message'] = 'La relation de tutorat a été archivée avec succès.';

} catch (Exception $e) {
    debug_log("Error occurred", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (isset($db)) {
        $db->rollBack();
        debug_log("Transaction rolled back");
    }
    $_SESSION['error_message'] = $e->getMessage();
}

debug_log("Redirecting to my-tutees.php");
header('Location: my-tutees.php');
exit;
?>