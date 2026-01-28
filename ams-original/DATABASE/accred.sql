-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 03:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `accred`
--

-- --------------------------------------------------------

--
-- Table structure for table `accreditation_levels`
--

CREATE TABLE `accreditation_levels` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accreditation_levels`
--

INSERT INTO `accreditation_levels` (`id`, `name`, `status`, `sort_order`) VALUES
(1, 'Candidate', 'active', 0),
(2, 'Level I', 'active', 1),
(3, 'Level II', 'active', 2),
(4, 'Level III', 'active', 3),
(5, 'Level IV', 'active', 4),
(6, 'Re-Accredited', 'active', 5);

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Draft',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `owner_user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_ext` varchar(12) NOT NULL,
  `mime_type` varchar(120) NOT NULL DEFAULT '',
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `owner_user_id`, `title`, `comment`, `original_name`, `stored_name`, `file_ext`, `mime_type`, `file_size`, `created_at`) VALUES
(9, 11, 'Level of accreditation status.pdf', '', 'Level of accreditation status.pdf', '20251016082945_757b7686.pdf', 'pdf', 'application/pdf', 4021759, '2025-10-16 06:29:45'),
(14, 11, '1 Completion requirements.docx', '', 'Preliminary Activity for Week 1 Completion requirements.docx', '20251021174309_8becc60f.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 18177, '2025-10-21 15:43:09'),
(15, 11, '548463215__nsdadsagdszf dswhgfdgfd gfsdgz.jpg', '', '548463215_1003312728523648_2694078431448917550_n.jpg', '20251021174322_a45969a1.jpg', 'jpg', 'image/jpeg', 105246, '2025-10-21 15:43:22'),
(16, 11, 'MODULE 2. NARRATIVE.pdf', '', 'MODULE 2. NARRATIVE.pdf', '20251023061917_3a825dc4.pdf', 'pdf', 'application/pdf', 797810, '2025-10-23 04:19:17'),
(17, 11, 'Screenshot 2025-09-15 231959.png', '', 'Screenshot 2025-09-15 231959.png', '20251023061925_b191b04d.png', 'png', 'image/png', 89210, '2025-10-23 04:19:25'),
(18, 11, 'EXAMPLE MANUSCRIPT.docx', '', 'EXAMPLE MANUSCRIPT.docx', '20251023061939_de2029b8.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 11203779, '2025-10-23 04:19:39'),
(19, 11, 'ITSP PROJECT.docx', '', 'ITSP PROJECT.docx', '20251023061943_b92928a3.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 984867, '2025-10-23 04:19:43'),
(20, 11, 'EXAMPLE MANUSCRIPT.docx', '', 'EXAMPLE MANUSCRIPT.docx', '20251023062019_040f2684.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 11203779, '2025-10-23 04:20:19'),
(21, 11, 'MODULE 3. NARRATIVE.docx', '', 'MODULE 3. NARRATIVE.docx', '20251023062049_4dc4b82a.docx', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 206569, '2025-10-23 04:20:49'),
(22, 11, 'Screenshot 2025-09-23 213959.png', '', 'Screenshot 2025-09-23 213959.png', '20251023062344_8f8c1c29.png', 'png', 'image/png', 195104, '2025-10-23 04:23:44'),
(23, 11, 'MODULE 3 & 4. .pdf', '', 'MODULE 3 & 4. .pdf', '20251023064856_a54166b0.pdf', 'pdf', 'application/pdf', 304198, '2025-10-23 04:48:56'),
(24, 11, 'MODULE 6..pdf', '', 'MODULE 6..pdf', '20251023064900_3026c080.pdf', 'pdf', 'application/pdf', 697252, '2025-10-23 04:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `document_shares`
--

CREATE TABLE `document_shares` (
  `id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `type`, `location`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'd', 'd', 'd', 'd', 11, '2025-10-21 04:40:13', '2025-10-21 04:40:13'),
