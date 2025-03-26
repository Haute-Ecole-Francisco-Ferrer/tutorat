-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Mar 26, 2025 at 12:36 PM
-- Server version: 5.7.39
-- PHP Version: 8.1.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `tutorat`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `department_id`, `created_at`) VALUES
(1, 16, 5, '2024-12-26 12:46:10'),
(2, 22, 5, '2025-01-10 13:07:53'),
(3, 42, 4, '2025-03-18 13:39:47'),
(4, 43, 2, '2025-03-19 08:41:39');

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `user_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 1, 'Monday', '09:00:00', '07:00:00'),
(2, 1, 'Saturday', '08:00:00', '07:00:00'),
(3, 2, 'Monday', '07:00:00', '07:00:00'),
(4, 13, 'Monday', '07:00:00', '07:00:00'),
(5, 13, 'Tuesday', '07:00:00', '07:00:00'),
(18, 14, 'Monday', '07:00:00', '12:00:00'),
(19, 14, 'Tuesday', '15:45:00', '18:49:00'),
(20, 14, 'Wednesday', '17:08:00', '20:08:00'),
(7, 15, 'Thursday', '07:00:00', '07:00:00'),
(39, 25, 'Saturday', '14:00:00', '17:00:00'),
(40, 25, 'Sunday', '14:00:00', '17:00:00'),
(48, 28, 'Monday', '08:00:00', '10:00:00'),
(49, 28, 'Tuesday', '08:00:00', '10:00:00'),
(50, 28, 'Wednesday', '12:00:00', '13:00:00'),
(51, 28, 'Friday', '08:00:00', '09:00:00'),
(28, 31, 'Monday', '16:00:00', '18:00:00'),
(29, 31, 'Tuesday', '18:00:00', '23:00:00'),
(30, 31, 'Wednesday', '16:00:00', '23:00:00'),
(31, 31, 'Thursday', '18:00:00', '23:00:00'),
(32, 31, 'Friday', '16:00:00', '23:00:00'),
(33, 31, 'Saturday', '07:00:00', '23:00:00'),
(34, 31, 'Sunday', '07:00:00', '23:00:00'),
(35, 32, 'Monday', '09:00:00', '14:30:00'),
(36, 32, 'Tuesday', '13:30:00', '17:00:00'),
(37, 32, 'Wednesday', '14:00:00', '17:00:00'),
(38, 32, 'Thursday', '13:30:00', '16:30:00'),
(41, 37, 'Monday', '08:00:00', '23:00:00'),
(42, 37, 'Tuesday', '08:00:00', '23:00:00'),
(43, 37, 'Wednesday', '14:00:00', '23:00:00'),
(44, 37, 'Thursday', '13:00:00', '23:00:00'),
(45, 37, 'Friday', '08:00:00', '12:00:00'),
(46, 37, 'Saturday', '07:00:00', '23:00:00'),
(47, 37, 'Sunday', '08:00:00', '14:00:00'),
(52, 38, 'Monday', '07:00:00', '07:00:00'),
(53, 38, 'Tuesday', '07:00:00', '07:00:00'),
(54, 38, 'Saturday', '07:00:00', '07:00:00'),
(55, 41, 'Monday', '07:00:00', '07:30:00'),
(56, 42, 'Monday', '07:00:00', '10:00:00'),
(57, 43, 'Monday', '07:00:00', '07:00:00'),
(58, 43, 'Tuesday', '07:00:00', '07:00:00'),
(59, 43, 'Wednesday', '07:00:00', '07:00:00'),
(60, 45, 'Monday', '07:00:00', '13:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'Arts appliqués'),
(2, 'Économique & social'),
(3, 'Paramédical'),
(4, 'Pédagogique'),
(5, 'Technique');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `created_at`, `expires_at`, `used`) VALUES
(2, 25, 'd47b0b0a95054b21b3a0da0c3a54cdba2bc029919a2c5af6738bc315c81a0dfd', '2025-03-26 10:18:50', '2025-03-27 10:18:50', 1),
(3, 14, '55ef3bc6eafd6e00ae3306da74fe30499f001cde37cf80e226c9cd23bb9a93d4', '2025-03-26 11:39:22', '2025-03-27 10:39:22', 1),
(4, 15, '39ef2dc68ccef6b325314f3b8ec6fd90e1d4c4269548b4e93303b54c64d8c547', '2025-03-26 11:41:09', '2025-03-27 10:41:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `department_id`, `name`) VALUES
(1, 2, 'Mathématiques'),
(2, 3, 'Français'),
(3, 4, 'Anglais'),
(4, 5, 'HTML et CSS'),
(5, 5, 'PHP et MySQL'),
(6, 5, 'JavaScript'),
(7, 1, 'test A'),
(9, 5, 'Mathématique 1 (en électronique)'),
(10, 5, 'Base de l\'électricité 1'),
(11, 5, 'Semiconducteurs'),
(12, 5, 'Electronique analogique 1'),
(13, 5, 'Electronique numérique 1 (théorie)'),
(14, 5, 'Informatique 1 (électronique)'),
(15, 5, 'Mathématique 2 (électronique)'),
(16, 5, 'Base de l\'électricité 2'),
(17, 5, 'Electronique analogique 2'),
(18, 5, 'Electronique numérique 2 (théorie)'),
(19, 5, 'Informatique 2 (électronique)'),
(20, 5, 'Dessin et création graphique Q1'),
(21, 5, 'Dessin et création graphique Q2'),
(22, 5, 'Infographie 2D Q1'),
(23, 5, 'Infographie 2D Q2'),
(24, 5, 'Composition Q1'),
(25, 5, 'Composition Q2'),
(26, 5, 'Mise en page Q1'),
(27, 5, 'Mise en page Q2'),
(28, 5, 'Technologie Q1'),
(29, 5, 'Communication écrite et visuelle Q1'),
(30, 5, 'Communication écrite et visuelle Q2'),
(31, 5, 'Histoire de l\'Art Q1'),
(32, 5, 'Histoire de l\'Art Q2'),
(33, 5, 'Mathématiques Q1 (infographie)'),
(34, 5, 'Initiation à la programmation Q1'),
(35, 5, 'Design et référencement Web Q2'),
(36, 5, 'Base de données Q2'),
(37, 5, 'Photographie Q2'),
(38, 5, 'Optique et mathématiques appliquées Q2'),
(39, 5, 'Infographie 3D Q2');

-- --------------------------------------------------------

--
-- Table structure for table `tutees`
--

CREATE TABLE `tutees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tutees`
--

INSERT INTO `tutees` (`id`, `user_id`) VALUES
(1, 13),
(2, 15),
(7, 27),
(8, 29),
(9, 33),
(10, 36),
(11, 39),
(12, 44);

-- --------------------------------------------------------

--
-- Table structure for table `tutoring_relationships`
--

CREATE TABLE `tutoring_relationships` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `tutee_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected','terminated') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `message` text,
  `tutor_response` text,
  `archived_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archive_reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tutoring_relationships`
