<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_password($password, $confirm_password) {
    if ($password !== $confirm_password) {
        return "Les mots de passe ne correspondent pas.";
    }
    
    if (strlen($password) < 8) {
        return "Le mot de passe doit contenir au moins 8 caractères.";
    }
    
    return true;
}

function upload_photo($file) {
    $target_dir = __DIR__ . "/../uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Vérifier le type de fichier
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        return ["error" => "Seuls les fichiers JPG, JPEG et PNG sont autorisés."];
    }
    
    // Vérifier la taille (max 5MB)
    if ($file["size"] > 5000000) {
        return ["error" => "Le fichier est trop volumineux (max 5MB)."];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["filename" => $new_filename];
    } else {
        return ["error" => "Une erreur est survenue lors du téléchargement."];
    }
}

function get_departments($db) {
    $stmt = $db->query("SELECT * FROM departments ORDER BY name");
    return $stmt->fetchAll();
}

function get_subjects($db) {
    $stmt = $db->query("SELECT * FROM subjects ORDER BY name");
    return $stmt->fetchAll();
}

function format_flash_message($message, $type = 'error') {
    return "<div class='alert alert-{$type}'>{$message}</div>";
}
