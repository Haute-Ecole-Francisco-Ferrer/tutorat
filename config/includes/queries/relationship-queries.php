<?php
/**
 * Functions for managing tutoring relationships
 */

function getTuteeId($db, $user_id) {
    $stmt = $db->prepare("SELECT id FROM tutees WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tutee = $stmt->fetch();
    return $tutee ? $tutee['id'] : null;
}

function checkTutorAvailability($db, $tutor_id) {
    // Check if tutor has reached maximum tutees
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM tutoring_relationships 
        WHERE tutor_id = ? AND status = 'accepted'
    ");
    $stmt->execute([$tutor_id]);
    $result = $stmt->fetch();
    
    if ($result['count'] >= 4) {
        throw new Exception("Ce tuteur a déjà atteint son nombre maximum de tutorés.");
    }
}

function checkExistingRequest($db, $tutor_id, $tutee_id, $subject_id) {
    $stmt = $db->prepare("
        SELECT id 
        FROM tutoring_relationships 
        WHERE tutor_id = ? AND tutee_id = ? AND subject_id = ? 
        AND status IN ('pending', 'accepted')
    ");
    $stmt->execute([$tutor_id, $tutee_id, $subject_id]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception("Une demande est déjà en cours pour cette matière avec ce tuteur.");
    }
}

function createTutoringRequest($db, $tutor_id, $tutee_id, $subject_id, $message) {
    $stmt = $db->prepare("
        INSERT INTO tutoring_relationships 
            (tutor_id, tutee_id, subject_id, message, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    
    if (!$stmt->execute([$tutor_id, $tutee_id, $subject_id, $message])) {
        throw new Exception("Erreur lors de la création de la demande.");
    }
    
    return $db->lastInsertId();
}