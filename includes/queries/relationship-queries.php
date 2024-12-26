<?php
/**
 * Functions for managing tutoring relationships
 */

function getTutorId($db, $user_id) {
    debug_log('queries', "Getting tutor ID for user: $user_id");
    $stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tutor = $stmt->fetch();
    $result = $tutor ? $tutor['id'] : null;
    debug_log('queries', "Tutor ID result: " . ($result ?? 'null'));
    return $result;
}

function getRelationshipDetails($db, $relationship_id, $tutor_id) {
    debug_log('queries', "Getting relationship details", [
        'relationship_id' => $relationship_id,
        'tutor_id' => $tutor_id
    ]);

    $stmt = $db->prepare("
        SELECT tr.*, 
               t.user_id as tutor_user_id,
               te.user_id as tutee_user_id,
               ut.username as tutor_name,
               ut.email as tutor_email,
               ute.username as tutee_name,
               ute.email as tutee_email
        FROM tutoring_relationships tr
        JOIN tutors t ON tr.tutor_id = t.id
        JOIN tutees te ON tr.tutee_id = te.id
        JOIN users ut ON t.user_id = ut.id
        JOIN users ute ON te.user_id = ute.id
        WHERE tr.id = ? AND tr.tutor_id = ? AND tr.status = 'pending'
    ");
    $stmt->execute([$relationship_id, $tutor_id]);
    return $stmt->fetch();
}

function updateRelationshipStatus($db, $relationship_id, $status, $message) {
    debug_log('queries', "Updating relationship status", [
        'relationship_id' => $relationship_id,
        'status' => $status
    ]);

    $stmt = $db->prepare("
        UPDATE tutoring_relationships 
        SET status = ?,
            tutor_response = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    return $stmt->execute([$status, $message, $relationship_id]);
}

function updateTutorTuteeCount($db, $tutor_id) {
    debug_log('queries', "Updating tutor tutee count for tutor: $tutor_id");
    
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

function checkTutorCapacity($db, $tutor_id) {
    debug_log('queries', "Checking tutor capacity for tutor: $tutor_id");
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM tutoring_relationships 
        WHERE tutor_id = ? AND status = 'accepted'
    ");
    $stmt->execute([$tutor_id]);
    $result = $stmt->fetch();
    
    if ($result['count'] >= 4) {
        throw new Exception('Nombre maximum de tutorés atteint');
    }
}