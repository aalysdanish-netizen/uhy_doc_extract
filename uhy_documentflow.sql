-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 01:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uhy_documentflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `old_value`, `new_value`, `ip_address`, `created_at`) VALUES
(1, 1, 'user_created', 'user', 1, 'First Admin created by setup.php', NULL, NULL, '::1', '2026-09-28 06:20:58'),
(2, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-28 06:22:18'),
(3, 1, 'password_changed', 'user', 1, 'User changed their password', NULL, NULL, '::1', '2026-09-28 06:27:35'),
(4, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-28 06:27:44'),
(5, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-28 06:28:37'),
(6, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-28 06:29:15'),
(7, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-28 06:45:28'),
(8, 1, 'user_created', 'user', 2, 'Created user nurul@uhy.docs.hub.com', NULL, NULL, '::1', '2026-09-28 07:10:00'),
(9, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-28 07:10:45'),
(10, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-28 07:10:55'),
(11, 2, 'password_changed', 'user', 2, 'User changed their password', NULL, NULL, '::1', '2026-09-28 07:11:20'),
(12, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-09-28 07:12:02'),
(13, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-28 07:12:11'),
(14, 1, 'user_updated', 'user', 2, 'Updated user nurul@uhy.docs.hub.com', 'status: active', 'status: inactive', '::1', '2026-09-28 07:12:42'),
(15, 1, 'user_activated', 'user', 2, 'Activated user nurul@uhy.docs.hub.com', 'inactive', 'active', '::1', '2026-09-28 07:13:06'),
(16, 1, 'password_reset', 'user', 2, 'Reset password for nurul@uhy.docs.hub.com', NULL, NULL, '::1', '2026-09-28 07:13:11'),
(17, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-28 07:15:38'),
(18, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-28 07:16:28'),
(19, 1, 'client_created', 'client', 2, 'Created client ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-28 07:42:07'),
(20, 1, 'client_created', 'client', 3, 'Created client XYZ Holdings', NULL, NULL, '::1', '2026-09-28 07:42:30'),
(21, 1, 'client_deactivated', 'client', 3, 'Deactivated client XYZ Holdings', 'active', 'inactive', '::1', '2026-09-28 07:42:49'),
(22, 1, 'client_activated', 'client', 3, 'Activated client XYZ Holdings', 'inactive', 'active', '::1', '2026-09-28 07:42:56'),
(23, 1, 'client_deactivated', 'client', 3, 'Deactivated client XYZ Holdings', 'active', 'inactive', '::1', '2026-09-28 07:43:13'),
(24, 1, 'document_type_created', 'document_type', 6, 'Created document type Tax Computation', NULL, NULL, '::1', '2026-09-28 07:44:40'),
(25, 1, 'document_type_updated', 'document_type', 6, 'Updated document type Tax Computation', NULL, NULL, '::1', '2026-09-28 07:45:06'),
(26, 1, 'document_type_updated', 'document_type', 1, 'Updated document type Company Profile', NULL, NULL, '::1', '2026-09-28 07:47:02'),
(27, 1, 'document_uploaded', 'document', 2, 'Uploaded Other (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-28 07:52:02'),
(28, 1, 'client_activated', 'client', 3, 'Activated client XYZ Holdings', 'inactive', 'active', '::1', '2026-09-28 08:01:39'),
(29, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-28 08:02:08'),
(30, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-29 01:13:59'),
(31, 1, 'document_uploaded', 'document', 3, 'Uploaded Other (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-29 01:17:10'),
(32, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-29 01:51:57'),
(33, 1, 'login_failed', 'user', 1, 'Failed login: admin@uhy.docs.hub.com', NULL, NULL, '::1', '2026-09-29 02:16:59'),
(34, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-29 02:17:15'),
(35, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:22:26'),
(36, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:22:49'),
(37, 1, 'ocr_run', 'document_version', 2, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:23:03'),
(38, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:23:32'),
(39, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:23:51'),
(40, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:25:41'),
(41, 1, 'document_type_updated', 'document_type', 6, 'Updated document type Tax Computation', NULL, NULL, '::1', '2026-09-29 02:31:22'),
(42, 1, 'document_type_updated', 'document_type', 6, 'Updated document type Tax Computation', NULL, NULL, '::1', '2026-09-29 02:31:46'),
(43, 1, 'document_type_updated', 'document_type', 6, 'Updated document type Tax Computation', NULL, NULL, '::1', '2026-09-29 02:32:06'),
(44, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:32:46'),
(45, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:36:39'),
(46, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:43:46'),
(47, 1, 'document_type_updated', 'document_type', 5, 'Updated document type Other', NULL, NULL, '::1', '2026-09-29 02:44:17'),
(48, 1, 'document_type_updated', 'document_type', 5, 'Updated document type Other', NULL, NULL, '::1', '2026-09-29 02:44:26'),
(49, 1, 'document_type_updated', 'document_type', 5, 'Updated document type Other', NULL, NULL, '::1', '2026-09-29 02:44:33'),
(50, 1, 'ocr_run', 'document_version', 3, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 02:44:43'),
(51, 1, 'ocr_run', 'document_version', 2, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 03:05:45'),
(52, 1, 'document_uploaded', 'document', 4, 'Uploaded Directors Information (2026) for XYZ Holdings', NULL, NULL, '::1', '2026-09-29 03:07:11'),
(53, 1, 'ocr_run', 'document_version', 4, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 03:07:33'),
(54, 1, 'document_uploaded', 'document', 5, 'Uploaded Bank Details (2026) for XYZ Holdings', NULL, NULL, '::1', '2026-09-29 03:10:31'),
(55, 1, 'ocr_run', 'document_version', 5, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 03:10:38'),
(56, 1, 'ocr_run', 'document_version', 5, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 03:11:00'),
(57, 1, 'document_uploaded', 'document', 6, 'Uploaded Company Profile (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-29 03:13:00'),
(58, 1, 'ocr_run', 'document_version', 6, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-29 03:13:08'),
(59, 1, 'login_failed', 'user', 1, 'Failed login: admin@uhy.docs.hub.com', NULL, NULL, '::1', '2026-09-30 01:07:43'),
(60, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-30 01:07:52'),
(61, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-30 02:07:30'),
(62, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-30 02:07:43'),
(63, 2, 'password_changed', 'user', 2, 'User changed their password', NULL, NULL, '::1', '2026-09-30 02:08:16'),
(64, 2, 'document_uploaded', 'document', 7, 'Uploaded Other (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-30 02:09:35'),
(65, 2, 'document_deleted', 'document', 7, 'Deleted Other (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-09-30 02:09:47'),
(66, 2, 'document_uploaded', 'document', 8, 'Uploaded Bank Details (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-30 02:13:21'),
(67, 2, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-09-30 02:13:31'),
(68, 2, 'task_status_changed', 'task', 12, 'Task \"Prepare Bank Details\" set to completed', 'uploaded', 'completed', '::1', '2026-09-30 02:48:57'),
(69, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-09-30 02:51:55'),
(70, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-30 02:52:04'),
(71, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-30 02:58:56'),
(72, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-30 02:59:06'),
(73, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-30 03:02:25'),
(74, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-09-30 03:11:05'),
(75, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-30 03:11:13'),
(76, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-30 03:11:38'),
(77, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-30 03:11:55'),
(78, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-09-30 03:23:54'),
(79, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-30 05:40:02'),
(80, 1, 'user_deactivated', 'user', 2, 'Deactivated user nurul@uhy.docs.hub.com', 'active', 'inactive', '::1', '2026-09-30 05:40:24'),
(81, 1, 'user_activated', 'user', 2, 'Activated user nurul@uhy.docs.hub.com', 'inactive', 'active', '::1', '2026-09-30 05:40:48'),
(82, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-30 05:41:42'),
(83, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-30 05:41:55'),
(84, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-30 07:01:37'),
(85, 1, 'document_uploaded', 'document', 9, 'Uploaded Other (1) (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-30 07:06:12'),
(86, 1, 'document_uploaded', 'document', 10, 'Uploaded Other (2) (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-09-30 07:06:12'),
(87, 1, 'ocr_run', 'document_version', 9, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-30 07:06:28'),
(88, 1, 'ocr_run', 'document_version', 10, 'Extracted information from version 1', NULL, NULL, '::1', '2026-09-30 07:06:29'),
(89, 1, 'document_deleted', 'document', 10, 'Deleted Other (2) (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-09-30 07:07:58'),
(90, 1, 'task_status_changed', 'task', 13, 'Task \"Prepare Other (1)\" set to completed', 'uploaded', 'completed', '::1', '2026-09-30 07:09:01'),
(91, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-09-30 07:34:52'),
(92, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-09-30 07:35:03'),
(93, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-09-30 08:19:40'),
(94, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-09-30 08:19:50'),
(95, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:21:41'),
(96, 1, 'avatar_updated', 'user', 1, 'Updated profile photo', NULL, NULL, '::1', '2026-10-01 01:21:58'),
(97, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-01 01:22:50'),
(98, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:22:59'),
(99, 2, 'avatar_updated', 'user', 2, 'Updated profile photo', NULL, NULL, '::1', '2026-10-01 01:32:46'),
(100, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-10-01 01:32:50'),
(101, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:33:01'),
(102, 1, 'user_created', 'user', 3, 'Created user alysdanish@uhy.docs.hub.com', NULL, NULL, '::1', '2026-10-01 01:34:31'),
(103, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-01 01:34:45'),
(104, 3, 'login', 'user', 3, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:34:54'),
(105, 3, 'password_changed', 'user', 3, 'User changed their password', NULL, NULL, '::1', '2026-10-01 01:35:29'),
(106, 3, 'logout', 'user', 3, 'User logged out', NULL, NULL, '::1', '2026-10-01 01:36:17'),
(107, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:36:27'),
(108, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-01 01:36:38'),
(109, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:36:47'),
(110, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-10-01 01:37:01'),
(111, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 01:37:10'),
(112, 1, 'document_deleted', 'document', 9, 'Deleted Other (1) (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 02:38:08'),
(113, 1, 'document_deleted', 'document', 2, 'Deleted Other (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 02:54:46'),
(114, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 03:14:11'),
(115, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 03:16:41'),
(116, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 05:52:39'),
(117, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 05:57:41'),
(118, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 06:03:33'),
(119, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 06:03:59'),
(120, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 06:06:57'),
(121, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 06:08:20'),
(122, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 06:08:39'),
(123, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 06:14:25'),
(124, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-01 06:18:47'),
(125, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-01 06:19:08'),
(126, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 06:26:20'),
(127, 1, 'document_deleted', 'document', 5, 'Deleted Bank Details (2026) for XYZ Holdings', 'draft', 'deleted', '::1', '2026-10-01 06:32:24'),
(128, 1, 'document_deleted', 'document', 3, 'Deleted Other (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-01 06:37:21'),
(129, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 06:37:39'),
(130, 1, 'document_deleted', 'document', 4, 'Deleted Directors Information (2026) for XYZ Holdings', 'draft', 'deleted', '::1', '2026-10-01 06:46:09'),
(131, 1, 'document_restored', 'document', 2, 'Restored Other (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 06:55:47'),
(132, 1, 'document_restored', 'document', 7, 'Restored Other (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 06:56:10'),
(133, 1, 'document_restored', 'document', 5, 'Restored Bank Details (2026) for XYZ Holdings', 'deleted', 'draft', '::1', '2026-10-01 06:57:03'),
(134, 1, 'document_restored', 'document', 4, 'Restored Directors Information (2026) for XYZ Holdings', 'deleted', 'draft', '::1', '2026-10-01 06:57:38'),
(135, 1, 'document_restored', 'document', 3, 'Restored Other (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-01 07:08:23'),
(136, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-02 00:43:12'),
(137, 1, 'document_deleted', 'document', 4, 'Deleted Directors Information (2026) for XYZ Holdings', 'draft', 'deleted', '::1', '2026-10-02 00:46:07'),
(138, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-02 00:51:05'),
(139, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-02 01:04:59'),
(140, 1, 'document_deleted', 'document', 6, 'Deleted Company Profile (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-02 01:05:10'),
(141, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-02 06:45:33'),
(142, 1, 'document_deleted', 'document', 7, 'Deleted Other (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-02 06:45:48'),
(143, 1, 'document_deleted', 'document', 2, 'Deleted Other (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-02 06:51:16'),
(144, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-02 07:15:09'),
(145, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-02 07:15:16'),
(146, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-02 07:18:58'),
(147, 1, 'task_status_changed', 'task', 14, 'Task \"Prepare Other (2)\" set to completed', 'uploaded', 'completed', '::1', '2026-10-02 08:38:38'),
(148, 1, 'task_status_changed', 'task', 10, 'Task \"Prepare Company Profile\" set to completed', 'uploaded', 'completed', '::1', '2026-10-02 08:38:50'),
(149, 1, 'task_status_changed', 'task', 8, 'Task \"Prepare Directors Information\" set to completed', 'uploaded', 'completed', '::1', '2026-10-02 08:39:23'),
(150, 1, 'task_status_changed', 'task', 9, 'Task \"Prepare Bank Details\" set to in_progress', 'uploaded', 'in_progress', '::1', '2026-10-02 08:39:57'),
(151, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-03 14:25:44'),
(152, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-05 01:37:17'),
(153, 1, 'document_restored', 'document', 6, 'Restored Company Profile (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-05 01:37:55'),
(154, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-05 01:38:32'),
(155, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-05 01:39:31'),
(156, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-05 01:47:13'),
(157, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-05 01:56:53'),
(158, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-05 01:58:39'),
(159, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-06 03:27:29'),
(160, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-06 03:27:45'),
(161, 1, 'document_restored', 'document', 10, 'Restored Other (2) (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-06 03:30:51'),
(162, 1, 'document_restored', 'document', 4, 'Restored Directors Information (2026) for XYZ Holdings', 'deleted', 'draft', '::1', '2026-10-06 03:31:05'),
(163, 1, 'document_restored', 'document', 8, 'Restored Bank Details (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-06 03:31:08'),
(164, 1, 'document_restored', 'document', 7, 'Restored Other (2026) for ABC Sdn Bhd', 'deleted', 'draft', '::1', '2026-10-06 03:31:12'),
(165, 1, 'document_deleted', 'document', 8, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-06 03:40:53'),
(166, 1, 'document_deleted', 'document', 7, 'Deleted Other (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-06 03:41:24'),
(167, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-06 04:54:50'),
(168, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-06 07:36:42'),
(169, 1, 'document_uploaded', 'document', 11, 'Uploaded Other (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-10-06 07:42:07'),
(170, 1, 'ocr_run', 'document_version', 11, 'Extracted information from version 1', NULL, NULL, '::1', '2026-10-06 07:42:16'),
(171, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-06 07:42:34'),
(172, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-06 07:42:54'),
(173, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-06 07:44:18'),
(174, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-06 07:53:22'),
(175, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-06 07:54:09'),
(176, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-07 04:05:29'),
(177, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-07 04:05:32'),
(178, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-07 07:19:31'),
(179, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-07 07:30:15'),
(180, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-07 07:44:37'),
(181, 1, 'user_updated', 'user', 2, 'Updated user nurul@uhy.docs.hub.com', 'staff_type: ', 'staff_type: staff', '::1', '2026-10-07 07:46:58'),
(182, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-08 03:30:05'),
(183, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-08 05:27:05'),
(184, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-10-08 05:27:13'),
(185, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-10-08 05:30:27'),
(186, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-08 05:30:39'),
(187, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-08 05:38:26'),
(188, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', '2026-10-08 05:38:42'),
(189, 2, 'document_uploaded', 'document', 12, 'Uploaded Bank Details (2026) for ABC Sdn Bhd', NULL, NULL, '::1', '2026-10-08 05:42:42'),
(190, 2, 'ocr_run', 'document_version', 12, 'Extracted information from version 1', NULL, NULL, '::1', '2026-10-08 05:42:46'),
(191, 2, 'document_deleted', 'document', 12, 'Deleted Bank Details (2026) for ABC Sdn Bhd', 'draft', 'deleted', '::1', '2026-10-08 05:43:50'),
(192, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', '2026-10-08 06:12:03'),
(193, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', '2026-10-08 06:12:09'),
(194, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', '2026-10-08 06:21:00'),
(195, 1, 'login_failed', 'user', 1, 'Failed login: admin@uhy.docs.hub.com', NULL, NULL, '::1', '2026-10-08 07:46:50'),
(196, 1, 'login_failed', 'user', 1, 'Failed login: admin@uhy.docs.hub.com', NULL, NULL, '::1', '2026-10-08 07:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(10) UNSIGNED NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `contact_person` varchar(120) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `registration_no`, `contact_person`, `status`, `created_by`, `created_at`) VALUES
(2, 'ABC Sdn Bhd', NULL, NULL, 'active', 1, '2026-09-28 07:42:07'),
(3, 'XYZ Holdings', NULL, NULL, 'active', 1, '2026-09-28 07:42:30');

-- --------------------------------------------------------

--
-- Table structure for table `community_attachments`
--

CREATE TABLE `community_attachments` (
  `attachment_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_channels`
--

CREATE TABLE `community_channels` (
  `channel_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_channels`
--

INSERT INTO `community_channels` (`channel_id`, `name`, `description`, `is_active`) VALUES
(1, 'General Audit Discussion', 'Open discussion about audit work', 1),
(2, 'Document Preparation Help', 'Questions about preparing documents', 1),
(3, 'Tax & Accounting', 'Tax and accounting topics', 1),
(4, 'Internship / New Staff', 'A friendly space for interns and new staff', 1);

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

CREATE TABLE `community_posts` (
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `channel_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `parent_post_id` bigint(20) UNSIGNED DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_reads`
--

CREATE TABLE `community_reads` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `channel_id` int(10) UNSIGNED NOT NULL,
  `last_read_post_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `community_reads`
--

INSERT INTO `community_reads` (`user_id`, `channel_id`, `last_read_post_id`) VALUES
(1, 1, 0),
(1, 2, 0),
(1, 3, 0),
(1, 4, 0),
(2, 1, 0),
(2, 2, 0),
(2, 3, 0),
(2, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `type_id` int(10) UNSIGNED NOT NULL,
  `document_name` varchar(200) NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `current_version_id` int(10) UNSIGNED DEFAULT NULL,
  `prepared_from_version_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('draft','in_review','returned','approved','final') NOT NULL DEFAULT 'draft',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `client_id`, `type_id`, `document_name`, `year`, `uploaded_by`, `current_version_id`, `prepared_from_version_id`, `status`, `is_deleted`, `deleted_at`, `deleted_by`, `created_at`, `updated_at`) VALUES
(2, 2, 5, 'Other', 2026, 1, 2, NULL, 'draft', 1, '2026-10-02 14:51:16', 1, '2026-09-28 07:52:01', '2026-10-02 06:51:16'),
(3, 2, 5, 'Other', 2026, 1, 3, NULL, 'draft', 0, NULL, NULL, '2026-09-29 01:17:10', '2026-10-01 07:08:23'),
(4, 3, 3, 'Directors Information', 2026, 1, 4, NULL, 'draft', 0, NULL, NULL, '2026-09-29 03:07:11', '2026-10-06 03:31:05'),
(5, 3, 2, 'Bank Details', 2026, 1, 5, NULL, 'draft', 0, NULL, NULL, '2026-09-29 03:10:31', '2026-10-01 06:57:03'),
(6, 2, 1, 'Company Profile', 2026, 1, 6, NULL, 'draft', 0, NULL, NULL, '2026-09-29 03:12:59', '2026-10-05 01:37:55'),
(7, 2, 5, 'Other', 2026, 2, 7, NULL, 'draft', 1, '2026-10-06 11:41:24', 1, '2026-09-30 02:09:35', '2026-10-06 03:41:24'),
(8, 2, 2, 'Bank Details', 2026, 2, 8, NULL, 'draft', 1, '2026-10-06 11:40:53', 1, '2026-09-30 02:13:21', '2026-10-06 03:40:53'),
(9, 2, 5, 'Other (1)', 2026, 1, 9, NULL, 'draft', 1, '2026-10-01 10:38:08', 1, '2026-09-30 07:06:12', '2026-10-01 02:38:08'),
(10, 2, 5, 'Other (2)', 2026, 1, 10, NULL, 'draft', 0, NULL, NULL, '2026-09-30 07:06:12', '2026-10-06 03:30:51'),
(11, 2, 5, 'Other', 2026, 1, 11, NULL, 'draft', 0, NULL, NULL, '2026-10-06 07:42:07', '2026-10-06 07:42:07'),
(12, 2, 2, 'Bank Details', 2026, 2, 12, NULL, 'draft', 1, '2026-10-08 13:43:50', 2, '2026-10-08 05:42:42', '2026-10-08 05:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `document_changes`
--

CREATE TABLE `document_changes` (
  `change_id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `change_status` enum('pending','accepted','rejected','edited') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_extractions`
--

CREATE TABLE `document_extractions` (
  `extraction_id` int(10) UNSIGNED NOT NULL,
  `version_id` int(10) UNSIGNED NOT NULL,
  `source` enum('ocr','file') NOT NULL DEFAULT 'ocr',
  `raw_text` mediumtext DEFAULT NULL,
  `engine` varchar(50) DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT NULL,
  `status` enum('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_extractions`
--

INSERT INTO `document_extractions` (`extraction_id`, `version_id`, `source`, `raw_text`, `engine`, `confidence`, `status`, `created_at`) VALUES
(10, 3, 'ocr', 'sales id date_time | | payment_method [-] employee_id [~] cq\n16/3/2024 15:54 cash EMP-018\nINV-20240316-0011 | 16/3/2024 15:54 cash EMP-018\n16/3/2024 16:23 online payment EMP-018\nINV-20240316-0012 | 16/3/2024 16:23 onlinePayment EMP-018\nINV-20240316-0013 16/3/2024 16:51 onlinePayment EMP-001\nINV-20240316-0014  16/3/202417:20 cash EMP-001\nA\nn vb & uv\nsales _id date_time payment_method @ employee_id a q\n7/10/2025 11:58 onlinePayment EMP-027\n! LINV-20251017-0004 17/10/2025 11:58 onlinePayment EMP-027\n\\ INV-20251017-0005 17/10/2025 12:22 onlinePayment EMP-008\n- INV-20251017-0006 17/10/202512:45 cash EMP-030\n+ INV-20251017-0007 17/10/2025 13:11 cash EMP-032', 'tesseract', NULL, 'done', '2026-09-29 02:44:43'),
(11, 2, 'ocr', '“ af 4\n: f -\nWANN  _—— —_ —\nMae\ni NG j an i\n\\\niit', 'tesseract', NULL, 'done', '2026-09-29 03:05:45'),
(12, 4, 'file', NULL, NULL, NULL, 'done', '2026-09-29 03:07:33'),
(14, 5, 'ocr', 'SNAP 2014\nStee', 'tesseract', NULL, 'done', '2026-09-29 03:11:00'),
(15, 6, 'ocr', 'Mailings Review View “Developer Help Acrobat Se Pe Ihe\n— , Pind\na Pee eit Normal No Spacing Heading (| Ge Replace\nEBS alt=: |a- B~ Ip Select ~\n\nPetaroph iS styles 5 Editing\nragraph\nPe\n\n1. Introduction\nDuring the documentation process, staff are required to prepare and update various working\ndocuments based on current information received from clients. In some cases, staff need to\nrefer to previous-year documents and update them using information from current-year\ndocuments.\nCurrently, this process may involve several separate applications and manual steps. Staff may\ncapture a document using a mobile phone, transfer the image through a messaging application,\naccess it again using a computer, open the previous year\'s Word or Excel document, manually\nidentify outdated information, replace the information, and save the updated document\nAlthough each individual step is relatively simple, the overall process involves repeated manual\nactions and switching between different applications.\nTherefore, UHY DocumentFloyy is proposed as an internal web-based system to centralise\ndocument capture, document preparation, OCR-assisted information extraction, comparison,\ntask tracking, communication, and document history in one platform.\n\non Ti Accessibility: Investigate 5', 'tesseract', NULL, 'done', '2026-09-29 03:13:08'),
(16, 9, 'ocr', 'bac N o P Q R co Ls Ln v Bf x x = :\nYears (DATE) 2025 Bi YEAR 2025 Bi\n\nMonths (DATE) Nov ia MONTH Fr x\n\nEMP ID __~ |Sum of TOTAL COMMISSION Sum of commission employee id » prepared by |» Sum of commission paid _\n\nEMP-007 5.67 3.22 REFUND =EMP.001_ EMP-O17 5.67 Dec-25 emp a3 takde sales\nEMP-002 31.95 13.12 SEMP-002___EMP-017 22.04 emp 20 ada sales, tap tak\nEMP-004 34.61 19.49 =EMP.004 _ EMP-O17 34.67 emp 32 ada sales, tap tak\nEMP-006 55.54 31.07 “SEMP-006_EMP-017 56.61 emp 30, sepatutnya comn\nEMP-008 29.08 28.08 SEMP.008 _EMP-017 29.09 571.58\n\nEMP-008 2.90 4.95 =EMP-009___EMP-017 2.91\n\nEMP-022 7.82 450 SEMP.022_ EMP-O17 783 Nov-25 emp 30 dapat commissior\nEMP-023 45.64 25.88 ‘SEMP-023._EMP-017 45.67 Cc]\n\nEMP-027 11.60 6.47 SEMP.027__ EMP-017 1161\n\nEMP-028 0.34 0.19 SEMP.028__EMP-017 0.34\n\nEMP-030 5.42 3.03 =EMP.030_EMP-O17 163.2\n\nEMP-031 15.02 851 SEMP-091___ EMP-017 15.02\n\nEMP-032 50.81 28.07 “SEMP.092__EMP-017 50.86\n\nEMP-093 _ 34.76 19.65, =EMP-033___EMP-O17 94.79\n\nGrand Total 357.95 201.23 Grand Total 316.11', 'tesseract', NULL, 'done', '2026-09-30 07:06:28'),
(17, 10, 'ocr', 'Years (DATE) 2025 a YEAR 2028\n\n‘Months (OTE) Oct Ei MoH 10 Ei\n\n[EMPID__ + SumOtTOTALCOMMISSION Sum ot commission ‘employee ié» prepared by ~ Sum ot commission paid\n\n=MP-001 10.22 5.68 REFUND “EMP-001 _EMP-O17 10.29 ec-25 emp 33 talc sales\n\nesp.002 9.98 570 =empoo2 _eMP.017 10.01 emp 20 ada zoe, ap tak bayar commission commision bayardekat em 39\nep-008 40.98 nn SEMP-004ENP.017 4087 emp 32 ada sales, tap tak bayat\n\n9-005 9.10 2728 p.008 _EMP-017 928 fem 30, sepatuinya commission rm9.72, ap dooat $91.90\nvP-008 34.04 ao7t =ENP-008 ENP.017 3405 S788\n\neP-009 476 258 SEMP-009—_EHP.017 478\n\nevp-022 a6 649 =enpo22  ee-o17 1160 Nov-25 emp 20 dapat commission eb\n\nep-023 58.52 27 SENP-02_—EHP.O7 585 oct-25\n\nesp.025 560 aos sepa ENP.017 56 cq\n\neup.o27 see 352 =ENP-027 —_EMP-017 59\n\nep-028 138 075 sempozs eMP-017 135\n\nevp-030 seit ast =ENP-090 ENP.-017 17408\n\neP-031 1209 eet SEMP.OM_eHP-17 12.02\n\nse 032 2228 25.98 enpog2 _eMP.017 __ 42.28\n\n(rand Totat 36439 20847 ‘Grand Toat 2053.11', 'tesseract', NULL, 'done', '2026-09-30 07:06:29'),
(18, 11, 'ocr', 'be and is hereby authorized, for and on behalf of the Company :-\ni. to give order(s) or instruction(s) in writing to AHAM in relation to all transactions entered into or\nmade for the account of the Company;\nii. to direct AHAM to pay into any banking accounts maintained in the name of the Company\nprovided that the account numbers are provided to AHAM in advance.\nTHAT such instruction contemplated herein may be effected by electronic mail and that AHAM is\nfully authorized to accept and give effect to any instructions communicated via electronic mail and\n, that AHAM is absolved from any blame and consequently to hold AHAM harmless in respect of any\n_/ cost, loss, liability or expenses whatsoever which may be suffered or incurred by the Company as a\nresult of acting on any transactions received via electronic mail and where applicable, to indemnify\nAHAM for any cost, loss, liability or expenses whatsoever which may be suffered or incurred by\nAHAN in relying on the said instruction.\nTHAT the above specimen signatures of the authorized signatories be furnished to AHAM and shall\nremain in full force until receipt by AHAM of new resolution passed by the Board.\nBOARD OF DIRECTO!\nLEEMENG WE! ROSLIZAN BIN NEKMAT\nDate: 9 8 OCT 2025\n|', 'tesseract', NULL, 'done', '2026-10-06 07:42:16'),
(19, 12, 'ocr', 'Course Taken\nADVANCED FINANCIAL\nBEE K20aS MANAGEMENT\nBKATK3023 ADVANCED TAXATION\nBKAAK2013 ‘AUDITING AND ASSURANCE |\nDATABASE MANAGEMENT\nBKASK3053 DATABASE\nFINANCIAL ACCOUNTING AND\nBKARKS043 REPORTING IV\nBKALK3023 PUBLIC SECTOR ACCOUNTING\nSEMINAR IN MANAGEMENT\nBICAMia033 [ACCOUNTING', 'tesseract', NULL, 'done', '2026-10-08 05:42:46');

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `template_id` int(10) UNSIGNED NOT NULL,
  `type_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `type_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`type_id`, `name`, `is_active`) VALUES
(1, 'Company Profile', 1),
(2, 'Bank Details', 1),
(3, 'Directors Information', 1),
(4, 'Shareholder Register', 1),
(5, 'Other', 1),
(6, 'Tax Computation', 1);

-- --------------------------------------------------------

--
-- Table structure for table `document_type_fields`
--

CREATE TABLE `document_type_fields` (
  `type_field_id` int(10) UNSIGNED NOT NULL,
  `type_id` int(10) UNSIGNED NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `ocr_pattern` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_type_fields`
--

INSERT INTO `document_type_fields` (`type_field_id`, `type_id`, `field_key`, `label`, `ocr_pattern`, `sort_order`) VALUES
(1, 1, 'company_name', 'Company Name', 'Company Name|Name of Company', 1),
(2, 1, 'registration_no', 'Registration No.', 'Registration No|Company No|Reg No', 2),
(3, 1, 'address', 'Address', 'Address|Registered Address|Business Address', 3),
(4, 1, 'financial_year', 'Financial Year', 'Financial Year|Year End|FYE', 4),
(6, 2, 'bank_name', 'Bank Name', 'Bank Name|Bank', 1),
(7, 2, 'account_no', 'Account No.', 'Account No|Account Number|A/C No', 2),
(8, 2, 'account_name', 'Account Name', 'Account Name|Account Holder', 3),
(9, 6, 'address', 'Address', 'Address|Registered Address', 1),
(10, 6, 'financial_year', 'Financial Year', 'Financial Year|Year End|FYE', 2),
(11, 6, 'director', 'Director', 'Director|Directors', 3),
(12, 5, 'address', 'Address', 'Address|Registered Address', 1),
(13, 5, 'financial_year', 'Financial Year', 'Financial Year|Year End|FYE', 2),
(14, 5, 'director', 'Director', 'Director|Directors', 3);

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `version_id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `version_no` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `change_note` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_final` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_versions`
--

INSERT INTO `document_versions` (`version_id`, `document_id`, `version_no`, `file_path`, `file_type`, `original_name`, `file_size`, `change_note`, `created_by`, `created_at`, `is_final`) VALUES
(2, 2, 1, 'documents/2026/09/b5e813b09a3cbea6429baa7ae1f8dad8.jpg', 'jpg', 'd1.jpg', 93916, 'Uploaded', 1, '2026-09-28 07:52:01', 0),
(3, 3, 1, 'documents/2026/09/2b12b57fa95041c3759656b2fc13330d.png', 'png', 'Screenshot 2026-06-26 204450.png', 208293, 'Uploaded', 1, '2026-09-29 01:17:10', 0),
(4, 4, 1, 'documents/2026/09/4c55c82dcccda77db0cd13112b6f7915.docx', 'docx', 'Project Proposal-Muhammad Alysdanish Bin Mohd Faisal.docx', 228627, 'Uploaded', 1, '2026-09-29 03:07:11', 0),
(5, 5, 1, 'documents/2026/09/a4de71835d71f40fffda6774e83e984d.jpg', 'jpg', 'images.jpeg', 17747, 'Uploaded', 1, '2026-09-29 03:10:31', 0),
(6, 6, 1, 'documents/2026/09/64c5a5f5cbd24820deb00db633480802.jpg', 'jpg', 'ghgh.jpg', 165132, 'Uploaded', 1, '2026-09-29 03:12:59', 0),
(7, 7, 1, 'documents/2026/09/4ea7353d44d596aec4bf6ccc39a004ea.pdf', 'pdf', 'SBR Study Planner(Jul 26).pdf', 100908, 'Uploaded', 2, '2026-09-30 02:09:35', 0),
(8, 8, 1, 'documents/2026/09/71bf142a1d45048bf8a42f99b66eb116.pdf', 'pdf', 'SBR Study Planner(Jul 26).pdf', 100908, 'Uploaded', 2, '2026-09-30 02:13:21', 0),
(9, 9, 1, 'documents/2026/09/584ed4d563ccdffdcb1b6fedab89ea10.jpg', 'jpg', 'nov.jpg', 100983, 'Uploaded', 1, '2026-09-30 07:06:12', 0),
(10, 10, 1, 'documents/2026/09/8428f56f3d39d487f971284a552ce487.jpg', 'jpg', 'oct.jpg', 72801, 'Uploaded', 1, '2026-09-30 07:06:12', 0),
(11, 11, 1, 'documents/2026/10/5ad76fc216173cefb5221aa9a548f4b3.jpg', 'jpg', 'photo_2026-10-06_15-41-48.jpg', 145500, 'Uploaded', 1, '2026-10-06 07:42:07', 0),
(12, 12, 1, 'documents/2026/10/84722b47c4c08dbed77ccee55f47a6e8.png', 'png', 'Screenshot 2025-03-13 140805.png', 22048, 'Uploaded', 2, '2026-10-08 05:42:42', 0);

-- --------------------------------------------------------

--
-- Table structure for table `extraction_fields`
--

CREATE TABLE `extraction_fields` (
  `field_id` int(10) UNSIGNED NOT NULL,
  `extraction_id` int(10) UNSIGNED NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `extracted_value` text DEFAULT NULL,
  `confirmed_value` text DEFAULT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `confirmed_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `extraction_fields`
--

INSERT INTO `extraction_fields` (`field_id`, `extraction_id`, `field_key`, `label`, `extracted_value`, `confirmed_value`, `is_confirmed`, `confirmed_by`) VALUES
(1, 10, 'address', 'Address', '', '', 1, 1),
(2, 10, 'financial_year', 'Financial Year', '', '', 1, 1),
(3, 10, 'director', 'Director', '', '', 1, 1),
(4, 11, 'line_1_1', 'Line 1', '“ af 4', NULL, 0, NULL),
(5, 11, 'line_2_2', 'Line 2', ': f -', NULL, 0, NULL),
(6, 11, 'line_3_3', 'Line 3', 'WANN  _—— —_ —', NULL, 0, NULL),
(7, 11, 'line_4_4', 'Line 4', 'Mae', NULL, 0, NULL),
(8, 11, 'line_5_5', 'Line 5', 'i NG j an i', NULL, 0, NULL),
(9, 11, 'line_6_6', 'Line 6', '\\', NULL, 0, NULL),
(10, 11, 'line_7_7', 'Line 7', 'iit', NULL, 0, NULL),
(16, 15, 'line_1_1', 'Line 1', 'Mailings Review View “Developer Help Acrobat Se Pe Ihe', NULL, 0, NULL),
(17, 15, 'line_2_2', 'Line 2', '— , Pind', NULL, 0, NULL),
(18, 15, 'line_3_3', 'Line 3', 'a Pee eit Normal No Spacing Heading (| Ge Replace', NULL, 0, NULL),
(19, 15, 'ebs_alt_4', 'EBS alt=', '|a- B~ Ip Select ~', NULL, 0, NULL),
(20, 15, 'line_5_5', 'Line 5', 'Petaroph iS styles 5 Editing', NULL, 0, NULL),
(21, 15, 'line_6_6', 'Line 6', 'ragraph', NULL, 0, NULL),
(22, 15, 'line_7_7', 'Line 7', 'Pe', NULL, 0, NULL),
(23, 15, 'line_8_8', 'Line 8', '1. Introduction', NULL, 0, NULL),
(24, 15, 'line_9_9', 'Line 9', 'During the documentation process, staff are required to prepare and update various working', NULL, 0, NULL),
(25, 15, 'line_10_10', 'Line 10', 'documents based on current information received from clients. In some cases, staff need to', NULL, 0, NULL),
(26, 15, 'refer_to_previous_11', 'refer to previous', 'year documents and update them using information from current-year', NULL, 0, NULL),
(27, 15, 'line_12_12', 'Line 12', 'documents.', NULL, 0, NULL),
(28, 15, 'line_13_13', 'Line 13', 'Currently, this process may involve several separate applications and manual steps. Staff may', NULL, 0, NULL),
(29, 15, 'line_14_14', 'Line 14', 'capture a document using a mobile phone, transfer the image through a messaging application,', NULL, 0, NULL),
(30, 15, 'line_15_15', 'Line 15', 'access it again using a computer, open the previous year\'s Word or Excel document, manually', NULL, 0, NULL),
(31, 15, 'line_16_16', 'Line 16', 'identify outdated information, replace the information, and save the updated document', NULL, 0, NULL),
(32, 15, 'line_17_17', 'Line 17', 'Although each individual step is relatively simple, the overall process involves repeated manual', NULL, 0, NULL),
(33, 15, 'line_18_18', 'Line 18', 'actions and switching between different applications.', NULL, 0, NULL),
(34, 15, 'therefore_uhy_documentfloyy_is_proposed_as_an_inte', 'Therefore, UHY DocumentFloyy is proposed as an internal web', 'based system to centralise', NULL, 0, NULL),
(35, 15, 'document_capture_document_preparation_ocr_20', 'document capture, document preparation, OCR', 'assisted information extraction, comparison,', NULL, 0, NULL),
(36, 15, 'line_21_21', 'Line 21', 'task tracking, communication, and document history in one platform.', NULL, 0, NULL),
(37, 15, 'on_ti_accessibility_22', 'on Ti Accessibility', 'Investigate 5', NULL, 0, NULL),
(38, 16, 'line_1_1', 'Line 1', 'bac N o P Q R co Ls Ln v Bf x x = :', NULL, 0, NULL),
(39, 16, 'line_2_2', 'Line 2', 'Years (DATE) 2025 Bi YEAR 2025 Bi', NULL, 0, NULL),
(40, 16, 'line_3_3', 'Line 3', 'Months (DATE) Nov ia MONTH Fr x', NULL, 0, NULL),
(41, 16, 'line_4_4', 'Line 4', 'EMP ID __~ |Sum of TOTAL COMMISSION Sum of commission employee id » prepared by |» Sum of commission paid _', NULL, 0, NULL),
(42, 16, 'emp_5', 'EMP', '007 5.67 3.22 REFUND =EMP.001_ EMP-O17 5.67 Dec-25 emp a3 takde sales', NULL, 0, NULL),
(43, 16, 'emp_6', 'EMP', '002 31.95 13.12 SEMP-002___EMP-017 22.04 emp 20 ada sales, tap tak', NULL, 0, NULL),
(44, 16, 'emp_7', 'EMP', '004 34.61 19.49 =EMP.004 _ EMP-O17 34.67 emp 32 ada sales, tap tak', NULL, 0, NULL),
(45, 16, 'emp_8', 'EMP', '006 55.54 31.07 “SEMP-006_EMP-017 56.61 emp 30, sepatutnya comn', NULL, 0, NULL),
(46, 16, 'emp_9', 'EMP', '008 29.08 28.08 SEMP.008 _EMP-017 29.09 571.58', NULL, 0, NULL),
(47, 16, 'emp_10', 'EMP', '008 2.90 4.95 =EMP-009___EMP-017 2.91', NULL, 0, NULL),
(48, 16, 'emp_11', 'EMP', '022 7.82 450 SEMP.022_ EMP-O17 783 Nov-25 emp 30 dapat commissior', NULL, 0, NULL),
(49, 16, 'emp_12', 'EMP', '023 45.64 25.88 ‘SEMP-023._EMP-017 45.67 Cc]', NULL, 0, NULL),
(50, 16, 'emp_13', 'EMP', '027 11.60 6.47 SEMP.027__ EMP-017 1161', NULL, 0, NULL),
(51, 16, 'emp_14', 'EMP', '028 0.34 0.19 SEMP.028__EMP-017 0.34', NULL, 0, NULL),
(52, 16, 'emp_15', 'EMP', '030 5.42 3.03 =EMP.030_EMP-O17 163.2', NULL, 0, NULL),
(53, 16, 'emp_16', 'EMP', '031 15.02 851 SEMP-091___ EMP-017 15.02', NULL, 0, NULL),
(54, 16, 'emp_17', 'EMP', '032 50.81 28.07 “SEMP.092__EMP-017 50.86', NULL, 0, NULL),
(55, 16, 'emp_18', 'EMP', '093 _ 34.76 19.65, =EMP-033___EMP-O17 94.79', NULL, 0, NULL),
(56, 16, 'line_19_19', 'Line 19', 'Grand Total 357.95 201.23 Grand Total 316.11', NULL, 0, NULL),
(57, 17, 'line_1_1', 'Line 1', 'Years (DATE) 2025 a YEAR 2028', NULL, 0, NULL),
(58, 17, 'line_2_2', 'Line 2', '‘Months (OTE) Oct Ei MoH 10 Ei', NULL, 0, NULL),
(59, 17, 'line_3_3', 'Line 3', '[EMPID__ + SumOtTOTALCOMMISSION Sum ot commission ‘employee ié» prepared by ~ Sum ot commission paid', NULL, 0, NULL),
(60, 17, 'mp_4', '=MP', '001 10.22 5.68 REFUND “EMP-001 _EMP-O17 10.29 ec-25 emp 33 talc sales', NULL, 0, NULL),
(61, 17, 'line_5_5', 'Line 5', 'esp.002 9.98 570 =empoo2 _eMP.017 10.01 emp 20 ada zoe, ap tak bayar commission commision bayardekat em 39', NULL, 0, NULL),
(62, 17, 'ep_6', 'ep', '008 40.98 nn SEMP-004ENP.017 4087 emp 32 ada sales, tap tak bayat', NULL, 0, NULL),
(63, 17, '9_7', '9', '005 9.10 2728 p.008 _EMP-017 928 fem 30, sepatuinya commission rm9.72, ap dooat $91.90', NULL, 0, NULL),
(64, 17, 'vp_8', 'vP', '008 34.04 ao7t =ENP-008 ENP.017 3405 S788', NULL, 0, NULL),
(65, 17, 'ep_9', 'eP', '009 476 258 SEMP-009—_EHP.017 478', NULL, 0, NULL),
(66, 17, 'evp_10', 'evp', '022 a6 649 =enpo22  ee-o17 1160 Nov-25 emp 20 dapat commission eb', NULL, 0, NULL),
(67, 17, 'ep_11', 'ep', '023 58.52 27 SENP-02_—EHP.O7 585 oct-25', NULL, 0, NULL),
(68, 17, 'line_12_12', 'Line 12', 'esp.025 560 aos sepa ENP.017 56 cq', NULL, 0, NULL),
(69, 17, 'eup_o27_see_352_enp_13', 'eup.o27 see 352 =ENP', '027 —_EMP-017 59', NULL, 0, NULL),
(70, 17, 'ep_14', 'ep', '028 138 075 sempozs eMP-017 135', NULL, 0, NULL),
(71, 17, 'evp_15', 'evp', '030 seit ast =ENP-090 ENP.-017 17408', NULL, 0, NULL),
(72, 17, 'ep_16', 'eP', '031 1209 eet SEMP.OM_eHP-17 12.02', NULL, 0, NULL),
(73, 17, 'line_17_17', 'Line 17', 'se 032 2228 25.98 enpog2 _eMP.017 __ 42.28', NULL, 0, NULL),
(74, 17, 'line_18_18', 'Line 18', '(rand Totat 36439 20847 ‘Grand Toat 2053.11', NULL, 0, NULL),
(75, 18, 'line_1_1', 'Line 1', 'be and is hereby authorized, for and on behalf of the Company :-', 'be and is hereby authorized, for and on behalf of the Company :-', 1, 2),
(76, 18, 'line_2_2', 'Line 2', 'i. to give order(s) or instruction(s) in writing to AHAM in relation to all transactions entered into or', 'i. to give order(s) or instruction(s) in writing to AHAM in relation to all transactions entered into or', 1, 2),
(77, 18, 'line_3_3', 'Line 3', 'made for the account of the Company;', 'made for the account of the Company;', 1, 2),
(78, 18, 'line_4_4', 'Line 4', 'ii. to direct AHAM to pay into any banking accounts maintained in the name of the Company', 'ii. to direct AHAM to pay into any banking accounts maintained in the name of the Company', 1, 2),
(79, 18, 'line_5_5', 'Line 5', 'provided that the account numbers are provided to AHAM in advance.', 'provided that the account numbers are provided to AHAM in advance.', 1, 2),
(80, 18, 'line_6_6', 'Line 6', 'THAT such instruction contemplated herein may be effected by electronic mail and that AHAM is', 'THAT such instruction contemplated herein may be effected by electronic mail and that AHAM is', 1, 2),
(81, 18, 'line_7_7', 'Line 7', 'fully authorized to accept and give effect to any instructions communicated via electronic mail and', 'fully authorized to accept and give effect to any instructions communicated via electronic mail and', 1, 2),
(82, 18, 'line_8_8', 'Line 8', ', that AHAM is absolved from any blame and consequently to hold AHAM harmless in respect of any', ', that AHAM is absolved from any blame and consequently to hold AHAM harmless in respect of any', 1, 2),
(83, 18, 'line_9_9', 'Line 9', '_/ cost, loss, liability or expenses whatsoever which may be suffered or incurred by the Company as a', '_/ cost, loss, liability or expenses whatsoever which may be suffered or incurred by the Company as a', 1, 2),
(84, 18, 'line_10_10', 'Line 10', 'result of acting on any transactions received via electronic mail and where applicable, to indemnify', 'result of acting on any transactions received via electronic mail and where applicable, to indemnify', 1, 2),
(85, 18, 'line_11_11', 'Line 11', 'AHAM for any cost, loss, liability or expenses whatsoever which may be suffered or incurred by', 'AHAM for any cost, loss, liability or expenses whatsoever which may be suffered or incurred by', 1, 2),
(86, 18, 'line_12_12', 'Line 12', 'AHAN in relying on the said instruction.', 'AHAN in relying on the said instruction.', 1, 2),
(87, 18, 'line_13_13', 'Line 13', 'THAT the above specimen signatures of the authorized signatories be furnished to AHAM and shall', 'THAT the above specimen signatures of the authorized signatories be furnished to AHAM and shall', 1, 2),
(88, 18, 'line_14_14', 'Line 14', 'remain in full force until receipt by AHAM of new resolution passed by the Board.', 'remain in full force until receipt by AHAM of new resolution passed by the Board.', 1, 2),
(89, 18, 'line_15_15', 'Line 15', 'BOARD OF DIRECTO!', 'BOARD OF DIRECTO!', 1, 2),
(90, 18, 'line_16_16', 'Line 16', 'LEEMENG WE! ROSLIZAN BIN NEKMAT', 'LEEMENG WE! ROSLIZAN BIN NEKMAT', 1, 2),
(91, 18, 'date_17', 'Date', '9 8 OCT 2025', '9 8 OCT 2025', 1, 2),
(92, 18, 'line_18_18', 'Line 18', '|', '|', 1, 2),
(93, 19, 'line_1_1', 'Line 1', 'Course Taken', NULL, 0, NULL),
(94, 19, 'line_2_2', 'Line 2', 'ADVANCED FINANCIAL', NULL, 0, NULL),
(95, 19, 'line_3_3', 'Line 3', 'BEE K20aS MANAGEMENT', NULL, 0, NULL),
(96, 19, 'line_4_4', 'Line 4', 'BKATK3023 ADVANCED TAXATION', NULL, 0, NULL),
(97, 19, 'line_5_5', 'Line 5', 'BKAAK2013 ‘AUDITING AND ASSURANCE |', NULL, 0, NULL),
(98, 19, 'line_6_6', 'Line 6', 'DATABASE MANAGEMENT', NULL, 0, NULL),
(99, 19, 'line_7_7', 'Line 7', 'BKASK3053 DATABASE', NULL, 0, NULL),
(100, 19, 'line_8_8', 'Line 8', 'FINANCIAL ACCOUNTING AND', NULL, 0, NULL),
(101, 19, 'line_9_9', 'Line 9', 'BKARKS043 REPORTING IV', NULL, 0, NULL),
(102, 19, 'line_10_10', 'Line 10', 'BKALK3023 PUBLIC SECTOR ACCOUNTING', NULL, 0, NULL),
(103, 19, 'line_11_11', 'Line 11', 'SEMINAR IN MANAGEMENT', NULL, 0, NULL),
(104, 19, 'line_12_12', 'Line 12', 'BICAMia033 [ACCOUNTING', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `attempted_at`) VALUES
(3, 'admin@uhy.docs.hub.com', '::1', 0, '2026-10-08 07:46:50'),
(4, 'admin@uhy.docs.hub.com', '::1', 0, '2026-10-08 07:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `receiver_id` int(10) UNSIGNED NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `read_at`, `created_at`) VALUES
(4, 2, 1, 'hi', 1, '2026-09-30 11:11:20', '2026-09-30 03:10:55'),
(5, 1, 2, 'hi', 1, '2026-09-30 11:11:59', '2026-09-30 03:11:28'),
(6, 1, 2, NULL, 1, '2026-09-30 13:42:02', '2026-09-30 05:41:13'),
(7, 3, 1, 'hi', 1, '2026-10-01 09:36:34', '2026-10-01 01:36:13'),
(8, 2, 1, 'tq', 1, '2026-10-01 09:37:15', '2026-10-01 01:36:57'),
(9, 1, 2, NULL, 1, '2026-10-08 13:27:19', '2026-10-06 07:53:39');

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `attachment_id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `message_attachments`
--

INSERT INTO `message_attachments` (`attachment_id`, `message_id`, `file_name`, `file_path`, `file_type`, `file_size`) VALUES
(1, 6, 'Project Proposal-Muhammad Alysdanish Bin Mohd Faisal.docx', 'documents/2026/09/4232bf2055e2eb814c7cdb5c82cf2d9d.docx', 'docx', 228103),
(2, 9, 'photo_2026-10-06_15-41-48.jpg', 'documents/2026/10/d2f41353aaf160931c46f5c8fd548b6d.jpg', 'jpg', 145500);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` enum('message','task','document','system') NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `reference_type` varchar(30) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `created_at`) VALUES
(4, 2, 'system', 'Welcome to UHY DocumentFlow', 'Your account is ready. Please change your password after your first sign in.', NULL, NULL, 1, '2026-09-28 07:10:00'),
(5, 1, 'message', 'New message', 'You have a new message', 'conversation', 2, 1, '2026-09-30 03:10:55'),
(6, 2, 'message', 'New message', 'You have a new message', 'conversation', 1, 1, '2026-09-30 03:11:28'),
(7, 2, 'message', 'New message', 'You have a new message', 'conversation', 1, 1, '2026-09-30 05:41:13'),
(8, 3, 'system', 'Welcome to UHY DocumentFlow', 'Your account is ready. Please change your password after your first sign in.', NULL, NULL, 0, '2026-10-01 01:34:31'),
(9, 1, 'message', 'New message', 'You have a new message', 'conversation', 3, 1, '2026-10-01 01:36:13'),
(10, 1, 'message', 'New message', 'You have a new message', 'conversation', 2, 1, '2026-10-01 01:36:57'),
(11, 2, 'message', 'New message', 'You have a new message', 'conversation', 1, 0, '2026-10-06 07:53:39');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED DEFAULT NULL,
  `client_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `assigned_to` int(10) UNSIGNED NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `status` enum('new','uploaded','processing','in_progress','pending_review','completed') NOT NULL DEFAULT 'new',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `seen_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `document_id`, `client_id`, `title`, `assigned_to`, `created_by`, `status`, `priority`, `due_date`, `seen_at`, `created_at`, `updated_at`, `completed_at`) VALUES
(6, 2, 2, 'Prepare Other', 1, 1, 'uploaded', 'medium', NULL, '2026-09-28 15:52:02', '2026-09-28 07:52:02', '2026-09-28 07:52:02', NULL),
(7, 3, 2, 'Prepare Other', 1, 1, 'uploaded', 'medium', NULL, '2026-09-29 09:17:10', '2026-09-29 01:17:10', '2026-09-29 01:17:10', NULL),
(8, 4, 3, 'Prepare Directors Information', 1, 1, 'completed', 'medium', NULL, '2026-09-29 11:07:11', '2026-09-29 03:07:11', '2026-10-02 08:39:23', '2026-10-02 16:39:23'),
(9, 5, 3, 'Prepare Bank Details', 1, 1, 'in_progress', 'medium', NULL, '2026-09-29 11:10:31', '2026-09-29 03:10:31', '2026-10-02 08:39:57', NULL),
(10, 6, 2, 'Prepare Company Profile', 1, 1, 'completed', 'medium', NULL, '2026-09-29 11:13:00', '2026-09-29 03:13:00', '2026-10-02 08:38:50', '2026-10-02 16:38:50'),
(11, 7, 2, 'Prepare Other', 2, 2, 'uploaded', 'medium', NULL, '2026-09-30 10:09:35', '2026-09-30 02:09:35', '2026-09-30 02:09:35', NULL),
(12, 8, 2, 'Prepare Bank Details', 2, 2, 'completed', 'medium', NULL, '2026-09-30 10:13:21', '2026-09-30 02:13:21', '2026-09-30 02:48:57', '2026-09-30 10:48:57'),
(13, 9, 2, 'Prepare Other (1)', 1, 1, 'completed', 'medium', NULL, '2026-09-30 15:06:12', '2026-09-30 07:06:12', '2026-09-30 07:09:01', '2026-09-30 15:09:01'),
(14, 10, 2, 'Prepare Other (2)', 1, 1, 'completed', 'medium', NULL, '2026-09-30 15:06:12', '2026-09-30 07:06:12', '2026-10-02 08:38:38', '2026-10-02 16:38:38'),
(15, 11, 2, 'Prepare Other', 1, 1, 'uploaded', 'medium', NULL, '2026-10-06 15:42:07', '2026-10-06 07:42:07', '2026-10-06 07:42:07', NULL),
(16, 12, 2, 'Prepare Bank Details', 2, 2, 'uploaded', 'medium', NULL, '2026-10-08 13:42:42', '2026-10-08 05:42:42', '2026-10-08 05:42:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `staff_type` enum('staff','contract','intern') DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `username`, `email`, `password`, `role`, `staff_type`, `department`, `avatar_path`, `status`, `must_change_password`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin', 'admin@uhy.docs.hub.com', '$2y$10$Tj8lSGPFy/q49l9Qbzs32.9znPDhFUH3OGsCUMhK0wRmCx.lFFqGO', 'admin', NULL, 'Administration', 'avatars/c0c608eb6041f67139ee5f63132a0a9e.jpg', 'active', 0, '2026-10-08 14:12:09', '2026-09-28 06:20:58', '2026-10-08 06:12:09'),
(2, 'Nurul Izza', 'nurul', 'nurul@uhy.docs.hub.com', '$2y$10$ScO8Ngl2wku9izgYpaS0Hu3MzlTLZWhy/K3Lw0W90krOiYaXlGcVa', 'staff', 'staff', 'Audit', 'avatars/f289e3715a3c27e56488f171d3092bb3.jpg', 'active', 0, '2026-10-08 13:38:42', '2026-09-28 07:10:00', '2026-10-08 05:38:42'),
(3, 'Alysdanish', 'alysdanish', 'alysdanish@uhy.docs.hub.com', '$2y$10$9hOt8YJTDi3xkjstTd4DAuElScu8erGesNune9v7W4qqF0rYlBer.', 'staff', 'intern', 'Audit', NULL, 'active', 0, '2026-10-01 09:34:54', '2026-10-01 01:34:31', '2026-10-01 01:35:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `idx_clients_name` (`client_name`),
  ADD KEY `fk_clients_created_by` (`created_by`);

--
-- Indexes for table `community_attachments`
--
ALTER TABLE `community_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `fk_com_att_post` (`post_id`);

--
-- Indexes for table `community_channels`
--
ALTER TABLE `community_channels`
  ADD PRIMARY KEY (`channel_id`),
  ADD UNIQUE KEY `uq_channel_name` (`name`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `idx_posts_channel` (`channel_id`,`post_id`),
  ADD KEY `fk_posts_user` (`user_id`),
  ADD KEY `fk_posts_parent` (`parent_post_id`);

--
-- Indexes for table `community_reads`
--
ALTER TABLE `community_reads`
  ADD PRIMARY KEY (`user_id`,`channel_id`),
  ADD KEY `fk_reads_channel` (`channel_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `idx_documents_client_year` (`client_id`,`year`),
  ADD KEY `idx_documents_status` (`status`),
  ADD KEY `fk_documents_type` (`type_id`),
  ADD KEY `fk_documents_user` (`uploaded_by`),
  ADD KEY `fk_documents_current_version` (`current_version_id`),
  ADD KEY `fk_documents_prepared_from` (`prepared_from_version_id`),
  ADD KEY `idx_documents_deleted` (`is_deleted`),
  ADD KEY `fk_documents_deleted_by` (`deleted_by`);

--
-- Indexes for table `document_changes`
--
ALTER TABLE `document_changes`
  ADD PRIMARY KEY (`change_id`),
  ADD KEY `idx_changes_document` (`document_id`),
  ADD KEY `fk_changes_user` (`reviewed_by`);

--
-- Indexes for table `document_extractions`
--
ALTER TABLE `document_extractions`
  ADD PRIMARY KEY (`extraction_id`),
  ADD KEY `idx_extractions_version` (`version_id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD KEY `fk_templates_type` (`type_id`),
  ADD KEY `fk_templates_user` (`created_by`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `uq_document_types_name` (`name`);

--
-- Indexes for table `document_type_fields`
--
ALTER TABLE `document_type_fields`
  ADD PRIMARY KEY (`type_field_id`),
  ADD UNIQUE KEY `uq_type_field` (`type_id`,`field_key`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`version_id`),
  ADD UNIQUE KEY `uq_document_version` (`document_id`,`version_no`),
  ADD KEY `fk_versions_user` (`created_by`);

--
-- Indexes for table `extraction_fields`
--
ALTER TABLE `extraction_fields`
  ADD PRIMARY KEY (`field_id`),
  ADD KEY `idx_fields_extraction` (`extraction_id`),
  ADD KEY `fk_fields_user` (`confirmed_by`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempts_email_time` (`email`,`attempted_at`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_messages_receiver_unread` (`receiver_id`,`is_read`),
  ADD KEY `idx_messages_conversation` (`sender_id`,`receiver_id`,`created_at`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `fk_msg_att_message` (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notif_user_unread` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `idx_tasks_assignee` (`assigned_to`,`status`),
  ADD KEY `idx_tasks_due` (`due_date`),
  ADD KEY `fk_tasks_document` (`document_id`),
  ADD KEY `fk_tasks_client` (`client_id`),
  ADD KEY `fk_tasks_creator` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `community_attachments`
--
ALTER TABLE `community_attachments`
  MODIFY `attachment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_channels`
--
ALTER TABLE `community_channels`
  MODIFY `channel_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `post_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `document_changes`
--
ALTER TABLE `document_changes`
  MODIFY `change_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_extractions`
--
ALTER TABLE `document_extractions`
  MODIFY `extraction_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `template_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `document_type_fields`
--
ALTER TABLE `document_type_fields`
  MODIFY `type_field_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `version_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `extraction_fields`
--
ALTER TABLE `extraction_fields`
  MODIFY `field_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `attachment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `fk_clients_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `community_attachments`
--
ALTER TABLE `community_attachments`
  ADD CONSTRAINT `fk_com_att_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD CONSTRAINT `fk_posts_channel` FOREIGN KEY (`channel_id`) REFERENCES `community_channels` (`channel_id`),
  ADD CONSTRAINT `fk_posts_parent` FOREIGN KEY (`parent_post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `community_reads`
--
ALTER TABLE `community_reads`
  ADD CONSTRAINT `fk_reads_channel` FOREIGN KEY (`channel_id`) REFERENCES `community_channels` (`channel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `fk_documents_current_version` FOREIGN KEY (`current_version_id`) REFERENCES `document_versions` (`version_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_documents_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_documents_prepared_from` FOREIGN KEY (`prepared_from_version_id`) REFERENCES `document_versions` (`version_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_documents_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`),
  ADD CONSTRAINT `fk_documents_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `document_changes`
--
ALTER TABLE `document_changes`
  ADD CONSTRAINT `fk_changes_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_changes_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `document_extractions`
--
ALTER TABLE `document_extractions`
  ADD CONSTRAINT `fk_extractions_version` FOREIGN KEY (`version_id`) REFERENCES `document_versions` (`version_id`) ON DELETE CASCADE;

--
-- Constraints for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD CONSTRAINT `fk_templates_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`),
  ADD CONSTRAINT `fk_templates_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `document_type_fields`
--
ALTER TABLE `document_type_fields`
  ADD CONSTRAINT `fk_dtf_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON DELETE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `fk_versions_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_versions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `extraction_fields`
--
ALTER TABLE `extraction_fields`
  ADD CONSTRAINT `fk_fields_extraction` FOREIGN KEY (`extraction_id`) REFERENCES `document_extractions` (`extraction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fields_user` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `fk_msg_att_message` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_tasks_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_tasks_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
