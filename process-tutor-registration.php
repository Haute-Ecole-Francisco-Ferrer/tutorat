<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/tutor-validation.php';
require_once 'includes/email/mailer.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: register-tutor.php");
    exit();
}

$errors = validateTutorRegistration($_POST, $_FILES);

if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: register-tutor.php");
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'tutor', 'pending')
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

    // Create tutor record
    $stmt = $db->prepare("INSERT INTO tutors (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $tutor_id = $db->lastInsertId();

    // Add subjects
    if (isset($_POST["subjects"]) && is_array($_POST["subjects"])) {
        $stmt = $db->prepare("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES (?, ?)");
        foreach ($_POST["subjects"] as $subject_id) {
            $stmt->execute([$tutor_id, $subject_id]);
        }
    }

    // Add availabilities
    if (isset($_POST["days"]) && is_array($_POST["days"])) {
        $stmt = $db->prepare("
            INSERT INTO availability (user_id, day_of_week, start_time, end_time) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($_POST["days"] as $day) {
            $start_time = $_POST["start_time_" . $day] ?? null;
            $end_time = $_POST["end_time_" . $day] ?? null;
            
            if ($start_time && $end_time) {
                $days_map = [
                    1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                    4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
                ];
                
                $stmt->execute([
                    $user_id,
                    $days_map[$day],
                    $start_time,
                    $end_time
                ]);
            }
        }
    }

    // Get department name
    $stmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
    $stmt->execute([$_POST['department_id']]);
    $department = $stmt->fetch();
    $department_name = $department ? $department['name'] : 'Inconnu';

    // Send notification to all admins
    $stmt = $db->prepare("
        SELECT u.email, u.firstname, u.lastname
        FROM users u 
        JOIN admins a ON u.id = a.user_id
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll();

    if ($admins) {
        $admin_subject = "Nouvelle inscription tuteur à valider";
        $admin_message = "Une nouvelle inscription comme tuteur est en attente de validation.\n\n";
        $admin_message .= "Nom: " . $_POST['firstname'] . " " . $_POST['lastname'] . "\n";
        $admin_message .= "Email: " . $_POST['email'] . "\n";
        $admin_message .= "Section: " . $_POST['section'] . "\n";
        $admin_message .= "Département: " . $department_name . "\n\n";
        $admin_message .= "Pour valider cette inscription, connectez-vous à l'interface d'administration.";
        
        foreach ($admins as $admin) {
            send_utf8_email($admin['email'], $admin_subject, $admin_message);
        }
    }

    // Send confirmation email to user
    $subject = "Inscription comme tuteur en attente de validation";
    $message = "Bonjour " . $_POST['firstname'] . ",\n\n";
    $message .= "Votre inscription comme tuteur a bien été enregistrée. ";
    $message .= "Le secrétariat va examiner votre demande et vous tiendra informé par email.\n\n";
    $message .= "Cordialement,\nL'équipe de la plateforme de tutorat";
    
    send_utf8_email($_POST['email'], $subject, $message);

    $db->commit();
    $_SESSION['registration_success'] = true;
    header("Location: login.php");
    exit();

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['registration_errors'] = ["Une erreur est survenue lors de l'inscription. Veuillez réessayer."];
    $_SESSION['form_data'] = $_POST;
    header("Location: register-tutor.php");
    exit();
}
?>
