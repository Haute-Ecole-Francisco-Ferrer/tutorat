<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/tutee-validation.php';

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

    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (
            firstname, lastname, username, email, password, 
            photo, phone, study_level, department_id, section, user_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'tutee')
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