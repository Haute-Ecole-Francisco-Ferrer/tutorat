<?php
/**
 * Common utility functions used throughout the application
 */

/**
 * Sanitize user input
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate password
 * @param string $password Password to validate
 * @param string $confirm_password Password confirmation
 * @return true|string True if valid, error message if invalid
 */
function validate_password($password, $confirm_password) {
    if ($password !== $confirm_password) {
        return "Les mots de passe ne correspondent pas.";
    }
    
    if (strlen($password) < 8) {
        return "Le mot de passe doit contenir au moins 8 caractères.";
    }
    
    return true;
}

/**
 * Upload and process photo
 * @param array $file Uploaded file data
 * @return array Result with filename or error message
 */
function upload_photo($file) {
    $target_dir = __DIR__ . "/../uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        return ["error" => "Seuls les fichiers JPG, JPEG et PNG sont autorisés."];
    }
    
    if ($file["size"] > 5000000) {
        return ["error" => "Le fichier est trop volumineux (max 5MB)."];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["filename" => $new_filename];
    }
    
    return ["error" => "Une erreur est survenue lors du téléchargement."];
}

/**
 * Get all departments
 * @param PDO $db Database connection
 * @return array List of departments
 */
function get_departments($db) {
    $stmt = $db->query("SELECT * FROM departments ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get all subjects
 * @param PDO $db Database connection
 * @return array List of subjects
 */
function get_subjects($db) {
    $stmt = $db->query("SELECT * FROM subjects ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Format flash message
 * @param string $message Message content
 * @param string $type Message type (error/success)
 * @return string Formatted HTML message
 */
function format_flash_message($message, $type = 'error') {
    return "<div class='alert alert-{$type}'>{$message}</div>";
}