(2, 'd', 'd', 'd', 'd', 11, '2025-10-21 04:40:28', '2025-10-21 04:40:28'),
(3, 'd', 'd', 'd', 'd', 11, '2025-10-21 04:40:33', '2025-10-21 04:40:33'),
(4, 'd', 'd', 'd', 'd', 11, '2025-10-21 04:40:37', '2025-10-21 04:40:37'),
(5, 'f', 'f', 'f', 'f', 11, '2025-10-21 05:01:37', '2025-10-21 05:01:37'),
(6, 'F', 'F', 'F', 'F', 11, '2025-10-21 16:38:24', '2025-10-21 16:38:24');

-- --------------------------------------------------------

--
-- Table structure for table `indicator_document_links`
--

CREATE TABLE `indicator_document_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `indicator_id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `indicator_document_links`
--

INSERT INTO `indicator_document_links` (`id`, `indicator_id`, `document_id`, `uploaded_by`, `created_at`) VALUES
(1, 1, 8, 11, '2025-10-16 06:06:25'),
(2, 17, 9, 11, '2025-10-16 06:29:45'),
(3, 1, 10, 11, '2025-10-17 13:45:08'),
(4, 1, 11, 11, '2025-10-17 13:45:25'),
(5, 22, 12, 11, '2025-10-17 16:04:08'),
(7, 24, 14, 11, '2025-10-21 15:43:09'),
(8, 25, 15, 11, '2025-10-21 15:43:22'),
(9, 29, 16, 11, '2025-10-23 04:19:17'),
(10, 30, 17, 11, '2025-10-23 04:19:25'),
(11, 27, 18, 11, '2025-10-23 04:19:39'),
(12, 28, 19, 11, '2025-10-23 04:19:43'),
(13, 26, 20, 11, '2025-10-23 04:20:19'),
(14, 32, 21, 11, '2025-10-23 04:20:49'),
(15, 26, 22, 11, '2025-10-23 04:23:44'),
(16, 33, 23, 11, '2025-10-23 04:48:56'),
(17, 34, 24, 11, '2025-10-23 04:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `indicator_labels`
--

CREATE TABLE `indicator_labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `parameter_label_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `title` text NOT NULL,
  `evidence` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `indicator_labels`
--

INSERT INTO `indicator_labels` (`id`, `parameter_label_id`, `code`, `title`, `evidence`, `sort_order`, `created_at`) VALUES
(1, 7, NULL, 'S1: the institution', NULL, 1, '2025-10-13 03:57:07'),
(17, 7, 'S2', 'The vision', NULL, 2, '2025-10-14 10:04:11'),
(22, 7, 'S3', 'sample', NULL, 3, '2025-10-17 16:03:07'),
(23, 21, 'S1', 'Indicator', NULL, 1, '2025-10-21 15:20:29'),
(24, 22, 'S1', 'The vision', NULL, 1, '2025-10-21 15:38:01'),
(25, 22, 'S2', 'the institution', NULL, 2, '2025-10-21 15:38:46'),
(26, 25, NULL, 'd', NULL, 1, '2025-10-23 04:18:09'),
(27, 26, NULL, 's', NULL, 1, '2025-10-23 04:18:23'),
(28, 26, NULL, 's', NULL, 2, '2025-10-23 04:18:25'),
(29, 27, NULL, 's', NULL, 1, '2025-10-23 04:18:37'),
(30, 27, NULL, 's', NULL, 2, '2025-10-23 04:18:40'),
(31, 27, NULL, 's', NULL, 3, '2025-10-23 04:18:43'),
(32, 28, NULL, 's', NULL, 1, '2025-10-23 04:18:59'),
(33, 29, NULL, 's', NULL, 1, '2025-10-23 04:48:31'),
(34, 29, NULL, 's', NULL, 2, '2025-10-23 04:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `instruments`
--

CREATE TABLE `instruments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instruments`
--

INSERT INTO `instruments` (`id`, `name`, `description`, `created_by`, `created_at`) VALUES
(6, 'Sample research instrument 2', '', 11, '2025-09-28 10:38:55'),
(7, 'Sample research instrument', '', 11, '2025-09-30 03:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `instrument_programs`
--

CREATE TABLE `instrument_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `instrument_id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` int(10) UNSIGNED NOT NULL,
  `instrument_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `weight` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `program_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `instrument_id`, `name`, `description`, `weight`, `created_at`, `program_id`) VALUES
(7, 6, 'Level 2', '', 0, '2025-09-28 11:12:10', NULL),
(10, 6, 'Level 1', '', 0, '2025-09-28 14:48:10', NULL),
(11, 7, 'level 2', '', 0, '2025-09-30 03:20:59', NULL),
(12, 7, 'Level 1', '', 0, '2025-09-30 03:21:09', NULL),
(13, 7, 'Candidate', '', 0, '2025-10-15 14:08:36', NULL),
(14, 7, 'level 3', '', 0, '2025-10-15 14:08:46', NULL),
(15, 7, 'Level 3', '', 0, '2025-10-15 14:08:53', NULL),
(16, 7, 'Sample', '', 0, '2025-10-23 04:48:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `level_programs`
--

CREATE TABLE `level_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `level_id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `level_programs`
--

INSERT INTO `level_programs` (`id`, `level_id`, `program_id`, `created_at`) VALUES
(3, 11, 1, '2025-10-15 10:36:25'),
(4, 13, 2, '2025-10-15 14:09:05'),
(5, 14, 10, '2025-10-15 14:09:10'),
(6, 15, 11, '2025-10-15 14:09:13'),
(8, 12, 9, '2025-10-16 06:28:44'),
(9, 16, 17, '2025-10-23 04:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `parameters`
--

CREATE TABLE `parameters` (
  `id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parameters`
--

INSERT INTO `parameters` (`id`, `section_id`, `name`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(8, 7, 'Parameter A', '', 1, '2025-10-13 03:01:10', '2025-10-14 17:26:02'),
(12, 7, 'Parameter B', '', 5, '2025-10-13 13:02:11', '2025-10-15 04:37:20'),
(18, 23, 'ds', NULL, 1, '2025-10-16 06:27:23', '2025-10-16 06:27:23'),
(19, 24, 'Parameter', NULL, 1, '2025-10-21 15:20:02', '2025-10-21 15:20:02'),
(20, 24, 'Parameter B', NULL, 2, '2025-10-22 12:08:44', '2025-10-22 12:08:44'),
(21, 24, 'Parameter C', NULL, 3, '2025-10-22 12:08:52', '2025-10-22 12:08:52'),
(22, 24, 'Parameter D', NULL, 4, '2025-10-22 12:09:00', '2025-10-22 12:09:00'),
(23, 7, 'Parameter C', NULL, 6, '2025-10-22 12:09:11', '2025-10-22 12:09:11'),
(24, 26, 'd', NULL, 1, '2025-10-23 04:18:05', '2025-10-23 04:18:05'),
(25, 27, 's', NULL, 1, '2025-10-23 04:18:33', '2025-10-23 04:18:33'),
(26, 28, 's', NULL, 1, '2025-10-23 04:18:55', '2025-10-23 04:18:55'),
(27, 29, 's', NULL, 1, '2025-10-23 04:48:26', '2025-10-23 04:48:26');

-- --------------------------------------------------------

--
-- Table structure for table `parameter_labels`
--

CREATE TABLE `parameter_labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `parameter_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parameter_labels`
--

INSERT INTO `parameter_labels` (`id`, `parameter_id`, `name`, `description`, `sort_order`, `created_at`) VALUES
(7, 8, 'System', NULL, 1, '2025-10-13 03:38:51'),
(8, 8, 'Implementation', NULL, 2, '2025-10-13 03:39:11'),
(9, 8, 'Outcomes', NULL, 3, '2025-10-13 03:39:25'),
(21, 19, 'System', NULL, 1, '2025-10-21 15:20:13'),
(22, 12, 'System', NULL, 1, '2025-10-21 15:37:18'),
(23, 12, 'Implementation', NULL, 2, '2025-10-21 15:37:31'),
(24, 12, 'Outcomes', NULL, 3, '2025-10-21 15:37:45'),
(25, 24, 'd', NULL, 1, '2025-10-23 04:18:07'),
(26, 18, 's', NULL, 1, '2025-10-23 04:18:19'),
(27, 25, 's', NULL, 1, '2025-10-23 04:18:36'),
(28, 26, 's', NULL, 1, '2025-10-23 04:18:57'),
(29, 27, 's', NULL, 1, '2025-10-23 04:48:29');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `coordinator_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `code`, `name`, `created_at`, `description`, `coordinator_user_id`) VALUES
(1, 'BSIT', 'Bachelor of Science in Information Technology', '2025-09-06 05:43:15', NULL, NULL),
(2, 'BSCRIM', 'Bachelor of Science in Criminology', '2025-09-06 05:43:15', NULL, NULL),
(9, 'BSBA', 'Bachelor of Science in Business Administration', '2025-09-24 04:36:45', '', NULL),
(10, 'BSED', 'Bachelor of science in Education', '2025-09-24 08:01:33', '', NULL),
(11, 'BSHM', 'Bachelor of Science in Hospitality Management', '2025-09-24 08:02:29', '', NULL),
(17, 'add example', 'sample', '2025-10-23 04:46:01', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `program_accreditation`
--

CREATE TABLE `program_accreditation` (
  `id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `level` varchar(32) NOT NULL DEFAULT 'Candidate',
  `phase` varchar(32) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_accreditation`
--

INSERT INTO `program_accreditation` (`id`, `program_id`, `level`, `phase`, `status`, `updated_at`) VALUES
(1, 9, 'Level I', '', 'active', '2025-09-29 15:40:31'),
(2, 10, 'level', 'Phase 1', 'active', '2025-09-29 15:09:41'),
(4, 2, 'Level II', '', 'active', '2025-09-29 16:40:12'),
(5, 1, 'Level II', '', 'active', '2025-09-29 16:40:20'),
(6, 11, 'Candidate', '', 'active', '2025-09-29 16:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `program_coordinators`
--

CREATE TABLE `program_coordinators` (
  `id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_coordinators`
--

INSERT INTO `program_coordinators` (`id`, `program_id`, `user_id`, `assigned_at`) VALUES
(2, 9, 11, '2025-10-23 12:15:29');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `level_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `program_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `level_id`, `name`, `description`, `created_at`, `program_id`) VALUES
(4, 7, 'Sample 1', '', '2025-09-30 03:22:23', NULL),
(7, 12, 'Section 1', '', '2025-10-13 03:01:02', NULL),
(23, 14, 's', '', '2025-10-16 06:27:18', NULL),
(24, 12, 'Section 2', '', '2025-10-17 13:09:05', NULL),
(26, 15, 'd', '', '2025-10-23 04:18:01', NULL),
(27, 13, 's', '', '2025-10-23 04:18:29', NULL),
(28, 11, 's', '', '2025-10-23 04:18:50', NULL),
(29, 16, 's', '', '2025-10-23 04:48:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `section_items`
--

CREATE TABLE `section_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `parameter_id` int(10) UNSIGNED NOT NULL,
  `group_name` enum('system','implementation','outcomes') NOT NULL DEFAULT 'system',
  `code` varchar(20) DEFAULT NULL,
  `text` text NOT NULL,
  `evidence_hint` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_items`
--

INSERT INTO `section_items` (`id`, `parameter_id`, `group_name`, `code`, `text`, `evidence_hint`, `sort_order`, `created_at`) VALUES
(1, 1, 'system', 'S.1', 'The institution has a system of determining its Vision and Mission.', 'Evidence to attach: evidence', 0, '2025-10-12 14:21:51'),
(2, 1, 'system', 'S.2', 'The Vision clearly reflects what the institution hopes to become in the future.', 'Evidence to attach: sample', 1, '2025-10-12 14:21:51'),
(3, 1, 'system', 'S.3', 'The Mission clearly reflects the institution\'s legal and other statutory mandates.', 'Evidence to attach: asdfasd', 2, '2025-10-12 14:21:51'),
(4, 1, 'system', 'S.4', 'The Goals of the College/Academic Unit are consistent with the Mission of the Institution.', 'Evidence to attach: dasdfasdf', 3, '2025-10-12 14:21:51'),
(5, 1, 'system', 'S.5', 'The Objectives of the program have the expected outcomes in terms of competencies (skills and knowledge), values and other attributes of the graduates which include the development of:', 'Evidence to attach: asdfasdfasdf', 4, '2025-10-12 14:21:51'),
(6, 2, 'system', 'S.1', 'The institution has a system of determining its Vision and Mission.', 'Evidence to attach: evidence', 0, '2025-10-12 14:22:00'),
(7, 2, 'system', 'S.2', 'The Vision clearly reflects what the institution hopes to become in the future.', 'Evidence to attach: sample', 1, '2025-10-12 14:22:00'),
(8, 2, 'system', 'S.3', 'The Mission clearly reflects the institution\'s legal and other statutory mandates.', 'Evidence to attach: asdfasd', 2, '2025-10-12 14:22:00'),
(9, 2, 'system', 'S.4', 'The Goals of the College/Academic Unit are consistent with the Mission of the Institution.', 'Evidence to attach: dasdfasdf', 3, '2025-10-12 14:22:00'),
(10, 2, 'system', 'S.5', 'The Objectives of the program have the expected outcomes in terms of competencies (skills and knowledge), values and other attributes of the graduates which include the development of:', 'Evidence to attach: asdfasdfasdf', 4, '2025-10-12 14:22:00');

-- --------------------------------------------------------

--
-- Table structure for table `section_parameters`
--

CREATE TABLE `section_parameters` (
  `id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(16) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_parameters`
--

INSERT INTO `section_parameters` (`id`, `section_id`, `code`, `label`, `name`, `sort_order`, `created_at`) VALUES
(1, 3, NULL, '', 'Parameter A  Statement of Vision, Mission, Goals and Objectives', 0, '2025-10-12 14:21:51'),
(2, 2, NULL, '', 'Parameter A  Statement of Vision, Mission, Goals and Objectives', 0, '2025-10-12 14:22:00');

-- --------------------------------------------------------

--
-- Table structure for table `section_programs`
--

CREATE TABLE `section_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `section_id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Draft',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','dean','program_coordinator','faculty','staff','external_accreditor') NOT NULL DEFAULT 'faculty',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role_backup` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `username`, `password_hash`, `role`, `created_at`, `role_backup`) VALUES
(3, 'Echo', 'Deguz', 'echo@gmail.com', 'echo', '$2y$10$wGbQ4jnzr3S1vQnL8a1vjuyAetjvUHp2KkNXRtnhVjgg4pljan50K', 'program_coordinator', '2025-09-03 01:59:01', 'student'),
(5, 'Rodante', 'Marcoleta', 'Rodante@gmail.com', 'Rodante', '$2y$10$GEMf0Y9w1eh6XCpIOYmm/OFYHmjSWl1I0huevsYXCpMtN3w1E8wZe', 'staff', '2025-09-06 05:49:58', 'staff'),
(8, 'Mike', 'Tyzon', 'Mike@gmail.com', 'Mike', '$2y$10$AWPq7lu.lHbBoUDXfOs6cu.UahkkzfiKOatB/K4Ff9IEQaP395eKS', 'faculty', '2025-09-06 06:41:18', 'faculty'),
(9, 'Mark', 'Abrasado', 'Abrasado@gmail.com', 'Abrasado', '$2y$10$HXmpKNKcWeaJu7iZ97x/PeeUGU6O/Hyfs2JtnXt7Y5W.fbsIbxe9S', 'faculty', '2025-09-06 06:43:43', 'faculty'),
(10, 'Arron', 'Dianito', 'Arron@gmail.com', 'Arron', '$2y$10$D1feneoZHfI4227WxUHEQegAcIvKFkCtqMzHrROmamEN/YyizvK/e', 'dean', '2025-09-24 04:39:26', NULL),
(11, 'Jerks', 'Cruz', 'jericho41102@gmail.com', 'jericho41102', '$2y$10$U3ZwYybiwBOVYpU/7dZeZ.PFbR3I5vuHyGoXlxZS0fOtMH8XY955K', 'program_coordinator', '2025-09-24 13:43:12', NULL),
(12, 'Angel', 'Lopez', 'Angel@gmail.com', 'Angel', '$2y$10$npuPq4a3pdZve7gb47jaMu1035SD8FCTTNXq3nzJePalYzbF2b.aa', 'program_coordinator', '2025-10-16 15:42:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` int(10) UNSIGNED NOT NULL,
  `team` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `visit_date` date NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('Planned','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  `type` enum('initial','revisit','followup','orientation') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visits`
--

INSERT INTO `visits` (`id`, `team`, `start_date`, `end_date`, `visit_date`, `purpose`, `status`, `type`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'CHED', NULL, NULL, '2025-09-04', 'Visit', 'Ongoing', NULL, '', NULL, '2025-09-23 04:16:37', '2025-09-23 04:16:37'),
(3, 'PAASCU', NULL, NULL, '2025-11-01', 'Visit', 'Ongoing', NULL, '', 3, '2025-09-23 06:11:17', '2025-09-23 06:11:17'),
(4, 'CHED', NULL, NULL, '2025-09-19', 'Visit', 'Completed', NULL, '', 3, '2025-09-23 06:11:34', '2025-09-23 06:11:34'),
(5, 'CHED', NULL, NULL, '2025-09-05', 'Visit', 'Planned', NULL, '', 3, '2025-09-23 09:28:18', '2025-09-23 09:32:57'),
(6, 'PAASCU', NULL, NULL, '2025-08-08', 'Visit', 'Cancelled', NULL, '', 3, '2025-09-23 09:28:38', '2025-09-23 09:28:38'),
(7, 'CHED', NULL, NULL, '2025-10-03', 'Visit', 'Completed', NULL, '', 3, '2025-09-24 04:39:56', '2025-09-24 04:39:56'),
(8, 'ACSCU-ACI', NULL, NULL, '2025-10-23', 'Visit', 'Ongoing', NULL, '', 11, '2025-10-22 13:05:20', '2025-10-22 13:05:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accreditation_levels`
--
ALTER TABLE `accreditation_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_name` (`name`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_program` (`program_id`),
  ADD KEY `idx_due` (`due_date`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_docs_owner` (`owner_user_id`);

--
-- Indexes for table `document_shares`
--
ALTER TABLE `document_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_share` (`document_id`,`user_id`),
  ADD KEY `idx_share_doc` (`document_id`),
  ADD KEY `idx_share_user` (`user_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_facilities_name` (`name`),
  ADD KEY `idx_facilities_type` (`type`),
  ADD KEY `idx_facilities_location` (`location`);

--
-- Indexes for table `indicator_document_links`
--
ALTER TABLE `indicator_document_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_indicator` (`indicator_id`),
  ADD KEY `idx_document` (`document_id`);

--
-- Indexes for table `indicator_labels`
--
ALTER TABLE `indicator_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parameter_label_id` (`parameter_label_id`),
  ADD KEY `sort_order` (`sort_order`);

--
-- Indexes for table `instruments`
--
ALTER TABLE `instruments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instrument_programs`
--
ALTER TABLE `instrument_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inst_prog` (`instrument_id`,`program_id`),
  ADD KEY `idx_prog` (`program_id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_levels_instrument` (`instrument_id`);

--
-- Indexes for table `level_programs`
--
ALTER TABLE `level_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_level_prog` (`level_id`,`program_id`),
  ADD KEY `idx_prog` (`program_id`);

--
-- Indexes for table `parameters`
--
ALTER TABLE `parameters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parameters_section` (`section_id`);

--
-- Indexes for table `parameter_labels`
--
ALTER TABLE `parameter_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_param_labels_param` (`parameter_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prog_code` (`code`),
  ADD KEY `idx_prog_name` (`name`);

--
-- Indexes for table `program_accreditation`
--
ALTER TABLE `program_accreditation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_program` (`program_id`);

--
-- Indexes for table `program_coordinators`
--
ALTER TABLE `program_coordinators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_prog_user` (`program_id`,`user_id`),
  ADD KEY `idx_prog` (`program_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sections_level` (`level_id`);

--
-- Indexes for table `section_items`
--
ALTER TABLE `section_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `section_parameters`
--
ALTER TABLE `section_parameters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `section_programs`
--
ALTER TABLE `section_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section_prog` (`section_id`,`program_id`),
  ADD KEY `idx_prog` (`program_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_program` (`program_id`),
  ADD KEY `idx_due` (`due_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD KEY `idx_users_name` (`last_name`,`first_name`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit_date` (`visit_date`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_visits_status_date` (`status`,`visit_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accreditation_levels`
--
ALTER TABLE `accreditation_levels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `document_shares`
--
ALTER TABLE `document_shares`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `indicator_document_links`
--
ALTER TABLE `indicator_document_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `indicator_labels`
--
ALTER TABLE `indicator_labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `instruments`
--
ALTER TABLE `instruments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `instrument_programs`
--
ALTER TABLE `instrument_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `level_programs`
--
ALTER TABLE `level_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `parameters`
--
ALTER TABLE `parameters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `parameter_labels`
--
ALTER TABLE `parameter_labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `program_accreditation`
--
ALTER TABLE `program_accreditation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `program_coordinators`
--
ALTER TABLE `program_coordinators`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `section_items`
--
ALTER TABLE `section_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `section_parameters`
--
ALTER TABLE `section_parameters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `section_programs`
--
ALTER TABLE `section_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_docs_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_shares`
--
ALTER TABLE `document_shares`
  ADD CONSTRAINT `fk_share_doc` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_share_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `indicator_labels`
--
ALTER TABLE `indicator_labels`
  ADD CONSTRAINT `fk_ind_pl` FOREIGN KEY (`parameter_label_id`) REFERENCES `parameter_labels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `instrument_programs`
--
ALTER TABLE `instrument_programs`
  ADD CONSTRAINT `fk_ip_inst` FOREIGN KEY (`instrument_id`) REFERENCES `instruments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ip_prog` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `levels`
--
ALTER TABLE `levels`
  ADD CONSTRAINT `fk_levels_instrument` FOREIGN KEY (`instrument_id`) REFERENCES `instruments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `level_programs`
--
ALTER TABLE `level_programs`
  ADD CONSTRAINT `fk_lp_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lp_prog` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parameters`
--
ALTER TABLE `parameters`
  ADD CONSTRAINT `fk_parameters_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parameter_labels`
--
ALTER TABLE `parameter_labels`
  ADD CONSTRAINT `fk_param_labels_param` FOREIGN KEY (`parameter_id`) REFERENCES `parameters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_accreditation`
--
ALTER TABLE `program_accreditation`
  ADD CONSTRAINT `fk_prog_acc_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `section_programs`
--
ALTER TABLE `section_programs`
  ADD CONSTRAINT `fk_sp_prog` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sp_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