--

INSERT INTO `tutoring_relationships` (`id`, `tutor_id`, `tutee_id`, `subject_id`, `status`, `created_at`, `updated_at`, `message`, `tutor_response`, `archived_at`, `archive_reason`) VALUES
(4, 14, 2, 4, 'pending', '2024-12-26 10:39:38', '2025-03-26 12:28:17', 'azdda', 'no', '0000-00-00 00:00:00', NULL),
(8, 14, 2, 6, 'pending', '2024-12-26 10:44:31', '2025-03-26 12:28:17', 'zazd', 'oui', '0000-00-00 00:00:00', NULL),
(14, 14, 2, 2, 'pending', '2024-12-26 12:31:02', '2025-03-26 12:28:17', 'FR', '', '0000-00-00 00:00:00', NULL),
(16, 14, 2, 1, 'pending', '2024-12-26 14:26:50', '2025-03-26 12:28:17', 'math', '', '0000-00-00 00:00:00', NULL),
(18, 14, 2, 3, 'pending', '2024-12-26 14:39:11', '2025-03-26 12:28:17', 'en', NULL, '0000-00-00 00:00:00', NULL),
(20, 14, 2, 5, 'pending', '2024-12-27 11:57:19', '2025-03-26 12:28:17', 'wsdfsd', NULL, '0000-00-00 00:00:00', NULL),
(23, 14, 2, 7, 'pending', '2025-01-09 14:11:05', '2025-03-26 12:28:17', 'dfgzegzze aef aef', 'ok', '0000-00-00 00:00:00', NULL),
(27, 19, 10, 4, 'accepted', '2025-02-18 20:36:10', '2025-02-19 06:29:03', 'Salut, je n\'ai pas encore suivi de cours de web, j\'ai du mal à suivre de part le prof et j\'aurai besoin d\'aide en reprenant du début si possible. Merci', NULL, '0000-00-00 00:00:00', NULL),
(28, 21, 12, 4, 'accepted', '2025-03-24 17:12:26', '2025-03-25 14:01:06', 'Salut. tu vas bien, je te contacte pour savoir si tu peux me prendre pour le cours de programmation afin que je puisse reussir ce cours en juin.\r\nmerci et à bientot', NULL, '0000-00-00 00:00:00', NULL),
(29, 18, 12, 4, 'accepted', '2025-03-24 17:14:33', '2025-03-25 14:01:04', 'salut cv, puisse je te déranger pour avoir un peut de ton temps pour me donner des cours de programmation afin que je reussise ce cours.', NULL, '0000-00-00 00:00:00', NULL);

