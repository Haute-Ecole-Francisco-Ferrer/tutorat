<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/email/mailer.php';
require_once 'includes/utils/logging.php';

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

    $relationship_id = filter_var($_POST['relationship_id'] ?? null, FILTER_VALIDATE_INT);
    $archive_reason = trim($_POST['archive_reason'] ?? '');

    if (!$relationship_id || empty($archive_reason)) {
        throw new Exception('Paramètres invalides');
    }

    // Get tutor ID
    $stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tutor = $stmt->fetch();
    
    if (!$tutor) {
        throw new Exception('Tuteur non trouvé');
    }

    // Get relationship details
    $stmt = $db->prepare("
        SELECT tr.*, 
               t.user_id as tutor_user_id,
               te.user_id as tutee_user_id,
               ut.username as tutor_name,
               ute.email as tutee_email,
               s.name as subject_name
        FROM tutoring_relationships tr
        JOIN tutors t ON tr.tutor_id = t.id
        JOIN tutees te ON tr.tutee_id = te.id
        JOIN users ut ON t.user_id = ut.id
        JOIN users ute ON te.user_id = ute.id
        JOIN subjects s ON tr.subject_id = s.id
        WHERE tr.id = ? AND tr.tutor_id = ? AND tr.status = 'accepted'
    ");
    $stmt->execute([$relationship_id, $tutor['id']]);
    $relationship = $stmt->fetch();

    if (!$relationship) {
        throw new Exception('Relation de tutorat introuvable ou non autorisée');
    }

    // Insert into archive table using MySQL's UUID()
    $stmt = $db->prepare("
        INSERT INTO tutoring_relationships_archive (
            id, original_id, tutor_id, tutee_id, subject_id,
            status, message, tutor_response, created_at,
            archived_at, archive_reason
        ) VALUES (
            UUID(), ?, ?, ?, ?,
            'archived', ?, ?, ?, NOW(), ?
        )
    ");
    $stmt->execute([
        $relationship['id'],
        $relationship['tutor_id'],
        $relationship['tutee_id'],
        $relationship['subject_id'],
        $relationship['message'],
        $relationship['tutor_response'],
        $relationship['created_at'],
        $archive_reason
    ]);

    // Delete from main table
    $stmt = $db->prepare("DELETE FROM tutoring_relationships WHERE id = ?");
    $stmt->execute([$relationship_id]);

    // Update tutor's current tutees count
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
    $stmt->execute([$tutor['id'], $tutor['id']]);

    // Send email notification to tutee
    $subject = "Fin de la relation de tutorat";
    $message = "Bonjour,\n\n";
    $message .= "Votre tuteur {$relationship['tutor_name']} a mis fin à la relation de tutorat.\n\n";
    $message .= "Matière : {$relationship['subject_name']}\n";
    $message .= "Raison : {$archive_reason}\n\n";
    $message .= "Vous pouvez rechercher un nouveau tuteur sur la plateforme.\n\n";
    $message .= "Cordialement,\nL'équipe de la plateforme de tutorat";
    
    mail($relationship['tutee_email'], $subject, $message);

    $db->commit();
    $_SESSION['success_message'] = 'La relation de tutorat a été archivée avec succès.';

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: my-tutees.php');
exit;
?>