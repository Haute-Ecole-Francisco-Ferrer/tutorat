<?php
/**
 * Validation functions for tutee registration
 */

function validateTuteeRegistration($post, $files) {
    $errors = [];
    
    // Required fields validation
    $required_fields = [
        'firstname' => 'Prénom',
        'lastname' => 'Nom',
        'username' => "Nom d'utilisateur",
        'email' => 'Email',
        'password' => 'Mot de passe',
        'confirm_password' => 'Confirmation du mot de passe',
        'phone' => 'Téléphone',
        'study_level' => "Niveau d'études",
        'department_id' => 'Département',
        'section' => 'Section'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty($post[$field])) {
            $errors[] = "Le champ {$label} est requis.";
        }
    }

    // Email validation
    if (!empty($post['email']) && !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // Password validation
    if (!empty($post['password'])) {
        if (strlen($post['password']) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if ($post['password'] !== $post['confirm_password']) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
    }

    // Phone validation
    if (!empty($post['phone']) && !preg_match("/^[0-9]{10}$/", $post['phone'])) {
        $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";
    }

    // Department validation
    if (!empty($post['department_id']) && !is_numeric($post['department_id'])) {
        $errors[] = "Le département sélectionné n'est pas valide.";
    }

    // Availability validation
    if (empty($post['days']) || !is_array($post['days'])) {
        $errors[] = "Veuillez sélectionner au moins une disponibilité.";
    }

    // Photo validation if provided
    if (isset($files['photo']) && $files['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($files['photo']['type'], $allowed_types)) {
            $errors[] = "Le format de la photo n'est pas valide. Utilisez JPG ou PNG.";
        }
        if ($files['photo']['size'] > $max_size) {
            $errors[] = "La taille de la photo ne doit pas dépasser 5MB.";
        }
    }

    return $errors;
}
?>