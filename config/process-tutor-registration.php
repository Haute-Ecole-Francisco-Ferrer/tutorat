<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: register-tutor.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/functions.php';

$db = Database::getInstance()->getConnection();
$error_message = "";
$success_message = "";

// Validation des données
$firstname = sanitize_input($_POST["firstname"] ?? '');
$lastname = sanitize_input($_POST["lastname"] ?? '');
$username = sanitize_input($_POST["username"] ?? '');
$password = $_POST["password"] ?? '';
$confirm_password = $_POST["confirm_password"] ?? '';
$phone = sanitize_input($_POST["phone"] ?? '');
$study_level = sanitize_input($_POST["study_level"] ?? '');
$department_id = filter_var($_POST["department_id"] ?? 0, FILTER_VALIDATE_INT);
$section = sanitize_input($_POST["section"] ?? '');

// Validation du mot de passe
$password_validation = validate_password($password, $confirm_password);
if ($password_validation !== true) {
    $error_message = $password_validation;
    return;
}

// Vérification du nombre de matières sélectionnées
if (!isset($_POST["subjects"]) || count($_POST["subjects"]) > 5 || count($_POST["subjects"]) === 0) {
    $error_message = "Veuillez sélectionner entre 1 et 5 matières.";
    return;
}

// Traitement de la photo
$photo_filename = null;
if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] != UPLOAD_ERR_NO_FILE) {
    $upload_result = upload_photo($_FILES["photo"]);
    if (isset($upload_result["error"])) {
        $error_message = $upload_result["error"];
        return;
    }
    $photo_filename = $upload_result["filename"];
}

try {
    $db->beginTransaction();

    // Insertion de l'utilisateur
    $stmt = $db->prepare("INSERT INTO users (firstname, lastname, username, password, photo, phone, study_level, department_id, section, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'tutor')");
    $stmt->execute([
        $firstname,
        $lastname,
        $username,
        password_hash($password, PASSWORD_DEFAULT),
        $photo_filename,
        $phone,
        $study_level,
        $department_id,
        $section
    ]);
    
    $user_id = $db->lastInsertId();

    // Création du tuteur
    $stmt = $db->prepare("INSERT INTO tutors (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    
    $tutor_id = $db->lastInsertId();

    // Ajout des matières
    $stmt = $db->prepare("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES (?, ?)");
    foreach ($_POST["subjects"] as $subject_id) {
        $stmt->execute([$tutor_id, $subject_id]);
    }

    // Ajout des disponibilités
    if (isset($_POST["days"]) && is_array($_POST["days"])) {
        $stmt = $db->prepare("INSERT INTO availability (user_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        foreach ($_POST["days"] as $day) {
            $start_time = $_POST["start_time_" . $day] ?? '00:00';
            $end_time = $_POST["end_time_" . $day] ?? '00:00';
            
            // Convertir le jour en format texte pour la base de données
            $days_map = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday'
            ];
            
            $stmt->execute([
                $user_id,
                $days_map[$day],
                $start_time,
                $end_time
            ]);
        }
    }

    $db->commit();
    $success_message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
    
    // Redirection après succès
    header("Location: register-tutor.php?success=1");
    exit();

} catch (Exception $e) {
    $db->rollBack();
    $error_message = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
    // Pour le debug : 
    // $error_message .= " " . $e->getMessage();
}
