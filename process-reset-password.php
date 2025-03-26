<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

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

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token)) {
    $_SESSION['message'] = "Token de réinitialisation manquant.";
    $_SESSION['message_type'] = "error";
    header('Location: forgot-password.php');
    exit();
}

// Validate password
$password_validation = validate_password($password, $confirm_password);
if ($password_validation !== true) {
    $_SESSION['message'] = $password_validation;
    $_SESSION['message_type'] = "error";
    header('Location: reset-password.php?token=' . urlencode($token));
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if token is valid
    $stmt = $db->prepare("
        SELECT * FROM password_reset_tokens 
        WHERE token = ? 
        AND expires_at > NOW() 
        AND used = 0
    ");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch();
    
    if ($token_data) {
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $token_data['user_id']]);
        
        // Mark token as used
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
        $stmt->execute([$token_data['id']]);
        
        $_SESSION['message'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
        $_SESSION['message_type'] = "success";
        header('Location: login.php');
    } else {
        $_SESSION['message'] = "Ce lien de réinitialisation est invalide ou a expiré.";
        $_SESSION['message_type'] = "error";
        header('Location: forgot-password.php');
    }
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    $_SESSION['message'] = "Une erreur est survenue. Veuillez réessayer.";
    $_SESSION['message_type'] = "error";
    header('Location: reset-password.php?token=' . urlencode($token));
}
exit();
