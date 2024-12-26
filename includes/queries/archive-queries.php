<?php
/**
 * Functions for managing archived tutoring relationships
 */

function archiveRelationship($db, $relationship_id, $tutor_id, $archive_reason) {
    // Verify relationship exists and belongs to tutor
    $stmt = $db->prepare("
        SELECT tr.*, 
               t.user_id as tutor_user_id,
               te.user_id as tutee_user_id,
               ut.username as tutor_name,
               ut.email as tutor_email,
               ute.username as tutee_name,
               ute.email as tutee_email,
               s.name as subject_name
        FROM tutoring_relationships tr
        JOIN tutors t ON tr.tutor_id = t.id
        JOIN tutees te ON tr.tutee_id = te.id
        JOIN users ut ON t.user_id = ut.id
        JOIN users ute ON te.user_id = ute.id
        JOIN subjects s ON tr.subject_id = s.id
        WHERE tr.id = ? AND tr.tutor_id = ? AND tr.status = 'accepted'
    ");
    $stmt->execute([$relationship_id, $tutor_id]);
    $relationship = $stmt->fetch();

    if (!$relationship) {
        throw new Exception('Relation de tutorat introuvable ou non autorisée');
    }

    // Update status to archived
    $stmt = $db->prepare("
        UPDATE tutoring_relationships 
        SET status = 'archived',
            archive_reason = ?
        WHERE id = ?
    ");
    $stmt->execute([$archive_reason, $relationship_id]);

    return $relationship;
}

function getArchivedRelationships($db, $tutor_id, $limit = 10) {
    $stmt = $db->prepare("
        SELECT tra.*,
               u.firstname, u.lastname,
               s.name as subject_name
        FROM tutoring_relationships_archive tra
        JOIN tutees t ON tra.tutee_id = t.id
        JOIN users u ON t.user_id = u.id
        JOIN subjects s ON tra.subject_id = s.id
        WHERE tra.tutor_id = ?
        ORDER BY tra.archived_at DESC
        LIMIT ?
    ");
    $stmt->execute([$tutor_id, $limit]);
    return $stmt->fetchAll();
}