--
-- Triggers `tutoring_relationships`
--
DELIMITER $$
CREATE TRIGGER `archive_tutoring_relationship_trigger` AFTER UPDATE ON `tutoring_relationships` FOR EACH ROW BEGIN
    IF NEW.status = 'archived' THEN
        INSERT INTO tutoring_relationships_archive (
            id, original_id, tutor_id, tutee_id, subject_id,
            status, message, tutor_response, created_at,
            archived_at, archive_reason
        )
        VALUES (
            UUID(), OLD.id, OLD.tutor_id, OLD.tutee_id, OLD.subject_id,
            OLD.status, OLD.message, OLD.tutor_response, OLD.created_at,
            CURRENT_TIMESTAMP, NEW.archive_reason
        );
        
        DELETE FROM tutoring_relationships WHERE id = OLD.id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tutoring_relationships_archive`
--

CREATE TABLE `tutoring_relationships_archive` (
  `id` char(36) NOT NULL,
  `original_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `tutee_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` text,
  `tutor_response` text,
  `created_at` datetime NOT NULL,
  `archived_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archive_reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tutoring_relationships_archive`
--

INSERT INTO `tutoring_relationships_archive` (`id`, `original_id`, `tutor_id`, `tutee_id`, `subject_id`, `status`, `message`, `tutor_response`, `created_at`, `archived_at`, `archive_reason`) VALUES
('692b863c-f02c-11ef-b77b-30e171630b0c', 25, 19, 7, 4, 'archived', 'revision', NULL, '2025-02-03 10:27:41', '2025-02-21 09:18:19', 'test'),
('6e3114bb-f02c-11ef-b77b-30e171630b0c', 26, 19, 8, 5, 'archived', 'blablabalablablablablabalbalablabalbaaalababaababblaablblablablbalblabblablablablblabbablablablablablblablabllbablaa', NULL, '2025-02-03 10:27:42', '2025-02-21 09:18:27', 'test'),
('cbaaf352-0a3d-11f0-a4b7-f33e5d175e91', 9, 14, 2, 1, 'archived', 'azdza', '', '2024-12-26 12:02:03', '2024-12-26 13:29:10', 'fin'),
('d5e6f34e-c396-11ef-adaa-30e171630b0c', 13, 14, 2, 3, 'archived', 'en', 'en ok', '2024-12-26 13:19:29', '2024-12-26 15:36:43', 'fin EN');

--
-- Triggers `tutoring_relationships_archive`
--
DELIMITER $$
CREATE TRIGGER `before_insert_tutoring_archive` BEFORE INSERT ON `tutoring_relationships_archive` FOR EACH ROW BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tutors`
--

CREATE TABLE `tutors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `max_tutees` int(11) DEFAULT '4',
  `current_tutees` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tutors`
--

INSERT INTO `tutors` (`id`, `user_id`, `max_tutees`, `current_tutees`) VALUES
(1, 1, 4, 0),
(2, 2, 4, 0),
(4, 3, 4, 0),
(5, 4, 4, 0),
(6, 5, 4, 0),
(7, 6, 4, 0),
(8, 7, 4, 0),
(9, 8, 4, 0),
(10, 9, 4, 0),
(11, 10, 4, 0),
(12, 11, 4, 0),
(13, 12, 4, 0),
(14, 14, 4, 3),
(18, 25, 4, 1),
(19, 28, 4, 1),
(20, 31, 4, 0),
(21, 32, 4, 1),
(22, 37, 4, 0),
(23, 38, 4, 0),
(24, 41, 4, 0),
(25, 42, 4, 0),
(26, 43, 4, 0),
(27, 45, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tutor_subjects`
--

