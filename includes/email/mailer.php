<?php
/**
 * Email utility functions
 */

function sendTutorRequestEmail($tutor_email, $tutee_name, $message) {
    $subject = "Nouvelle demande de tutorat";
    $body = "Bonjour,\n\n";
    $body .= "Vous avez reçu une nouvelle demande de tutorat de la part de {$tutee_name}.\n\n";
    $body .= "Message du tutoré :\n{$message}\n\n";
    $body .= "Pour répondre à cette demande, connectez-vous sur :\n";
    $body .= "https://tutorat.techniques-graphiques.be/my-tutees.php\n\n";
    $body .= "Cordialement,\nL'équipe de la plateforme de tutorat";

    return mail($tutor_email, $subject, $body);
}

function sendTuteeResponseEmail($tutee_email, $tutor_name, $status, $message = '') {
    $subject = "Réponse à votre demande de tutorat";
    $body = "Bonjour,\n\n";
    $body .= "Votre demande de tutorat a été " . ($status === 'accepted' ? 'acceptée' : 'refusée') . " par {$tutor_name}.\n\n";
    
    if ($message) {
        $body .= "Message du tuteur :\n{$message}\n\n";
    }
    
    $body .= "Cordialement,\nL'équipe de la plateforme de tutorat";

    return mail($tutee_email, $subject, $body);
}

/**
 * Send password reset email
 * @param string $email User's email address
 * @param string $user_name User's full name
 * @param string $token Reset token
 * @return bool Whether the email was sent successfully
 */
function sendPasswordResetEmail($email, $user_name, $token) {
    $reset_url = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
    
    $subject = "Réinitialisation de votre mot de passe";
    $body = "Bonjour {$user_name},\n\n";
    $body .= "Vous avez demandé la réinitialisation de votre mot de passe sur la plateforme de tutorat.\n\n";
    $body .= "Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant :\n";
    $body .= "{$reset_url}\n\n";
    $body .= "Ce lien expirera dans 24 heures.\n\n";
    $body .= "Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.\n\n";
    $body .= "Cordialement,\nL'équipe de la plateforme de tutorat";

    return mail($email, $subject, $body);
}
?>
