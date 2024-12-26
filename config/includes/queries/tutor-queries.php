<?php
/**
 * Tutor query functions
 */

/**
 * Get latest tutors with their details
 * @param PDO $db Database connection
 * @param int|null $department_id Optional department filter
 * @param int $limit Number of tutors to return
 * @return array Array of tutor records
 */
function getLatestTutors($db, $department_id = null, $limit = 6) {
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

    $query .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get filtered tutors list
 * @param PDO $db Database connection
 * @param int|null $department_id Optional department filter
 * @param int|null $subject_id Optional subject filter
 * @return array Array of tutor records
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
 * Get tutor availabilities
 * @param PDO $db Database connection
 * @param array $tutor_ids Array of tutor IDs
 * @return array Array of availabilities indexed by tutor ID
 */
function getTutorsAvailabilities($db, $tutor_ids) {
    if (empty($tutor_ids)) {
        return [];
    }

    $placeholders = str_repeat('?,', count($tutor_ids) - 1) . '?';
    $query = "
        SELECT user_id, 
               day_of_week, 
               DATE_FORMAT(start_time, '%H:%i') as start_time,
               DATE_FORMAT(end_time, '%H:%i') as end_time
        FROM availability
        WHERE user_id IN ($placeholders)
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
    ";

    $stmt = $db->prepare($query);
    $stmt->execute($tutor_ids);
    $availabilities = $stmt->fetchAll();

    $result = [];
    foreach ($availabilities as $availability) {
        $result[$availability['user_id']][] = $availability;
    }

    return $result;
}