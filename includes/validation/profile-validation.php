<?php
/**
 * Profile validation functions
 */

function validateProfileData($post, $files) {
    $data = [];
    
    // Validate required fields
    $required_fields = ['firstname', 'lastname', 'email', 'phone', 'study_level', 'department_id', 'section'];
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

function updateUserProfile($db, $user_id, $data) {
    // Check if email is already taken by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$data['email'], $user_id]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Cette adresse email est déjà utilisée.");
    }

    $query = "UPDATE users SET 
              firstname = ?, 
              lastname = ?, 
              email = ?,
              phone = ?, 
              study_level = ?, 
              department_id = ?, 
              section = ?";
    
    $params = [
        $data['firstname'],
        $data['lastname'],
        $data['email'],
        $data['phone'],
        $data['study_level'],
        $data['department_id'],
        $data['section']
    ];

    if (isset($data['photo'])) {
        $query .= ", photo = ?";
        $params[] = $data['photo'];
    }

    $query .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $db->prepare($query);
    return $stmt->execute($params);
}