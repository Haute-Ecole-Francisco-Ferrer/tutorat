<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/email/mailer.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.php');
    exit();
}

$email = sanitize_input($_POST['email'] ?? '');
$message = '';
$message_type = '';

if (empty($email)) {
    $_SESSION['message'] = "Veuillez entrer une adresse e-mail.";
    $_SESSION['message_type'] = "error";
    header('Location: forgot-password.php');
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id, firstname, lastname FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Delete any existing tokens for this user
        $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        
        // Insert new token
        $stmt = $db->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user['id'], $token, $expires_at]);
        
        // Send email
        $fullname = $user['firstname'] . ' ' . $user['lastname'];
        sendPasswordResetEmail($email, $fullname, $token);
        
        $message = "Un lien de réinitialisation a été envoyé à votre adresse e-mail.";
        $message_type = "success";
    } else {
        // Don't reveal if email exists or not for security
        $message = "Si cette adresse e-mail est associée à un compte, un lien de réinitialisation a été envoyé.";
        $message_type = "success";
    }
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    $message = "Une erreur est survenue. Veuillez réessayer.";
    $message_type = "error";
}

$_SESSION['message'] = $message;
$_SESSION['message_type'] = $message_type;
header('Location: forgot-password.php');
exit();
