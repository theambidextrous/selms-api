SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS app_messages (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  initiator varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  message text COLLATE utf8mb4_unicode_ci NOT NULL,
  subject varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  recipient_phone varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  approved tinyint(1) NOT NULL DEFAULT '0',
  send tinyint(1) NOT NULL DEFAULT '0',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assessmentgroups (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY assessmentgroups_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO assessmentgroups (id, name, created_at, updated_at) VALUES
(1, 'Entry test', '2025-07-01 07:16:02', '2025-07-01 07:16:02'),
(2, 'CBC CAT 1', '2025-07-01 07:17:34', '2025-07-01 07:17:34'),
(3, 'CBC CAT 2', '2025-07-01 07:17:39', '2025-07-01 07:17:39'),
(4, 'Cbc CAT 3', '2025-07-01 07:17:44', '2025-07-01 07:18:06');

CREATE TABLE IF NOT EXISTS attendances (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  current_term varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  lesson varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  student varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  is_in tinyint(1) NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollments (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  year varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  subject varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  student varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  status varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enrolled',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY enrollments_subject_student_unique (subject,student)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO enrollments (id, year, subject, student, status, created_at, updated_at) VALUES
(1, '2025', '1', '1', 'enrolled', '2025-06-29 14:08:14', '2025-06-29 14:08:14'),
(2, '2025', '2', '1', 'enrolled', '2025-06-29 14:08:14', '2025-06-29 14:08:14'),
(3, '2025', '3', '1', 'enrolled', '2025-06-29 14:08:14', '2025-06-29 14:08:14'),
(4, '2025', '4', '1', 'enrolled', '2025-06-29 14:08:14', '2025-06-29 14:08:14'),
(5, '2025', '1', '2', 'enrolled', '2025-06-29 14:08:19', '2025-06-29 14:08:19'),
(6, '2025', '2', '2', 'enrolled', '2025-06-29 14:08:19', '2025-06-29 14:08:19'),
(7, '2025', '3', '2', 'enrolled', '2025-06-29 14:08:19', '2025-06-29 14:08:19'),
(8, '2025', '4', '2', 'enrolled', '2025-06-29 14:08:19', '2025-06-29 14:08:19'),
(9, '2025', '1', '3', 'enrolled', '2025-06-29 14:09:39', '2025-06-29 14:09:39'),
(10, '2025', '2', '3', 'enrolled', '2025-06-29 14:09:39', '2025-06-29 14:09:39'),
(11, '2025', '3', '3', 'enrolled', '2025-06-29 14:09:39', '2025-06-29 14:09:39'),
(12, '2025', '4', '3', 'enrolled', '2025-06-29 14:09:39', '2025-06-29 14:09:39'),
(13, '2025', '1', '4', 'enrolled', '2025-06-29 14:10:52', '2025-06-29 14:10:52'),
(14, '2025', '2', '4', 'enrolled', '2025-06-29 14:10:52', '2025-06-29 14:10:52'),
(15, '2025', '3', '4', 'enrolled', '2025-06-29 14:10:52', '2025-06-29 14:10:52'),
(16, '2025', '4', '4', 'enrolled', '2025-06-29 14:10:52', '2025-06-29 14:10:52'),
(17, '2025', '1', '5', 'enrolled', '2025-06-29 17:39:56', '2025-06-29 17:39:56'),
(18, '2025', '2', '5', 'enrolled', '2025-06-29 17:39:56', '2025-06-29 17:39:56'),
(19, '2025', '3', '5', 'enrolled', '2025-06-29 17:39:56', '2025-06-29 17:39:56'),
(20, '2025', '4', '5', 'enrolled', '2025-06-29 17:39:56', '2025-06-29 17:39:56'),
(21, '2025', '1', '6', 'enrolled', '2025-06-30 03:27:04', '2025-06-30 03:27:04'),
(22, '2025', '2', '6', 'enrolled', '2025-06-30 03:27:04', '2025-06-30 03:27:04'),
(23, '2025', '3', '6', 'enrolled', '2025-06-30 03:27:04', '2025-06-30 03:27:04'),
(25, '2025', '1', '7', 'enrolled', '2025-06-30 03:33:05', '2025-06-30 03:33:05'),
(28, '2025', '4', '7', 'enrolled', '2025-06-30 03:33:05', '2025-06-30 03:33:05'),
(33, '2025', '4', '6', 'enrolled', '2025-07-01 03:36:24', '2025-07-01 03:36:24'),
(34, '2025', '3', '7', 'enrolled', '2025-07-01 06:47:35', '2025-07-01 06:47:35'),
(35, '2025', '2', '7', 'enrolled', '2025-07-01 06:47:52', '2025-07-01 06:47:52');

CREATE TABLE IF NOT EXISTS expenses (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  term varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  narration varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  person varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  fee varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS failed_jobs (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  connection text COLLATE utf8mb4_unicode_ci NOT NULL,
  queue text COLLATE utf8mb4_unicode_ci NOT NULL,
  payload longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  exception longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  failed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fees (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  term varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  narration varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  student varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  fee varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  type varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tution',
  subject varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  cleared tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY fees_student_subject_type_unique (student,subject,type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO fees (id, term, narration, student, fee, created_at, updated_at, type, subject, cleared) VALUES
(32, '2', 'Tution fees for Introduction to arabic', '6', '1250.00', '2025-07-01 06:19:54', '2025-07-01 06:19:54', 'Tution', '1', 0),
(33, '2', 'Tution fees for Sounds and letters arabic', '6', '1050.00', '2025-07-01 06:19:54', '2025-07-01 06:19:54', 'Tution', '2', 0),
(34, '2', 'Tution fees for Arabic smilies and proverbs', '6', '1500.00', '2025-07-01 06:19:54', '2025-07-01 06:19:54', 'Tution', '3', 0),
(35, '2', 'Tution fees for Arabic sayings and proverbs', '6', '3000.00', '2025-07-01 06:19:54', '2025-07-01 06:19:54', 'Tution', '4', 0),
(36, '2', 'Tution fees for Introduction to arabic', '7', '1250.00', '2025-07-01 06:20:20', '2025-07-01 06:20:20', 'Tution', '1', 0),
(39, '2', 'Tution fees for Arabic sayings and proverbs', '7', '3000.00', '2025-07-01 06:20:20', '2025-07-01 06:20:20', 'Tution', '4', 0),
(40, '2', 'Tution fees for Introduction to arabic', '5', '1250.00', '2025-07-01 06:20:26', '2025-07-01 06:20:26', 'Tution', '1', 0),
(41, '2', 'Tution fees for Sounds and letters arabic', '5', '1050.00', '2025-07-01 06:20:26', '2025-07-01 06:20:26', 'Tution', '2', 0),
(42, '2', 'Tution fees for Arabic smilies and proverbs', '5', '1500.00', '2025-07-01 06:20:26', '2025-07-01 06:20:26', 'Tution', '3', 0),
(43, '2', 'Tution fees for Arabic sayings and proverbs', '5', '3000.00', '2025-07-01 06:20:26', '2025-07-01 06:20:26', 'Tution', '4', 0),
(44, '2', 'Tution fees for Introduction to arabic', '4', '1250.00', '2025-07-01 06:22:11', '2025-07-01 06:22:11', 'Tution', '1', 0),
(45, '2', 'Tution fees for Sounds and letters arabic', '4', '1050.00', '2025-07-01 06:22:11', '2025-07-01 06:22:11', 'Tution', '2', 0),
(46, '2', 'Tution fees for Arabic smilies and proverbs', '4', '1500.00', '2025-07-01 06:22:11', '2025-07-01 06:22:11', 'Tution', '3', 0),
(47, '2', 'Tution fees for Arabic sayings and proverbs', '4', '3000.00', '2025-07-01 06:22:11', '2025-07-01 06:22:11', 'Tution', '4', 0),
(48, '2', 'Tution fees for Introduction to arabic', '3', '1250.00', '2025-07-01 06:22:15', '2025-07-01 06:22:15', 'Tution', '1', 0),
(49, '2', 'Tution fees for Sounds and letters arabic', '3', '1050.00', '2025-07-01 06:22:15', '2025-07-01 06:22:15', 'Tution', '2', 0),
(50, '2', 'Tution fees for Arabic smilies and proverbs', '3', '1500.00', '2025-07-01 06:22:15', '2025-07-01 06:22:15', 'Tution', '3', 0),
(51, '2', 'Tution fees for Arabic sayings and proverbs', '3', '3000.00', '2025-07-01 06:22:15', '2025-07-01 06:22:15', 'Tution', '4', 0),
(52, '2', 'Tution fees for Introduction to arabic', '2', '1250.00', '2025-07-01 06:22:22', '2025-07-01 06:22:22', 'Tution', '1', 0),
(53, '2', 'Tution fees for Sounds and letters arabic', '2', '1050.00', '2025-07-01 06:22:22', '2025-07-01 06:22:22', 'Tution', '2', 0),
(54, '2', 'Tution fees for Arabic smilies and proverbs', '2', '1500.00', '2025-07-01 06:22:22', '2025-07-01 06:22:22', 'Tution', '3', 0),
(55, '2', 'Tution fees for Arabic sayings and proverbs', '2', '3000.00', '2025-07-01 06:22:22', '2025-07-01 06:22:22', 'Tution', '4', 0),
(57, '2', 'Tution fees for Arabic smilies and proverbs', '7', '1500.00', '2025-07-01 06:47:35', '2025-07-01 06:47:35', 'Tution', '3', 0),
(58, '2', 'Tution fees for Sounds and letters arabic', '7', '1050.00', '2025-07-01 06:47:52', '2025-07-01 06:47:52', 'Tution', '2', 0);

CREATE TABLE IF NOT EXISTS forms (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  description varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO forms (id, name, description, created_at, updated_at) VALUES
(1, 'GRADE_1', 'The lowest level available', '2025-06-29 06:45:25', '2025-06-29 06:45:25'),
(2, 'GRADE_2', 'The 2nd lowest level available', '2025-06-29 06:45:39', '2025-06-29 06:45:39'),
(3, 'GRADE_3', 'The 3rd lowest level available', '2025-06-29 06:45:51', '2025-06-29 06:45:51'),
(4, 'GRADE_4', 'The point where learners advance in arabic', '2025-06-29 06:45:58', '2025-06-30 08:19:57'),
(5, 'GRADE_5', 'The 5th lowest level available', '2025-06-29 06:46:06', '2025-06-29 06:46:06'),
(6, 'GRADE_6', 'The 6th lowest level available', '2025-06-29 06:46:13', '2025-06-29 06:46:13'),
(7, 'NEW_GRADE_1', 'some test level that learners can be added', '2025-06-30 08:20:31', '2025-06-30 08:20:31');

CREATE TABLE IF NOT EXISTS formstreams (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  form varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  name varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  class_teacher varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  label varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO formstreams (id, form, name, class_teacher, label, created_at, updated_at) VALUES
(1, '1', '1WEST', '2', 'West', '2025-06-29 07:05:54', '2025-06-29 07:05:54'),
(2, '1', '1EAST', '2', 'East', '2025-06-29 07:06:59', '2025-06-29 07:06:59'),
(3, '2', 'BLUE', '5', 'blue', '2025-06-30 08:48:29', '2025-06-30 08:48:29'),
(4, '5', '5WEST', '4', 'West', '2025-06-30 08:51:01', '2025-06-30 08:51:24');

CREATE TABLE IF NOT EXISTS librarybooks (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  number varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  catalogue varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  status varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'In',
  lent_to varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  lent_from varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  lent_until varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY librarybooks_number_unique (number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS librarycatalogues (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  title varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  author varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  publisher varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  available varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  lent varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  lost varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
  email varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  token varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  KEY password_resets_email_index (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS performances (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  student varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  subject varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  mark varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  grade varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  remark varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  term varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY performances_student_subject_group_term_unique (student,subject,`group`,term)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO performances (id, student, subject, `group`, mark, grade, remark, term, created_at, updated_at) VALUES
(1, '7', '2', '2', '55', 'AE', 'well done', '2', '2025-07-02 08:26:17', '2025-07-02 09:04:12'),
(2, '6', '2', '2', '70', 'AE', 'well done', '2', '2025-07-02 09:34:42', '2025-07-02 09:34:42'),
(3, '5', '2', '2', '87', 'ME', 'n/a', '2', '2025-07-02 09:35:01', '2025-07-02 09:36:17'),
(4, '4', '2', '2', '99', 'NA', 'dd', '2', '2025-07-02 09:35:37', '2025-07-02 14:43:24');

CREATE TABLE IF NOT EXISTS positions (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  person varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  title varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  signature varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scales (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  min_mark varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  grade varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  form varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  max_mark varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY scales_form_grade_unique (form,grade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO scales (id, min_mark, grade, form, created_at, updated_at, max_mark) VALUES
(1, '90', 'EE', '6', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(2, '75', 'ME', '6', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(3, '50', 'AE', '6', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(4, '1', 'BE', '6', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49'),
(5, '90', 'EE', '1', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(6, '75', 'ME', '1', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(7, '50', 'AE', '1', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(8, '1', 'BE', '1', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49'),
(9, '90', 'EE', '2', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(10, '75', 'ME', '2', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(11, '50', 'AE', '2', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(12, '1', 'BE', '2', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49'),
(13, '90', 'EE', '3', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(14, '75', 'ME', '3', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(15, '50', 'AE', '3', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(16, '1', 'BE', '3', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49'),
(17, '90', 'EE', '4', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(18, '75', 'ME', '4', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(19, '50', 'AE', '4', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(20, '1', 'BE', '4', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49'),
(21, '90', 'EE', '5', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(22, '75', 'ME', '5', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(23, '50', 'AE', '5', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(24, '1', 'BE', '5', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49'),
(25, '90', 'EE', '7', '2025-07-01 08:03:16', '2025-07-01 08:23:22', '100'),
(26, '75', 'ME', '7', '2025-07-01 08:24:08', '2025-07-01 08:24:08', '89'),
(27, '50', 'AE', '7', '2025-07-01 08:24:30', '2025-07-01 08:24:30', '74'),
(28, '1', 'BE', '7', '2025-07-01 08:24:49', '2025-07-01 08:24:49', '49');

CREATE TABLE IF NOT EXISTS setups (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  school varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  address varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  city varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  county varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  zip varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  email varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  phone varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  website varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  motto varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  logo varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  is_active tinyint(1) NOT NULL DEFAULT '1',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO setups (id, school, address, city, county, zip, email, phone, website, motto, logo, is_active, created_at, updated_at) VALUES
(1, 'Juma school', 'Naivasha Road', 'Nairobi', 'coco', '00200', 'test-com@gmail.com', '254705007999', 'www.juma-school.com', 'Here we learn', 'http://127.0.0.1:8000/pci/api/v1/downloads/get/rpt/file/4297b801-9a98-4595-a646-29199e21b0e0.png', 1, '2025-07-03 16:38:48', '2025-07-03 17:45:08');

CREATE TABLE IF NOT EXISTS sports (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  description varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  admission varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  date_of_admission varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  fname varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  lname varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  address varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  city varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  county varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  zip varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  parent varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  form varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  stream varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  current_term varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  expected_grad varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  gender varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  dob varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  birth_cert varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  nemis_no varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  huduma_no varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  is_active varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  pic varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  kcpe varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY students_admission_unique (admission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO students (id, admission, date_of_admission, fname, lname, address, city, county, zip, parent, form, stream, current_term, expected_grad, gender, dob, birth_cert, nemis_no, huduma_no, is_active, pic, created_at, updated_at, kcpe) VALUES
(1, '20250629001', '2025-06-29', 'Alice', 'Kiri', '123 valencia', 'Nairobi', 'Nairobi', '00400', '3', '1', '1', '2', '2026-01-31', 'Female', '2012-02-20', '0023455', '000023455', '00023455', '1', 'default.png', '2025-06-29 14:08:14', '2025-07-01 06:14:07', '234'),
(2, '20250629002', '2025-06-29', 'Alice', 'Kiri', '123 valencia', 'Nairobi', 'Nairobi', '00400', '3', '1', '1', '2', '2026-01-31', 'Female', '2012-02-20', '0023455', '000023455', '00023455', '1', 'default.png', '2025-06-29 14:08:19', '2025-07-01 06:14:07', '234'),
(3, '20250629003', '2025-06-29', 'Ben', 'Kan', '600700 valencia', 'Nairobi', 'Nairobi', '00400', '3', '1', '2', '2', '2026-01-31', 'Male', '2012-04-20', '0023450', '000023450', '00023450', '1', 'default.png', '2025-06-29 14:09:39', '2025-07-01 06:14:07', '300'),
(4, '20250629004', '2025-06-29', 'Katerina', 'Kellenopolos', '600700 Gitanga', 'Nairobi', 'Nairobi', '00400', '3', '1', '2', '2', '2027-01-31', 'Female', '2012-04-20', '1123450', '110023450', '11023450', '1', 'http://127.0.0.1:8000/pci/api/v1/downloads/get/rpt/file/default.png', '2025-06-29 14:10:52', '2025-07-01 06:14:07', '300'),
(5, '20250629005', '2025-06-29', 'Skylar', 'Juma', 'Naivasha Road', 'Nairobi', 'Nairobi', '00200', '3', '1', '1', '2', '2026-12-12', 'Female', '2020-06-12', '0011450450', '0022450450', '0033450450', '1', 'default.png', '2025-06-29 17:39:56', '2025-07-01 06:14:07', '0'),
(6, '20250630006', '2025-06-30', 'Liana', 'Menz', '4450 Atabro street', 'Nairobi', 'Nairobi', '00400', '3', '1', '1', '2', '2026-05-31', 'Female', '2010-01-12', '00987200', NULL, NULL, '1', 'default.png', '2025-06-30 03:27:04', '2025-07-01 06:14:07', NULL),
(7, '20250630007', '2025-06-30', 'Saisolomon', 'kamarinolos', 'Naivasha Road', 'Nairobi', 'Garissa', '00200', '3', '1', '1', '2', '2026-03-30', 'Male', '2013-02-02', '00986000', NULL, NULL, '1', 'http://127.0.0.1:8000/pci/api/v1/downloads/get/rpt/file/default.png', '2025-06-30 03:33:05', '2025-07-02 14:37:21', NULL);

CREATE TABLE IF NOT EXISTS studentsports (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  student varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  sport varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  achievement text COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  form varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  name varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  label varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  pass_mark varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  max_score varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '100',
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  tution_fee decimal(8,2) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO subjects (id, form, name, label, pass_mark, max_score, created_at, updated_at, tution_fee) VALUES
(1, '1', 'Introduction to arabic', 'Introduction To Arabic Form 1', '33', '100', '2025-06-29 13:39:41', '2025-07-01 05:21:13', 1250.00),
(2, '1', 'Sounds and letters arabic', 'Sounds And Letters Arabic Form 1', '60', '100', '2025-06-29 13:41:40', '2025-07-01 05:21:06', 1050.00),
(3, '1', 'Arabic smilies and proverbs', 'Arabic Smilies And Proverbs Form 1', '49', '100', '2025-06-29 13:50:26', '2025-07-01 05:20:59', 1500.00),
(4, '1', 'Arabic sayings and proverbs', 'Arabic Sayings And Proverbs Form 1', '50', '100', '2025-06-29 13:50:36', '2025-07-01 05:20:00', 3000.00),
(5, '5', 'My advanced subject', 'My Advanced Subject Form 5', '35', '100', '2025-07-01 05:18:18', '2025-07-01 05:18:44', 10000.00);

CREATE TABLE IF NOT EXISTS terms (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  year varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  label varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  start varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  end varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  is_current tinyint(1) NOT NULL DEFAULT '0',
  f1_fee varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  f2_fee varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  f3_fee varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  f4_fee varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY terms_year_label_unique (year,label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO terms (id, year, label, start, end, is_current, f1_fee, f2_fee, f3_fee, f4_fee, created_at, updated_at) VALUES
(1, '2025', 'TERM1', '2025-01-02', '2025-03-30', 0, '0', '0', '0', '0', '2025-06-29 13:30:31', '2025-07-01 06:14:07'),
(2, '2025', 'TERM2', '2025-05-02', '2025-07-30', 1, '0', '0', '0', '0', '2025-06-29 13:32:06', '2025-07-01 06:14:07'),
(3, '2025', 'TERM3', '2025-09-02', '2025-10-30', 0, '0', '0', '0', '0', '2025-06-29 13:32:41', '2025-07-01 06:14:07'),
(4, '2026', 'TERM1', '2026-01-01', '2026-03-01', 0, '0', '0', '0', '0', '2025-06-30 07:45:36', '2025-07-01 06:14:07'),
(5, '2026', 'TERM2', '2026-04-04', '2026-06-06', 0, '0', '0', '0', '0', '2025-06-30 07:49:47', '2025-07-01 06:14:07');

CREATE TABLE IF NOT EXISTS timetables (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  current_term varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  day varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  date varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  time varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  stream varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  subject varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  teacher varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  datetime varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  color varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Success',
  lesson_name varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  duration varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY timetables_teacher_datetime_unique (teacher,datetime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO timetables (id, current_term, day, date, time, stream, subject, teacher, datetime, created_at, updated_at, color, lesson_name, duration) VALUES
(2, '2', 'Monday', '2025-07-07', '10:00', '4', '5', '5', '202507071000', '2025-07-03 10:55:37', '2025-07-03 10:55:37', 'Success', 'My advanced subject', '30'),
(3, '2', 'Monday', '2025-07-07', '10:00', '1', '2', '4', '202507071000', '2025-07-03 10:59:26', '2025-07-03 12:40:58', 'Primary', 'Sounds and letters arabic', '45'),
(4, '2', 'Tuesday', '2025-07-08', '11:45', '4', '5', '2', '202507081145', '2025-07-03 11:47:29', '2025-07-03 12:33:13', 'Warning', 'My advanced subject', '60'),
(5, '2', 'Thursday', '2025-07-03', '15:00', '2', '3', '4', '202507031500', '2025-07-03 12:49:29', '2025-07-03 12:49:29', 'Primary', 'Arabic smilies and proverbs', '60');

CREATE TABLE IF NOT EXISTS translations (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  en text COLLATE utf8mb4_unicode_ci NOT NULL,
  ar text COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO translations (id, en, ar, created_at, updated_at) VALUES
(1, 'Enter your email and password to sign in!', 'أدخل بريدك الإلكتروني وكلمة المرور لتسجيل الدخول!', '2025-06-28 08:40:48', '2025-06-28 08:40:48'),
(2, 'Sign in', 'تسجيل الدخول', '2025-06-28 08:40:55', '2025-06-28 08:46:37'),
(3, 'Email', 'بريد إلكتروني', '2025-06-28 08:42:30', '2025-06-28 08:43:08'),
(4, 'Password', 'كلمة المرور', '2025-06-28 08:43:42', '2025-06-28 08:43:42'),
(5, 'Keep me logged in', 'أبقني مسجلاً الدخول', '2025-06-28 08:44:43', '2025-06-28 08:44:43'),
(6, 'Forgot password?', 'هل نسيت كلمة السر؟', '2025-06-28 08:45:40', '2025-06-28 08:45:40'),
(7, 'System Translations', 'ترجمات النظام', '2025-07-03 18:22:42', '2025-07-03 18:22:42'),
(8, 'Use this page to translate system language words from english to arabic and vice versa.', 'استخدم هذه الصفحة لترجمة كلمات لغة النظام من الإنجليزية إلى العربية والعكس.', '2025-07-03 18:23:23', '2025-07-03 18:23:23'),
(9, 'Home', 'بيت', '2025-07-03 18:23:58', '2025-07-03 18:23:58'),
(10, 'Translate words', 'ترجمة الكلمات', '2025-07-03 18:25:59', '2025-07-03 18:25:59'),
(11, 'Export words', 'تصدير الكلمات', '2025-07-03 18:26:26', '2025-07-03 18:26:26'),
(12, 'Translate words & Sentences', 'ترجمة الكلمات والجمل', '2025-07-03 18:27:33', '2025-07-03 18:39:19'),
(13, 'Enter english word or sentence', 'أدخل الكلمة أو الجملة باللغة الإنجليزية', '2025-07-03 18:28:01', '2025-07-03 18:28:01'),
(14, 'Enter arabic word or sentence', 'أدخل الكلمة أو الجملة العربية', '2025-07-03 18:28:31', '2025-07-03 18:28:31'),
(15, 'English', 'إنجليزي', '2025-07-03 18:30:24', '2025-07-03 18:30:24'),
(16, 'Arabic', 'عربي', '2025-07-03 18:30:42', '2025-07-03 18:30:42'),
(17, 'Save Translation', 'حفظ الترجمة', '2025-07-03 18:53:20', '2025-07-03 18:53:20'),
(18, 'Summaries', 'ملخصات', '2025-07-04 05:36:43', '2025-07-04 05:36:43'),
(19, 'Students', 'طلاب', '2025-07-04 05:37:06', '2025-07-04 05:37:06'),
(20, 'Teachers', 'المعلمين', '2025-07-04 05:37:30', '2025-07-04 05:37:30'),
(21, 'Manage Teachers', 'إدارة المعلمين', '2025-07-04 05:38:05', '2025-07-04 05:38:05'),
(22, 'Teacher Subjects', 'مواضيع المعلم', '2025-07-04 05:38:53', '2025-07-04 05:38:53'),
(23, 'Academics', 'الأكاديميين', '2025-07-04 05:39:29', '2025-07-04 05:39:29'),
(24, 'Performance', 'أداء', '2025-07-04 05:39:52', '2025-07-04 05:39:52'),
(25, 'Timetabling', 'الجدول الزمني', '2025-07-04 05:40:14', '2025-07-04 05:40:14'),
(26, 'Finance', 'تمويل', '2025-07-04 05:40:32', '2025-07-04 05:40:32'),
(27, 'Administration', 'إدارة', '2025-07-04 05:40:56', '2025-07-04 05:40:56'),
(28, 'Library', 'مكتبة', '2025-07-04 05:41:16', '2025-07-04 05:41:16'),
(29, 'Sports & Activities', 'الرياضة والأنشطة', '2025-07-04 05:41:50', '2025-07-04 05:41:50'),
(30, 'Menu', 'قائمة طعام', '2025-07-04 05:42:26', '2025-07-04 05:42:26'),
(31, 'Lang', 'لانج', '2025-07-04 05:42:57', '2025-07-04 05:42:57');

CREATE TABLE IF NOT EXISTS tsubjects (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  teacher varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  subject varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tsubjects (id, teacher, subject, created_at, updated_at) VALUES
(1, '5', '3', '2025-07-02 15:16:17', '2025-07-02 16:36:19'),
(2, '5', '4', '2025-07-02 16:35:46', '2025-07-02 16:35:46'),
(3, '4', '1', '2025-07-02 16:35:54', '2025-07-02 16:35:54'),
(4, '2', '4', '2025-07-02 16:36:01', '2025-07-02 16:36:01');

CREATE TABLE IF NOT EXISTS users (
  id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  fname varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  lname varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  address varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  city varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  county varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  zip varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  email varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  phone varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  email_verified_at timestamp NULL DEFAULT NULL,
  password varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  is_super tinyint(1) NOT NULL DEFAULT '0',
  is_admin tinyint(1) NOT NULL DEFAULT '0',
  is_lib tinyint(1) NOT NULL DEFAULT '0',
  is_fin tinyint(1) NOT NULL DEFAULT '0',
  is_teacher tinyint(1) NOT NULL DEFAULT '0',
  is_parent tinyint(1) NOT NULL DEFAULT '1',
  is_active tinyint(1) NOT NULL DEFAULT '1',
  pic varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png',
  remember_token varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email),
  UNIQUE KEY users_phone_unique (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, fname, lname, address, city, county, zip, email, phone, email_verified_at, password, is_super, is_admin, is_lib, is_fin, is_teacher, is_parent, is_active, pic, remember_token, created_at, updated_at) VALUES
(1, 'Admin', 'Super user', '7th street eastleight', 'nairobi', 'nairobi', '400560', 'super.admin@selms-app.com', '254705000000', NULL, '$2y$10$flVDglO55rcIRk8YJopKbumwY3hcB0GZJ3V3GBflJVt6QLkUaKVlC', 1, 0, 0, 0, 0, 0, 1, 'default.png', NULL, '2025-06-14 14:28:43', '2025-06-14 14:28:43'),
(2, 'Ahmed', 'Shambuli', '7th street eastleight', 'Nairobi', 'Nairobi', '00560', 'ahmed.sha@selms-app.com', '254702000000', NULL, '$2y$10$e8/b3hJhitQLZ9PeeFDlRe0XMYVKVMmJ2NfxfnPt6/szTwExpefCC', 0, 0, 0, 0, 1, 0, 1, 'default.png', NULL, '2025-06-29 07:02:04', '2025-06-30 05:34:03'),
(3, 'Mohamed', 'Medi', '7th street eastleight', 'nairobi', 'nairobi', '40560', 'm.medi@selms-app.com', '254706000000', NULL, '$2y$10$D6pJD8a5rLyqU9GUE6dcFO6YrK3LROF4tEkjl7sSJEOJghDUDIBBK', 0, 0, 0, 0, 0, 1, 1, 'default.png', NULL, '2025-06-29 13:58:29', '2025-07-07 04:19:18'),
(4, 'Kosta', 'Lupinopolos', '5th street eastleigh', 'Nairobi', 'Nairobi', '40030', 'k.lupinopolos@gmail.com', '254722000001', NULL, '$2y$10$A2EEafDaTxYsznyWhAQQPuykMJl0FsJkUIVXg9MSCxC9KqzjW5qtS', 0, 0, 0, 0, 1, 0, 1, 'default.png', NULL, '2025-06-30 05:21:12', '2025-06-30 05:21:12'),
(5, 'Eveginia', 'Polinapolos', '8th street eastleigh', 'Nairobi', 'Nairobi', '30040', 'e.polinapolos@outlook.com', '254722000003', NULL, '$2y$10$tkC2j5OJQfyXe9M7BWJ9kOf/TbUGNdzr0aJJQ8iA8GfKGRoeWw5wu', 0, 0, 0, 0, 1, 0, 1, 'default.png', NULL, '2025-06-30 05:22:57', '2025-06-30 05:22:57'),
(6, 'Emman', 'Wafula', '1233 Valencia Close', 'Nairobi', 'Nairobi', '50600', 'lib.admin@selms-app.com', '254722004455', NULL, '$2y$10$bCNs3UMAQtHIpQosrTcpkeiliVdslazq2zWmyiufp2PFk2LkBAbeG', 0, 0, 0, 1, 0, 0, 1, 'default.png', NULL, '2025-07-07 04:03:33', '2025-07-07 04:20:03');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
