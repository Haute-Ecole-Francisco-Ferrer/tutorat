<?php
/**
 * Email utility functions
 */

/**
 * Send an email with proper UTF-8 encoding
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body
 * @param string $from_email Optional sender email
 * @param string $from_name Optional sender name
 * @return bool Whether the email was sent successfully
 */
function send_utf8_email($to, $subject, $body, $from_email = '', $from_name = 'Plateforme de Tutorat') {
    // Encode subject for UTF-8
    $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    
    // Set headers for UTF-8 and HTML
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
    
    // Set From header if provided
    if (!empty($from_email)) {
        $from_name = '=?UTF-8?B?'.base64_encode($from_name).'?=';
        $headers .= "From: {$from_name} <{$from_email}>\r\n";
    }
    
    // Set additional headers
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Send email
    return mail($to, $subject, $body, $headers);
}

function sendTutorRequestEmail($tutor_email, $tutee_name, $message) {
    $subject = "Nouvelle demande de tutorat";
    $body = "Bonjour,\n\n";
    $body .= "Vous avez reçu une nouvelle demande de tutorat de la part de {$tutee_name}.\n\n";
    $body .= "Message du tutoré :\n{$message}\n\n";
    $body .= "Pour répondre à cette demande, connectez-vous sur :\n";
    $body .= "https://tutorat.techniques-graphiques.be/my-tutees.php\n\n";
    $body .= "Cordialement,\nL'équipe de la plateforme de tutorat";

    return send_utf8_email($tutor_email, $subject, $body);
}

function sendTuteeResponseEmail($tutee_email, $tutor_name, $status, $message = '') {
    $subject = "Réponse à votre demande de tutorat";
    $body = "Bonjour,\n\n";
    $body .= "Votre demande de tutorat a été " . ($status === 'accepted' ? 'acceptée' : 'refusée') . " par {$tutor_name}.\n\n";
    
    if ($message) {
        $body .= "Message du tuteur :\n{$message}\n\n";
    }
    
    $body .= "Cordialement,\nL'équipe de la plateforme de tutorat";

    return send_utf8_email($tutee_email, $subject, $body);
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

    return send_utf8_email($email, $subject, $body);
}
?>
