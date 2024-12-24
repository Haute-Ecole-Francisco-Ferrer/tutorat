-- Insertion des tuteurs
INSERT INTO users (firstname, lastname, username, password, photo, phone, study_level, department_id, section, user_type) VALUES
('Thomas', 'Dubois', 'tdubois', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0472123456', 'Bloc 2 - année diplômante', 5, 'Informatique de gestion', 'tutor'),
('Marie', 'Laurent', 'mlaurent', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0473234567', 'Master', 2, 'Sciences économiques', 'tutor'),
('Lucas', 'Lefèvre', 'llefevre', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0474345678', 'Bloc 2 - poursuite d\'études', 1, 'Design graphique', 'tutor'),
('Emma', 'Martin', 'emartin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0475456789', 'Master', 4, 'Langues romanes', 'tutor'),
('Antoine', 'Bernard', 'abernard', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0476567890', 'Bloc 2 - année diplômante', 3, 'Soins infirmiers', 'tutor');

-- Insertion des tutorés
INSERT INTO users (firstname, lastname, username, password, photo, phone, study_level, department_id, section, user_type) VALUES
('Sophie', 'Dupont', 'sdupont', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0477678901', 'Bloc 1', 5, 'Informatique de gestion', 'tutee'),
('Jules', 'Moreau', 'jmoreau', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0478789012', 'Bloc 1', 2, 'Sciences économiques', 'tutee'),
('Léa', 'Petit', 'lpetit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0479890123', 'Bloc 1', 1, 'Design graphique', 'tutee'),
('Hugo', 'Roux', 'hroux', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0470901234', 'Bloc 1', 4, 'Langues germaniques', 'tutee'),
('Clara', 'Simon', 'csimon', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0471012345', 'Bloc 1', 3, 'Soins infirmiers', 'tutee');

-- Création des entrées dans la table tutors pour les nouveaux tuteurs uniquement
INSERT INTO tutors (user_id)
SELECT id 
FROM users 
WHERE user_type = 'tutor' 
AND id NOT IN (SELECT user_id FROM tutors);

-- Création des entrées dans la table tutees pour les nouveaux tutorés uniquement
INSERT INTO tutees (user_id)
SELECT id 
FROM users 
WHERE user_type = 'tutee' 
AND id NOT IN (SELECT user_id FROM tutees);

-- Ajout des matières pour les nouveaux tuteurs uniquement
INSERT INTO tutor_subjects (tutor_id, subject_id)
SELECT 
    t.id,
    s.id
FROM tutors t
CROSS JOIN subjects s
WHERE NOT EXISTS (
    SELECT 1 
    FROM tutor_subjects ts 
    WHERE ts.tutor_id = t.id AND ts.subject_id = s.id
)
AND s.id <= 3
ORDER BY t.id, s.id;

-- Ajout des disponibilités pour les nouveaux utilisateurs uniquement
INSERT INTO availability (user_id, day_of_week, start_time, end_time)
SELECT DISTINCT
    u.id,
    CASE numbers.n
        WHEN 1 THEN 'Monday'
        WHEN 2 THEN 'Wednesday'
        WHEN 3 THEN 'Friday'
    END,
    CASE numbers.n
        WHEN 1 THEN '09:00'
        WHEN 2 THEN '14:00'
        WHEN 3 THEN '16:00'
    END,
    CASE numbers.n
        WHEN 1 THEN '11:00'
        WHEN 2 THEN '16:00'
        WHEN 3 THEN '18:00'
    END
FROM users u
CROSS JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3) numbers
WHERE NOT EXISTS (
    SELECT 1 
    FROM availability a 
    WHERE a.user_id = u.id
);