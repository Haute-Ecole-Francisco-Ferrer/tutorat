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
?>