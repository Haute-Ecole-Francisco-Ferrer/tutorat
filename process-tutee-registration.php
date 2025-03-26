<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/tutee-validation.php';
require_once 'includes/email/mailer.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: register-tutee.php");
    exit();
}

$errors = validateTuteeRegistration($_POST, $_FILES);

if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: register-tutee.php");
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Handle photo upload if provided
    $photo_filename = null;
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = upload_photo($_FILES["photo"]);
        if (isset($upload_result["error"])) {
            throw new Exception($upload_result["error"]);
        }
        $photo_filename = $upload_result["filename"];
    }

    // Insert user with pending status
    $stmt = $db->prepare("
        INSERT INTO users (
            firstname, lastname, username, email, password, 
            photo, phone, study_level, department_id, section, user_type, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'tutee', 'pending')
    ");
    
    $stmt->execute([
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['username'],
        $_POST['email'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $photo_filename,
        $_POST['phone'],
        $_POST['study_level'],
        $_POST['department_id'],
        $_POST['section']
    ]);
    
    $user_id = $db->lastInsertId();

    // Create tutee record
    $stmt = $db->prepare("INSERT INTO tutees (user_id) VALUES (?)");
    $stmt->execute([$user_id]);

    // Send confirmation email to user
    $subject = "Inscription comme tutoré en attente de validation";
    $message = "Bonjour " . $_POST['firstname'] . ",\n\n";
    $message .= "Votre inscription comme tutoré a bien été enregistrée. ";
    $message .= "Le secrétariat va examiner votre demande et vous tiendra informé par email.\n\n";
    $message .= "Cordialement,\nL'équipe de la plateforme de tutorat";
    
    mail($_POST['email'], $subject, $message);

    // Send notification to admin
    $stmt = $db->prepare("
        SELECT u.email 
        FROM users u 
        JOIN admins a ON u.id = a.user_id 
        WHERE a.department_id = ?
    ");
    $stmt->execute([$_POST['department_id']]);
    $admin = $stmt->fetch();

    if ($admin) {
        $admin_subject = "Nouvelle inscription tutoré à valider";
        $admin_message = "Une nouvelle inscription comme tutoré est en attente de validation.\n\n";
        $admin_message .= "Nom: " . $_POST['firstname'] . " " . $_POST['lastname'] . "\n";
        $admin_message .= "Email: " . $_POST['email'] . "\n";
        $admin_message .= "Section: " . $_POST['section'] . "\n\n";
        $admin_message .= "Pour valider cette inscription, connectez-vous à l'interface d'administration.";
        
        mail($admin['email'], $admin_subject, $admin_message);
    }

    $db->commit();
    $_SESSION['registration_success'] = true;
    header("Location: login.php");
    exit();

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['registration_errors'] = ["Une erreur est survenue lors de l'inscription. Veuillez réessayer."];
    $_SESSION['form_data'] = $_POST;
    header("Location: register-tutee.php");
    exit();
}
?>