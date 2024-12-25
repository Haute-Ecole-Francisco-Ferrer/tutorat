<?php
/**
 * Registration validation functions
 */

function validateRegistrationData($post, $files) {
    $data = [];
    
    // Validate required fields
    $required_fields = ['firstname', 'lastname', 'username', 'email', 'password', 'confirm_password', 
                       'phone', 'study_level', 'department_id', 'section'];
    
    foreach ($required_fields as $field) {
        if (empty($post[$field])) {
            throw new Exception("Le champ " . $field . " est requis.");
        }
        $data[$field] = sanitize_input($post[$field]);
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("L'adresse email n'est pas valide.");
    }

    // Validate password
    if ($data['password'] !== $data['confirm_password']) {
        throw new Exception("Les mots de passe ne correspondent pas.");
    }
    if (strlen($data['password']) < 8) {
        throw new Exception("Le mot de passe doit contenir au moins 8 caractères.");
    }

    // Handle photo upload if provided
    if (isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_photo($files['photo']);
        if (isset($upload_result['error'])) {
            throw new Exception($upload_result['error']);
        }
        $data['photo'] = $upload_result['filename'];
    }

    return $data;
}

function checkExistingUser($db, $username, $email) {
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Ce nom d'utilisateur ou cette adresse email est déjà utilisé(e).");
    }
}