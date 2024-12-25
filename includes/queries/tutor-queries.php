<?php
/**
 * Get latest tutors with their details
 */
function getLatestTutors($db, $limit = 6) {
    $query = "
        SELECT 
            u.id,
            u.firstname,
            u.lastname,
            u.photo,
            u.username,
            d.name as department_name,
            GROUP_CONCAT(DISTINCT s.name) as subjects,
            t.id as tutor_id,
            (
                SELECT COUNT(*)
                FROM tutoring_relationships tr
                WHERE tr.tutor_id = t.id
                AND tr.status = 'accepted'
            ) as current_tutees
        FROM users u
        INNER JOIN departments d ON u.department_id = d.id
        INNER JOIN tutors t ON u.id = t.user_id
        LEFT JOIN tutor_subjects ts ON t.id = ts.tutor_id
        LEFT JOIN subjects s ON ts.subject_id = s.id
        WHERE u.user_type = 'tutor'
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT ?
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get filtered tutors list
 */
function getFilteredTutors($db, $department_id = null, $subject_id = null) {
    $query = "
        SELECT 
            u.id,
            u.firstname,
            u.lastname,
            u.photo,
            u.username,
            d.name as department_name,
            GROUP_CONCAT(DISTINCT s.name) as subjects,
            t.id as tutor_id,
            (
                SELECT COUNT(*)
                FROM tutoring_relationships tr
                WHERE tr.tutor_id = t.id
                AND tr.status = 'accepted'
            ) as current_tutees
        FROM users u
        INNER JOIN departments d ON u.department_id = d.id
        INNER JOIN tutors t ON u.id = t.user_id
        LEFT JOIN tutor_subjects ts ON t.id = ts.tutor_id
        LEFT JOIN subjects s ON ts.subject_id = s.id
        WHERE u.user_type = 'tutor'
    ";

    $params = [];
    
    if ($department_id) {
        $query .= " AND u.department_id = ?";
        $params[] = $department_id;
    }

    if ($subject_id) {
        $query .= " AND EXISTS (
            SELECT 1 FROM tutor_subjects ts2 
            WHERE ts2.tutor_id = t.id 
            AND ts2.subject_id = ?
        )";
        $params[] = $subject_id;
    }

    $query .= " GROUP BY u.id ORDER BY u.lastname, u.firstname";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get tutors' availabilities
 */
function getTutorsAvailabilities($db, $tutor_ids) {
    if (empty($tutor_ids)) {
        return [];
    }

    $query = "
        SELECT user_id, 
               day_of_week, 
               DATE_FORMAT(start_time, '%H:%i') as start_time,
               DATE_FORMAT(end_time, '%H:%i') as end_time
        FROM availability
        WHERE user_id IN (" . implode(',', $tutor_ids) . ")
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
    ";
    
    $availabilities = $db->query($query)->fetchAll();
    
    $availability_by_tutor = [];
    foreach ($availabilities as $availability) {
        $availability_by_tutor[$availability['user_id']][] = $availability;
    }
    
    return $availability_by_tutor;
}
?>