<?php
/**
 * Functions for managing tutoring relationships
 */

function createTutoringRequest($db, $tutor_id, $tutee_id, $subject_id, $message) {
    $stmt = $db->prepare("
        INSERT INTO tutoring_relationships (tutor_id, tutee_id, subject_id, message, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    return $stmt->execute([$tutor_id, $tutee_id, $subject_id, $message]);
}

function updateTutoringRequest($db, $request_id, $status, $tutor_response = null) {
    $stmt = $db->prepare("
        UPDATE tutoring_relationships 
        SET status = ?, 
            tutor_response = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$status, $tutor_response, $request_id]);
}

function updateTutorCurrentTutees($db, $tutor_id) {
    $stmt = $db->prepare("
        UPDATE tutors 
        SET current_tutees = (
            SELECT COUNT(*) 
            FROM tutoring_relationships 
            WHERE tutor_id = ? 
            AND status = 'accepted'
        )
        WHERE id = ?
    ");
    return $stmt->execute([$tutor_id, $tutor_id]);
}
?>