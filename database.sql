-- Création de la base de données
CREATE DATABASE IF NOT EXISTS tutorat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tutorat;

-- Table des départements
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Table des matières
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Table des utilisateurs (commune aux tuteurs et tutorés)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    photo VARCHAR(255),
    phone VARCHAR(20),
    study_level ENUM('Bloc 1', 'Bloc 2 - poursuite d\'études', 'Bloc 2 - année diplômante', 'Master') NOT NULL,
    department_id INT NOT NULL,
    section VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_type ENUM('tutor', 'tutee') NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Table des tuteurs
CREATE TABLE tutors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    max_tutees INT DEFAULT 4,
    current_tutees INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des tutorés
CREATE TABLE tutees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des disponibilités
CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_availability (user_id, day_of_week, start_time, end_time)
) ENGINE=InnoDB;

-- Table de liaison tuteurs-matières
CREATE TABLE tutor_subjects (
    tutor_id INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (tutor_id, subject_id),
    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des relations de tutorat
CREATE TABLE tutoring_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    tutee_id INT NOT NULL,
    subject_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'terminated') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE,
    FOREIGN KEY (tutee_id) REFERENCES tutees(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    UNIQUE KEY unique_relationship (tutor_id, tutee_id, subject_id)
) ENGINE=InnoDB;

-- Insertion des départements
INSERT INTO departments (name) VALUES
('Arts appliqués'),
('Économique & social'),
('Paramédical'),
('Pédagogique'),
('Technique');

-- Insertion des matières initiales
INSERT INTO subjects (name) VALUES
('Mathématiques'),
('Français'),
('Anglais'),
('HTML et CSS'),
('PHP et MySQL'),
('JavaScript');