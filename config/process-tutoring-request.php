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

    $request_id = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (!$request_id || !in_array($action, ['accept', 'reject'])) {
        throw new Exception('Paramètres invalides');
    }

    // Get tutor ID
    $stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tutor = $stmt->fetch();
    
    if (!$tutor) {
        throw new Exception('Tuteur non trouvé');
    }

    // Get request details
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
        WHERE tr.id = ? AND tr.tutor_id = ? AND tr.status = 'pending'
    ");
    $stmt->execute([$request_id, $tutor['id']]);
    $request = $stmt->fetch();

    if (!$request) {
        throw new Exception('Demande introuvable ou déjà traitée');
    }

    // Check tutor capacity when accepting
    if ($action === 'accept') {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM tutoring_relationships 
            WHERE tutor_id = ? AND status = 'accepted'
        ");
        $stmt->execute([$tutor['id']]);
        $result = $stmt->fetch();
        
        if ($result['count'] >= 4) {
            throw new Exception('Vous avez atteint le nombre maximum de tutorés');
        }
    }

    // Update request status
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    $stmt = $db->prepare("
        UPDATE tutoring_relationships 
        SET status = ?, tutor_response = ?
        WHERE id = ?
    ");
    $stmt->execute([$status, $message, $request_id]);

    // Update tutor's current tutees count if accepted
    if ($action === 'accept') {
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
    }

    // Send email notification
    $subject = "Réponse à votre demande de tutorat";
    $email_message = "Bonjour,\n\n";
    $email_message .= "Votre demande de tutorat en {$request['subject_name']} a été " . 
                     ($status === 'accepted' ? 'acceptée' : 'refusée') . ".\n\n";
    
    if ($message) {
        $email_message .= "Message du tuteur :\n{$message}\n\n";
    }
    
    if ($status === 'accepted') {
        $email_message .= "Vous pouvez maintenant contacter votre tuteur pour organiser vos séances.\n\n";
    }
    
    $email_message .= "Cordialement,\nL'équipe de la plateforme de tutorat";
    
    mail($request['tutee_email'], $subject, $email_message);

    $db->commit();
    $_SESSION['success_message'] = 'La demande a été ' . ($status === 'accepted' ? 'acceptée' : 'refusée') . ' avec succès.';

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: my-tutees.php');
exit;
?>