CREATE TABLE `tutor_subjects` (
  `tutor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tutor_subjects`
--

INSERT INTO `tutor_subjects` (`tutor_id`, `subject_id`) VALUES
(14, 1),
(26, 1),
(2, 2),
(14, 2),
(1, 3),
(14, 3),
(25, 3),
(14, 4),
(18, 4),
(19, 4),
(21, 4),
(14, 5),
(21, 5),
(1, 6),
(14, 6),
(14, 7),
(27, 12),
(20, 15),
(24, 15),
(20, 16),
(20, 17),
(20, 18),
(24, 22),
(27, 25),
(23, 32),
(19, 33),
(22, 33),
(23, 33),
(21, 35),
(27, 35);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `study_level` enum('Bloc 1','Bloc 2 - poursuite d''études','Bloc 2 - année diplômante','Master') NOT NULL,
  `department_id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_type` enum('tutor','tutee') NOT NULL,
  `status` enum('pending','published','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `photo`, `phone`, `study_level`, `department_id`, `section`, `created_at`, `updated_at`, `user_type`, `status`) VALUES
(1, 'emmanuel', 'lemal', 'man', '', '$2y$10$wzICPTIa8T8vHXy3SyR6p.dVJnDxlctgJ55ySsMwtXbxrX.1at79K', '676978b7d2b66.jpg', '0478945140', 'Bloc 1', 2, 'azazd', '2024-12-23 14:50:32', '2025-02-12 11:51:29', 'tutor', 'rejected'),
(2, 'erergerg', 'regere', 'ergreg', '', '$2y$10$XNgQK2KRY6.CM9KypHC8heeucF04tA9KSuwrdH93FU54iSlD6gWlO', '67697f3c50de1.png', '0478945140', 'Bloc 1', 2, 'yttyt', '2024-12-23 15:18:20', '2025-02-12 11:51:26', 'tutor', 'rejected'),
(3, 'Thomas', 'Dubois', 'tdubois', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0472123456', 'Bloc 2 - année diplômante', 5, 'Informatique de gestion', '2024-12-23 15:20:33', '2025-02-12 11:51:36', 'tutor', 'rejected'),
(4, 'Marie', 'Laurent', 'mlaurent', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0473234567', 'Master', 2, 'Sciences économiques', '2024-12-23 15:20:33', '2025-02-12 11:51:25', 'tutor', 'rejected'),
(5, 'Lucas', 'Lefèvre', 'llefevre', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0474345678', 'Bloc 2 - poursuite d\'études', 1, 'Design graphique', '2024-12-23 15:20:33', '2025-02-12 11:51:23', 'tutor', 'rejected'),
(6, 'Emma', 'Martin', 'emartin', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0475456789', 'Master', 4, 'Langues romanes', '2024-12-23 15:20:33', '2025-02-12 11:51:33', 'tutor', 'rejected'),
(7, 'Antoine', 'Bernard', 'abernard', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0476567890', 'Bloc 2 - année diplômante', 3, 'Soins infirmiers', '2024-12-23 15:20:33', '2025-02-12 11:51:31', 'tutor', 'rejected'),
(8, 'Sophie', 'Dupont', 'sdupont', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0477678901', 'Bloc 1', 5, 'Informatique de gestion', '2024-12-23 15:20:33', '2025-02-12 11:51:15', 'tutee', 'rejected'),
(9, 'Jules', 'Moreau', 'jmoreau', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0478789012', 'Bloc 1', 2, 'Sciences économiques', '2024-12-23 15:20:33', '2025-02-12 11:51:05', 'tutee', 'rejected'),
(10, 'Léa', 'Petit', 'lpetit', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0479890123', 'Bloc 1', 1, 'Design graphique', '2024-12-23 15:20:33', '2025-02-12 11:51:01', 'tutee', 'rejected'),
(11, 'Hugo', 'Roux', 'hroux', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0470901234', 'Bloc 1', 4, 'Langues germaniques', '2024-12-23 15:20:33', '2025-02-12 11:51:09', 'tutee', 'rejected'),
(12, 'Clara', 'Simon', 'csimon', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '676978b7d2b66.jpg', '0471012345', 'Bloc 1', 3, 'Soins infirmiers', '2024-12-23 15:20:33', '2025-02-12 11:51:07', 'tutee', 'rejected'),
(13, 'emmanuel', 'lemal', 'man123', '', '$2y$10$AEYSwg4YArrs9eCP4XM/Mef9coseUWwVcuFlPoGvDlC0befZHAPFq', NULL, '0478945140', 'Bloc 2 - année diplômante', 2, 'azdazd', '2024-12-24 11:25:23', '2025-02-12 11:51:03', 'tutee', 'rejected'),
(14, 'tuteur', 'tuteur', 'tuteur', 'emmanuel.lemal@he-ferrer.eu', '$2y$10$nZc9imu8JmeqL1QGiZr2EONGy2J6O75zDguqrZIqH9OU9YusbRklu', '676a9a8e113b6.png', '0478945140', 'Bloc 1', 5, 'dzefertr', '2024-12-24 11:27:10', '2025-03-26 11:40:02', 'tutor', 'rejected'),
(15, 'tutore', 'tutore', 'tutore', 'manuman73@gmail.com', '$2y$10$PU70qSO368LYIc5ygRuW8ukwO9Shp.44Ne1UgF2eEP4qLCLeFA.qm', '676a9b0b7d7db.png', '0478945140', 'Bloc 1', 5, 'azdaz', '2024-12-24 11:29:15', '2025-03-26 11:41:42', 'tutee', 'rejected'),
(16, 'Emmanuel', 'Lemal', 'elemal', 'emmanuel.lemal@he-ferrer.eu', '$2y$10$Sfx6NalSWjvzkTFp4iGkKOF1ps7fF3yotsJ3UEt4hH7mxfUvx01re', NULL, NULL, 'Bloc 1', 5, '', '2024-12-26 12:45:08', '2024-12-26 12:45:08', '', 'pending'),
(22, 'Isabelle', 'Estercq', 'isabelle', 'isabelle.estercq@he-ferrer.eu', '$2y$10$Sfx6NalSWjvzkTFp4iGkKOF1ps7fF3yotsJ3UEt4hH7mxfUvx01re', NULL, NULL, '', 5, '', '2025-01-10 13:06:35', '2025-01-10 13:06:35', '', 'pending'),
(25, 'Alex', 'Xiao', 'Alex', 'alex.xiao.26215@stu.he-ferrer.eu', '$2y$10$mGvg1aF34WOBn0EYSeF34eWC8mcn0Ewj45maPdZhok0qfNuURfiyu', '67a08b1b3d5ea.jpg', '1234567890', 'Bloc 2 - poursuite d\'études', 5, 'web design', '2025-02-03 09:23:39', '2025-03-26 10:19:29', 'tutor', 'published'),
(26, 'Taïchi', 'Magritte', 'Taïchi', 'taichi.magritte.25930@stu.he-ferrer.eu', '$2y$10$Gd1JTdFuNNDlfJJa3q5G6uZ7pGndVywipOnUuTojG3oZz3K.h3qsu', NULL, '0471992607', 'Bloc 2 - poursuite d\'études', 5, 'Web', '2025-02-03 09:23:49', '2025-02-12 11:51:12', 'tutee', 'rejected'),
(27, 'Nuria Sofia', 'Ramos', 'nuriasofia12', 'nuriasofia12@hotmail.com', '$2y$10$eZSw.R1V7uBzi8wziA0HheL9BRwkhcArx7TxUTEcrR7EMD/n0F3Ny', NULL, '0466312358', 'Bloc 2 - poursuite d\'études', 5, 'Web', '2025-02-03 09:24:11', '2025-02-12 11:51:11', 'tutee', 'rejected'),
(28, 'Aliyar', 'Demir', 'aliyar', 'aliyar.demir.25841@stu.he-ferrer.eu', '$2y$10$as8M21XQnT3tYduoP5ohQeByshkufa7KW/uYF3xABXKZEzdD48kxa', NULL, '0487917652', 'Bloc 2 - poursuite d\'études', 5, 'technique infographique web', '2025-02-03 09:24:17', '2025-02-03 09:26:05', 'tutor', 'published'),
(29, 'rayan', 'thamers', 'rayaned', 'rayan.thamers@gmail.com', '$2y$10$lwFUmYA60J/wVuDefDAXueLX28yfXjGsAOuN/OpLtXDSFb5lkAH2O', NULL, '0483729681', 'Bloc 2 - poursuite d\'études', 5, 'Web', '2025-02-03 09:24:23', '2025-02-12 11:51:10', 'tutee', 'rejected'),
(30, 'Virginie', 'Jossart', 'virginie', 'virginie.jossart@he-ferrer.eu', '$2y$10$Sfx6NalSWjvzkTFp4iGkKOF1ps7fF3yotsJ3UEt4hH7mxfUvx01re', NULL, NULL, '', 5, '', '2025-02-11 14:07:37', '2025-02-11 14:08:12', '', 'pending'),
(31, 'Dolvis ', 'Tagou Timamah', 'Dolvis ', 'dolvis.tagou.24129@stu.he-ferrer.eu', '$2y$10$J6L9LTDIQl32PtlAqE0nDOjM/cPr6IK.9HErMxoTMcSp7x2rLqNQy', NULL, '0466459903', 'Bloc 2 - poursuite d\'études', 5, 'Électronique appliquée ', '2025-02-12 13:45:46', '2025-02-19 06:28:31', 'tutor', 'published'),
(32, 'Souraya', 'Wachrine', 'Souraya', 'souraya.wachrine.23394@stu.he-ferrer.eu', '$2y$10$PJk2g.I80UOMQX7tA4eRSe.oXb/JE6dmOF0lyW8W/6iixe.idtf0G', NULL, '0486587874', 'Bloc 2 - poursuite d\'études', 5, 'Techniques Infographiques (web)', '2025-02-12 14:00:58', '2025-02-19 06:28:28', 'tutor', 'published'),
(33, 'Laurenda', 'Tchouba', 'Cloe', 'laurenda.tchoubatchanha.240695@stu.he-ferrer.eu', '$2y$10$wXVSYNX9jbp5/Rd4PgS8ku85APqMBjc6SISTmRKYPXfw.pHSqgDo.', NULL, '0465998503', 'Bloc 1', 5, 'Électronique ', '2025-02-14 05:29:14', '2025-02-19 06:28:39', 'tutee', 'published'),
(36, 'Anthony', 'Nizigiyimana', 'Anthony44', 'tony.nizi.2002@icloud.com', '$2y$10$R1sgccUGWTQm5Mqv/w3TpewnQTSnuqBHk0nNPN.aodYUMdAyw552C', NULL, '0470112078', 'Bloc 1', 5, 'Technique Graphique', '2025-02-18 20:32:27', '2025-02-19 06:28:38', 'tutee', 'published'),
(37, 'Soraya', 'Ibardane', 'Soraya', 'soraya.ibardane.21408@stu.he-ferrer.eu', '$2y$10$LhS92LOjyKxzSr4mapfqY.DeqCkwMvv3acjKTbnT52A.zwVeiwHeG', NULL, '0484644197', 'Bloc 2 - année diplômante', 5, 'Technique', '2025-02-19 11:12:16', '2025-02-27 08:20:20', 'tutor', 'published'),
(38, 'Isabelle', 'Estercq', 'IsabelleE', 'isabelle.estercq@he-ferrer.eu', '$2y$10$rXSNDqu7SHCiroO2HTgPG.p5iGwsPqS2f3b2WQxfGKYI.K3rPm4ri', '67c0200f21176.jpg', '0498659987', 'Bloc 1', 5, 'Techniques d\'édition', '2025-02-27 08:19:27', '2025-02-27 08:20:55', 'tutor', 'rejected'),
(39, 'Isabelle', 'Estercq', 'IsabelleB', 'isabelleestercq@gmail.com', '$2y$10$daOJqGrk8kA1l7S5IisvDOnkz/JjCM9Hnwn3IE.6nefdysBvAKES.', '67c022d9bedfe.jpg', '0496789789', 'Bloc 1', 5, 'Edition', '2025-02-27 08:31:21', '2025-02-27 08:31:47', 'tutee', 'published'),
(41, 'Viriginie', 'Jossart', 'V.JO', 'tofsteen@hotmail.com', '$2y$10$0RyCx3FtEONpkid0LZfo3.WH0BMPhE7qjS43zCPRd1y4BwL1TJOky', '67c0267658d08.jpg', '0497898989', 'Bloc 2 - poursuite d\'études', 5, 'Techniques de l\'édition', '2025-02-27 08:46:46', '2025-02-27 08:47:37', 'tutor', 'published'),
(42, 'Bégonia', 'Paz', 'Bego', 'begonia.paz@he-ferrer.eu', '$2y$10$5RPwJQ/OGPbNsSF3PuHqHO9ufuWRvjoLX2ejoqH4.aR35y.gIjeZO', NULL, '0485520242', 'Master', 4, 'Peda', '2025-03-18 13:14:11', '2025-03-18 13:41:29', 'tutor', 'pending'),
(43, 'Cherine', 'Amrane', 'Cheram', 'cherine.amrane@he-ferrer.eu', '$2y$10$vsgf2JBTzSuN59v3vnyZdOMQodpfRkL4rV8XJjCyZPrDccFgc.RCq', '67da80a49eb54.jpg', '0494865584', 'Bloc 1', 2, 'comptabilité', '2025-03-19 08:30:28', '2025-03-19 08:30:28', 'tutor', 'pending'),
(44, 'Oliviu', 'Jalba', '2Val9', 'Oliviu.jalba.241119@stu.he-ferrer.eu', '$2y$10$PEANWYbNc9JkQhVFzAxocuDh0uLOs3rM.VluxV3LPVCS3u.lYGaV2', '67e191d80863c.jpg', '0485325457', 'Bloc 1', 5, 'infographie', '2025-03-24 17:09:44', '2025-03-25 14:01:16', 'tutee', 'published'),
(45, 'tutor', 'tutor', 'tutor', 'tutor@tutor.com', '$2y$10$oEE86uF5oLaJTcp.q2H3Z.s/6XGoQiF1blDoz5tmzwVYTGnY5wBHO', NULL, '4786982544', 'Bloc 2 - poursuite d\'études', 5, 'gsegse', '2025-03-26 11:46:19', '2025-03-26 11:46:19', 'tutor', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_availability` (`user_id`,`day_of_week`,`start_time`,`end_time`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tutees`
--
ALTER TABLE `tutees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `tutoring_relationships`
--
ALTER TABLE `tutoring_relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_relationship` (`tutor_id`,`tutee_id`,`subject_id`),
  ADD KEY `tutee_id` (`tutee_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `tutoring_relationships_archive`
--
ALTER TABLE `tutoring_relationships_archive`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `tutee_id` (`tutee_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `tutors`
--
ALTER TABLE `tutors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `tutor_subjects`
--
ALTER TABLE `tutor_subjects`
  ADD PRIMARY KEY (`tutor_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `tutees`
--
ALTER TABLE `tutees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tutoring_relationships`
--
ALTER TABLE `tutoring_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tutors`
--
ALTER TABLE `tutors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `admins_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `availability_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutees`
--
ALTER TABLE `tutees`
  ADD CONSTRAINT `tutees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutoring_relationships`
--
ALTER TABLE `tutoring_relationships`
  ADD CONSTRAINT `tutoring_relationships_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutoring_relationships_ibfk_2` FOREIGN KEY (`tutee_id`) REFERENCES `tutees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutoring_relationships_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `tutoring_relationships_archive`
--
ALTER TABLE `tutoring_relationships_archive`
  ADD CONSTRAINT `tutoring_relationships_archive_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`),
  ADD CONSTRAINT `tutoring_relationships_archive_ibfk_2` FOREIGN KEY (`tutee_id`) REFERENCES `tutees` (`id`),
  ADD CONSTRAINT `tutoring_relationships_archive_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `tutors`
--
ALTER TABLE `tutors`
  ADD CONSTRAINT `tutors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutor_subjects`
--
ALTER TABLE `tutor_subjects`
  ADD CONSTRAINT `tutor_subjects_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutor_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);
COMMIT;
