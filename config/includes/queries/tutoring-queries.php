<?php
/**
 * Functions for managing tutoring relationships
 */

function getTutorPendingRequests($db, $tutor_id) {
    debug_log('queries', "Getting pending requests for tutor: $tutor_id");
    $stmt = $db->prepare("
        SELECT tr.id, tr.status, tr.created_at, tr.message,
               u.firstname, u.lastname, u.photo, u.study_level, u.section,
               s.name as subject_name, d.name as department_name,
               u.phone, u.email
        FROM tutoring_relationships tr
        JOIN tutees t ON tr.tutee_id = t.id
        JOIN users u ON t.user_id = u.id
        JOIN subjects s ON tr.subject_id = s.id
        JOIN departments d ON u.department_id = d.id
        WHERE tr.tutor_id = ? AND tr.status = 'pending'
        ORDER BY tr.created_at DESC
    ");
    $stmt->execute([$tutor_id]);
    $results = $stmt->fetchAll();
    debug_log('queries', "Found " . count($results) . " pending requests");
    return $results;
}

function getTutorActiveTutees($db, $tutor_id) {
    debug_log('queries', "Getting active tutees for tutor: $tutor_id");
    $stmt = $db->prepare("
        SELECT tr.id, tr.created_at,
               u.firstname, u.lastname, u.photo, u.study_level, u.section,
               s.name as subject_name, d.name as department_name,
               u.phone, u.email
        FROM tutoring_relationships tr
        JOIN tutees t ON tr.tutee_id = t.id
        JOIN users u ON t.user_id = u.id
        JOIN subjects s ON tr.subject_id = s.id
        JOIN departments d ON u.department_id = d.id
        WHERE tr.tutor_id = ? AND tr.status = 'accepted'
        ORDER BY u.lastname, u.firstname
    ");
    $stmt->execute([$tutor_id]);
    $results = $stmt->fetchAll();
    debug_log('queries', "Found " . count($results) . " active tutees");
    return $results;
}

function getTutorArchivedRelationships($db, $tutor_id) {
    debug_log('queries', "Getting archived relationships for tutor: $tutor_id");
    
    try {
        $stmt = $db->prepare("
            SELECT tr.id, tr.created_at, tr.archived_at, tr.archive_reason,
                   u.firstname, u.lastname,
                   s.name as subject_name
            FROM tutoring_relationships tr
            JOIN tutees t ON tr.tutee_id = t.id
            JOIN users u ON t.user_id = u.id
            JOIN subjects s ON tr.subject_id = s.id
            WHERE tr.tutor_id = ? 
            AND tr.status = 'archived'
            ORDER BY tr.archived_at DESC
            LIMIT 10
        ");
        
        $stmt->execute([$tutor_id]);
        $results = $stmt->fetchAll();
        
        debug_log('queries', "Found " . count($results) . " archived relationships", [
            'first_result' => !empty($results) ? $results[0] : null
        ]);
        
        return $results;
    } catch (Exception $e) {
        debug_log('queries', "Error getting archived relationships: " . $e->getMessage());
        throw $e;
    }
}

function getTutorId($db, $user_id) {
    debug_log('queries', "Getting tutor ID for user: $user_id");
    $stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tutor = $stmt->fetch();
    $result = $tutor ? $tutor['id'] : null;
    debug_log('queries', "Tutor ID result: " . ($result ?? 'null'));
    return $result;
}
?>