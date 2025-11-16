-- --------------------------------------------------------
-- Host:                         w01e3b67.kasserver.com
-- Server-Version:               10.11.13-MariaDB-0ubuntu0.24.04.1-log - Ubuntu 24.04
-- Server-Betriebssystem:        debian-linux-gnu
-- HeidiSQL Version:             12.4.0.6659
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Exportiere Struktur von Tabelle d044f149.active_users
CREATE TABLE IF NOT EXISTS `active_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `last_activity` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_page` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.active_users: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.activity_log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `marker_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_activity_log_user_id` (`user_id`),
  KEY `idx_activity_log_username` (`username`),
  KEY `idx_activity_log_action` (`action`),
  KEY `idx_activity_log_created_at` (`created_at`),
  KEY `idx_activity_log_marker_id` (`marker_id`),
  KEY `idx_activity_log_ip_address` (`ip_address`),
  KEY `idx_activity_log_composite` (`user_id`,`created_at`),
  KEY `idx_activity_log_action_date` (`action`,`created_at`),
  KEY `idx_activity_log_user_action` (`user_id`,`action`,`created_at`),
  KEY `idx_activity_user_created` (`user_id`,`created_at`),
  KEY `idx_activity_action` (`action`),
  KEY `idx_activity_created` (`created_at`),
  KEY `idx_activity_user_date` (`user_id`,`created_at`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activity_log_ibfk_2` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=467 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.activity_log: ~429 rows (ungefähr)
INSERT INTO `activity_log` (`id`, `user_id`, `username`, `action`, `details`, `marker_id`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.112.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-07 21:52:01'),
	(2, 1, 'admin', 'qr_codes_generated', 'Batch \'BATCH_2025-10-07_215820\': 10 QR-Codes erstellt, 0 übersprungen', NULL, '109.43.112.168', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-07 21:58:20'),
	(3, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(4, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(5, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(6, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(7, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(8, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(9, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(10, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(11, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(12, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:04'),
	(13, 1, 'admin', 'auto_login', 'Auto-Login via Remember Me', NULL, '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2025-10-07 23:48:07'),
	(14, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '185.113.148.103', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-08 08:30:21'),
	(15, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 09:22:04'),
	(16, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 11:54:48'),
	(17, 1, 'admin', 'marker_created', 'Marker \'Test Gerät\' erstellt mit QR-Code \'QR-0001\'', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 11:56:00'),
	(18, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.102.95', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.2', '2025-10-08 12:18:50'),
	(19, 1, 'admin', 'marker_deleted_soft', 'Marker \'Test Gerät\' in Papierkorb verschoben', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 12:20:19'),
	(20, 1, 'admin', 'marker_created', 'Marker \'Test Gerät\' erstellt mit QR-Code \'QR-0001\'', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 12:21:42'),
	(21, 1, 'admin', 'qr_activated', 'QR-Code \'QR-0001\' aktiviert durch GPS-Update', NULL, '80.187.102.95', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.2', '2025-10-08 12:22:42'),
	(22, 1, 'admin', 'marker_updated', 'Marker \'Test Gerät\' aktualisiert', NULL, '80.187.102.95', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.2', '2025-10-08 12:22:42'),
	(23, 1, 'admin', 'bug_report', 'Bug gemeldet: QR-Code Print aktuell fix aber nicht die richtige version', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 12:25:43'),
	(24, 1, 'admin', 'bug_report', 'Bug gemeldet: Marker DGUV und co', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 12:26:17'),
	(25, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 16:44:26'),
	(26, 1, 'admin', 'bug_report', 'Bug gemeldet: Prüfungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 16:46:40'),
	(27, 1, 'admin', 'bug_report', 'Bug gemeldet: Meine Bug-Tickets', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 16:49:29'),
	(28, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 19:55:23'),
	(29, 1, 'admin', 'marker_deleted_soft', 'Marker \'Test Gerät\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 20:31:06'),
	(30, 1, 'admin', 'marker_deleted_soft', 'Marker \'Test Gerät\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 20:31:20'),
	(31, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 15 freigegeben', NULL, NULL, NULL, '2025-10-08 20:53:23'),
	(32, 1, 'admin', 'marker_deleted_soft', 'Marker \'Test Gerät\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 20:53:23'),
	(33, 1, 'admin', 'marker_restored', 'Marker aus Papierkorb wiederhergestellt', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 20:54:20'),
	(34, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 15 freigegeben', NULL, NULL, NULL, '2025-10-08 20:54:28'),
	(35, 1, 'admin', 'marker_deleted_soft', 'Marker \'Test Gerät\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-08 20:54:28'),
	(37, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-09 08:34:26'),
	(38, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-09 20:08:10'),
	(39, 1, 'admin', 'marker_created', 'Lagergerät \'GX74K-V\' erstellt mit QR-Code \'QR-0001\'', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-09 20:08:50'),
	(40, 1, 'admin', 'qr_activated', 'QR-Code \'QR-0001\' aktiviert durch GPS-Update', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-09 20:59:13'),
	(41, 1, 'admin', 'marker_updated', 'Marker \'GX74K-V\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-09 20:59:13'),
	(42, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 16 freigegeben', NULL, NULL, NULL, '2025-10-09 21:43:09'),
	(43, 1, 'admin', 'marker_deleted_soft', 'Lagergerät \'GX74K-V\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-09 21:43:09'),
	(45, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-10 20:00:48'),
	(46, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-10 21:36:11'),
	(47, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-11 07:04:26'),
	(48, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-11 07:15:56'),
	(49, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-11 20:13:37'),
	(50, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:19:54'),
	(51, 1, 'admin', 'marker_created', 'Lagergerät \'dsfhgsdf\' erstellt mit QR-Code \'QR-0001\'', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:27:30'),
	(52, 1, 'admin', 'qr_activated', 'QR-Code \'QR-0001\' aktiviert durch GPS-Update', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:35:40'),
	(53, 1, 'admin', 'marker_updated', 'Marker \'dsfhgsdf\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:35:40'),
	(54, 1, 'admin', 'marker_created', 'Lagergerät \'fgjrtfdg\' erstellt mit QR-Code \'QR-0002\'', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:40:33'),
	(55, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 18 freigegeben', NULL, NULL, NULL, '2025-10-12 06:45:21'),
	(56, 1, 'admin', 'marker_deleted_soft', 'Lagergerät \'fgjrtfdg\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:45:21'),
	(58, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 17 freigegeben', NULL, NULL, NULL, '2025-10-12 06:58:02'),
	(59, 1, 'admin', 'marker_deleted_soft', 'Lagergerät \'dsfhgsdf\' in Papierkorb verschoben', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:58:02'),
	(61, 1, 'admin', 'marker_created', 'Gerät \'rtzhe\' erstellt mit QR-Code \'QR-0001\'', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:58:22'),
	(62, 1, 'admin', 'qr_activated', 'QR-Code \'QR-0001\' aktiviert durch GPS-Update', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:58:44'),
	(63, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 06:58:44'),
	(64, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-12 07:04:00'),
	(65, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 11:16:44'),
	(66, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 11:21:07'),
	(67, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-12 11:21:46'),
	(68, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-12 11:28:47'),
	(69, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 12:53:42'),
	(70, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 18:03:03'),
	(71, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 18:03:34'),
	(72, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 18:05:59'),
	(73, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-12 18:08:13'),
	(74, 1, 'admin', 'qr_branding_added', 'Logo \'Logo\' hochgeladen', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 18:10:49'),
	(75, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-12 18:18:40'),
	(76, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:17:16'),
	(77, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:18:56'),
	(78, 1, 'admin', 'status_changed', 'Status geändert: Verfügbar → Vermietet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:18:56'),
	(79, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:19:22'),
	(80, 1, 'admin', 'status_changed', 'Status geändert: Vermietet → Verfügbar', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:19:22'),
	(81, 1, 'admin', 'inspection_added', 'Prüfung \'DGUV\' für \'rtzhe\' hinzugefügt', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:20:36'),
	(82, 1, 'admin', 'checklist_template_created', 'Checklisten-Template \'GX74K-V\' erstellt', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 20:23:19'),
	(83, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 21:07:16'),
	(84, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 21:07:49'),
	(85, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 21:15:10'),
	(86, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 21:20:48'),
	(87, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-12 21:29:50'),
	(88, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-13 06:05:24'),
	(89, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-13 19:59:48'),
	(90, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 06:17:30'),
	(91, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 07:34:43'),
	(92, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 12:09:06'),
	(93, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-14 12:40:02'),
	(94, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-14 16:23:40'),
	(95, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-14 16:24:39'),
	(96, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-14 16:44:30'),
	(97, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-14 16:44:54'),
	(98, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-15 09:44:18'),
	(99, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-15 09:45:05'),
	(100, 1, 'admin', 'marker_finished', 'Gerät \'rtzhe\' als fertig markiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-15 09:45:31'),
	(101, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 12:02:45'),
	(102, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0 (Edition std-1)', '2025-10-15 21:28:31'),
	(103, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-16 07:44:20'),
	(104, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-16 09:05:10'),
	(105, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-16 12:16:23'),
	(106, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-16 12:19:25'),
	(107, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-16 12:27:51'),
	(108, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.71.31', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-16 12:41:51'),
	(109, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-16 16:10:53'),
	(113, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-17 05:48:43'),
	(114, 1, 'admin', 'maintenance_added', 'Wartung durchgeführt', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-17 05:49:50'),
	(115, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-17 06:30:47'),
	(116, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-17 14:35:00'),
	(117, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-17 16:11:24'),
	(118, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-17 19:50:47'),
	(119, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.111', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-17 20:36:47'),
	(120, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-19 11:45:21'),
	(121, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 06:16:14'),
	(122, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 06:16:22'),
	(125, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 06:21:39'),
	(127, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 11:06:59'),
	(130, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.69.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 12:30:48'),
	(132, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.69.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 12:36:41'),
	(133, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.69.220', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-20 12:48:58'),
	(134, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 12:49:53'),
	(136, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 12:56:36'),
	(137, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 14:01:33'),
	(138, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 14:02:48'),
	(139, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.69.220', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-20 14:04:43'),
	(140, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 15:21:24'),
	(142, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.69.220', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-20 15:25:19'),
	(143, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.187.69.220', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-20 15:26:37'),
	(144, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.187.69.220', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-20 15:27:03'),
	(145, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 16:22:21'),
	(148, 1, 'admin', 'bug_report', 'Bug gemeldet: Verbesserungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 16:27:14'),
	(149, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 16:29:58'),
	(150, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 20:31:44'),
	(153, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 20:40:34'),
	(154, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.45', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-20 21:02:42'),
	(155, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 06:16:12'),
	(156, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 06:24:42'),
	(159, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.69.220', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 06:52:27'),
	(160, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 10:03:00'),
	(161, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 10:05:01'),
	(162, 1, 'admin', 'status_changed', 'Status geändert: Vermietet → Verfügbar', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 10:05:01'),
	(163, 1, 'admin', 'marker_unfinished', 'Fertig-Status von Gerät \'rtzhe\' zurückgesetzt', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 10:05:10'),
	(164, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 10:05:48'),
	(165, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 11:12:18'),
	(166, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 11:19:05'),
	(167, 1, 'admin', 'bug_report', 'Bug gemeldet: https://bgg-objekt.de/delete_inspection.php?id=1&marker_id=19', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 11:44:47'),
	(168, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 16:00:57'),
	(169, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 17:53:26'),
	(170, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/141.0.3537.72 Version/26.0 Mobile/15E148 Safari/604.1', '2025-10-21 17:57:22'),
	(171, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:08:21'),
	(172, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:11:33'),
	(176, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-BATCH-2025-10-21\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:23:13'),
	(177, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 20:23:50'),
	(178, 1, 'admin', 'nfc_chip_deleted', 'NFC-Chip \'04:39:A6:65:4E:61:80\' gelöscht', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:28:35'),
	(179, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:31:54'),
	(180, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-BATCH-2025-10-21\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:32:28'),
	(181, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 20:32:46'),
	(183, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-BATCH-2025-10-21\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:37:24'),
	(185, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-BATCH-2025-10-21\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 20:39:13'),
	(187, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 21:05:36'),
	(188, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/141.0.3537.72 Version/26.0 Mobile/15E148 Safari/604.1', '2025-10-21 21:06:36'),
	(189, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-10-21 21:11:21'),
	(190, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 21:15:16'),
	(191, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-21 21:19:50'),
	(192, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-21 21:21:06'),
	(193, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 06:14:55'),
	(194, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-22 06:21:38'),
	(195, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-22 06:23:12'),
	(196, 1, 'admin', 'inspection_deleted', 'Prüfung \'DGUV\' für \'rtzhe\' gelöscht', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 06:37:58'),
	(197, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 07:43:15'),
	(204, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 10:19:17'),
	(205, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 11:58:02'),
	(206, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 12:03:29'),
	(207, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 14:29:57'),
	(208, 1, 'admin', 'bug_report', 'Bug gemeldet: Messe', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 14:36:01'),
	(209, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 20:25:11'),
	(212, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-22 20:38:40'),
	(213, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 21:01:26'),
	(214, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-22 21:28:19'),
	(215, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-22 21:29:40'),
	(216, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-22 21:41:58'),
	(217, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 06:02:13'),
	(218, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 06:17:52'),
	(219, 1, 'admin', 'messe_ended', 'Messe ID 5 beendet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 06:39:22'),
	(220, 1, 'admin', 'messe_activated', 'Messe ID 5 aktiviert', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 06:39:25'),
	(221, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 07:52:55'),
	(222, 1, 'admin', 'marker_updated', 'Marker \'rtzhe\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 07:53:21'),
	(223, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 09:19:43'),
	(224, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 10:29:19'),
	(225, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 13:19:12'),
	(226, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 14:07:59'),
	(229, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 14:13:27'),
	(230, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 19:49:56'),
	(231, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-23 19:54:50'),
	(232, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 21:05:39'),
	(233, 1, 'admin', 'messe_badge_add', 'Badge \'Test\' hinzugefügt für Messe-Marker ID: 7', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-23 21:06:27'),
	(234, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 12:36:13'),
	(235, 1, 'admin', 'messe_badge_delete', 'Badge gelöscht (ID: 1)', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 12:59:46'),
	(236, 1, 'admin', 'messe_badge_delete', 'Badge gelöscht (ID: 2)', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 12:59:54'),
	(237, 1, 'admin', 'messe_badge_delete', 'Badge gelöscht (ID: 3)', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 13:00:03'),
	(238, 1, 'admin', 'messe_badge_delete', 'Badge gelöscht (ID: 4)', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 13:00:10'),
	(239, 1, 'admin', 'messe_badge_add', 'Badge \'Lichtleistung: 460W LED\' hinzugefügt für Messe-Marker ID: 7', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 13:03:05'),
	(240, 1, 'admin', 'messe_badge_add', 'Badge \'Antrieb: Diesel – wassergekühlt-1500rpm\' hinzugefügt für Messe-Marker ID: 7', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 13:03:48'),
	(241, 1, 'admin', 'messe_badge_add', 'Badge \'Mastsystem: 9 Meter – Mehrsegmentmast – hydraulisch\' hinzugefügt für Messe-Marker ID: 7', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 13:04:50'),
	(242, 1, 'admin', 'messe_device_image_upload', 'Gerätebild hochgeladen für Messe-Marker ID: 7', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 13:04:57'),
	(243, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 19:50:54'),
	(244, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-24 20:40:10'),
	(245, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 06:40:52'),
	(246, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 06:44:16'),
	(247, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 12:05:07'),
	(248, 1, 'admin', 'checklist_created', 'Checkliste \'Test Check\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 12:36:03'),
	(249, 1, 'admin', 'checklist_created', 'Checkliste \'fdsgedf\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 12:41:39'),
	(250, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 19 freigegeben', NULL, NULL, NULL, '2025-10-25 13:13:08'),
	(251, 1, 'admin', 'marker_deleted_soft', 'Kundengerät \'rtzhe\' in Papierkorb verschoben', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:13:09'),
	(252, 1, 'admin', 'marker_deleted_permanent', 'Marker \'rtzhe\' endgültig gelöscht', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:13:13'),
	(253, 1, 'admin', '1', 'geofence_deleted', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:13:21'),
	(254, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:16:37'),
	(255, 1, 'admin', 'marker_created', 'Lagergerät \'dfdf\' erstellt mit QR-Code \'QR-0001\'', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:24:43'),
	(256, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 24 freigegeben', NULL, NULL, NULL, '2025-10-25 13:25:12'),
	(257, 1, 'admin', 'marker_deleted_soft', 'Lagergerät \'dfdf\' in Papierkorb verschoben', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:25:12'),
	(258, 1, 'admin', 'marker_deleted_permanent', 'Marker \'dfdf\' endgültig gelöscht', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:25:20'),
	(259, 1, 'admin', 'marker_created', 'Kundengerät \'dfghfgd\' erstellt mit QR-Code \'QR-0001\'', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 13:30:03'),
	(260, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-25 13:30:55'),
	(261, 1, 'admin', 'qr_activated', 'QR-Code \'QR-0001\' aktiviert durch GPS-Update', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-25 13:31:13'),
	(262, 1, 'admin', 'marker_updated', 'Marker \'dfghfgd\' aktualisiert', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-25 13:31:13'),
	(263, 1, 'admin', 'status_changed', 'Status geändert: Kein Status → Wartung', NULL, '109.43.113.193', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-10-25 13:31:13'),
	(264, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:03:03'),
	(265, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-25 20:45:05'),
	(266, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 05:07:13'),
	(267, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 06:07:34'),
	(268, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 08:36:35'),
	(269, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 08:50:00'),
	(270, 1, 'admin', 'messe_deleted', 'Messe \'Test Messe\' gelöscht', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 08:52:45'),
	(271, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 20:03:24'),
	(272, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-26 20:59:28'),
	(273, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 06:02:28'),
	(274, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 12:03:03'),
	(275, 1, 'admin', 'checklist_created', 'Checkliste \'Wartungscheckliste Elektrotechnischer Anlagenteil\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 12:10:16'),
	(276, 1, 'admin', 'user_created', 'Benutzer \'mrapp\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 12:34:57'),
	(277, 1, 'admin', 'user_updated', 'Benutzer \'mrapp\' aktualisiert', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 12:35:53'),
	(278, 1, 'admin', 'user_created', 'Benutzer \'mrapp\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 12:50:28'),
	(279, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 13:11:00'),
	(280, 1, 'admin', 'user_created', 'Benutzer \'mkleesattel\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 13:12:21'),
	(281, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 14:05:34'),
	(282, 1, 'admin', 'user_created', 'Benutzer \'marapp\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 14:05:54'),
	(283, 1, 'admin', 'user_created', 'Benutzer \'sofcarek\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 14:10:09'),
	(284, 1, 'admin', 'user_created', 'Benutzer \'rweber\' erstellt', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 14:11:06'),
	(285, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 14:17:20'),
	(286, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.113.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-27 20:00:36'),
	(287, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 06:22:17'),
	(288, 1, 'admin', 'maintenance_started', 'Wartung für Marker \'dfghfgd\' gestartet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 06:30:44'),
	(289, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 07:54:56'),
	(290, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-28 11:50:05'),
	(291, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 10:54:59'),
	(292, 1, 'admin', 'maintenance_started', 'Wartung für Marker \'dfghfgd\' gestartet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 11:21:19'),
	(293, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 12:07:28'),
	(294, 1, 'admin', 'maintenance_started', 'Wartung für Marker \'dfghfgd\' gestartet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 12:07:37'),
	(295, 1, 'admin', 'maintenance_started', 'Wartung für Marker \'dfghfgd\' gestartet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 12:21:19'),
	(296, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 19:32:27'),
	(297, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-29 21:33:21'),
	(298, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:03:35'),
	(299, 1, 'admin', 'checklist_updated', 'Checkliste \'Test Protokoll SEA Intern\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:17:12'),
	(300, 1, 'admin', 'checklist_updated', 'Checkliste \'Wartungscheckliste Elektrotechnischer Anlagenteil\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:17:19'),
	(301, 1, 'admin', 'checklist_updated', 'Checkliste \'Prüfprotokoll für mobile Stromerzeuger DGUV V3\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:17:38'),
	(302, 1, 'admin', 'checklist_updated', 'Checkliste \'Wartungscheckliste motortechnischer Anlagenteil\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:17:46'),
	(303, 1, 'admin', 'checklist_updated', 'Checkliste \'Test Protokoll AUSGANG\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:17:53'),
	(304, 1, 'admin', 'checklist_updated', 'Checkliste \'Container Test Protokoll\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:18:00'),
	(305, 1, 'admin', 'checklist_updated', 'Checkliste \'Test Protokoll Fahrgestelle\' aktualisiert', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 06:26:10'),
	(306, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 13:32:09'),
	(307, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 06:42:14'),
	(308, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 09:54:58'),
	(309, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 11:58:13'),
	(310, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 11:59:07'),
	(311, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 20:08:46'),
	(312, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 13:23:13'),
	(313, 1, 'admin', 'messe_created', 'Messe \'Test\' erstellt (ID: 6)', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 13:23:34'),
	(314, 1, 'admin', 'messe_activated', 'Messe ID 6 aktiviert', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 13:23:44'),
	(315, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-01 13:46:23'),
	(316, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-02 07:01:22'),
	(317, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-02 08:32:30'),
	(318, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-02 12:43:49'),
	(319, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.116', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-02 20:46:12'),
	(320, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-11-03 07:55:13'),
	(321, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 10:53:36'),
	(322, 1, 'admin', 'user_created', 'Benutzer \'testname\' erstellt', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 10:58:31'),
	(323, 10, 'testname', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 10:58:41'),
	(324, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 11:57:16'),
	(325, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:11:46'),
	(326, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:11:55'),
	(327, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:00'),
	(328, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:04'),
	(329, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:09'),
	(330, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:12'),
	(331, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:16'),
	(332, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:19'),
	(333, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:12:46'),
	(334, 1, 'admin', 'marker_updated', 'Marker \'dfghfgd\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:18:36'),
	(335, 1, 'admin', 'marker_updated', 'Marker \'dfghfgd\' aktualisiert', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 12:19:52'),
	(336, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-11-03 15:10:09'),
	(337, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-11-03 15:10:27'),
	(338, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-03 15:45:11'),
	(339, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-04 05:58:22'),
	(340, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-04 08:05:11'),
	(341, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:03:25'),
	(342, 1, 'admin', 'user_updated', 'Benutzer \'testname\' aktualisiert', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:04:16'),
	(343, 10, 'testname', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:04:30'),
	(344, 10, 'testname', 'password_changed', 'Pflicht-Passwortänderung durchgeführt', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:08:10'),
	(345, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:08:21'),
	(346, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:33:12'),
	(347, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-11-04 16:40:18'),
	(348, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '85.13.129.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-04 22:19:30'),
	(349, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 12:46:26'),
	(350, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 21:14:38'),
	(351, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 21:53:40'),
	(352, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 06:00:58'),
	(353, 1, 'admin', 'marker_updated', 'Marker \'dfghfgd\' aktualisiert', NULL, '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 06:14:05'),
	(354, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 07:44:13'),
	(355, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 11:20:36'),
	(356, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 12:12:01'),
	(357, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:45:19'),
	(358, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 25 freigegeben', NULL, NULL, NULL, '2025-11-06 15:45:42'),
	(359, 1, 'admin', 'marker_deleted_soft', 'Kundengerät \'dfghfgd\' in Papierkorb verschoben', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:45:42'),
	(360, 1, 'admin', 'marker_deleted_permanent', 'Marker \'dfghfgd\' endgültig gelöscht', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:45:45'),
	(361, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-07 05:56:18'),
	(362, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-07 07:47:04'),
	(363, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 12:07:50'),
	(364, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:20:09'),
	(365, 1, 'admin', 'nfc_chip_deleted', 'NFC-Chip \'04:FC:9F:65:4E:61:80\' gelöscht (inkl. Marker)', NULL, '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:20:43'),
	(366, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-1\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:21:59'),
	(367, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-2\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:25:35'),
	(368, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:42:05'),
	(369, 1, 'admin', 'marker_created', 'Lagergerät \'NFC Test\' erstellt mit NFC-Chip \'04:F9:DA:73:3E:61:80\'', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:42:32'),
	(370, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 15:43:44'),
	(371, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:18:35'),
	(372, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-3\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:19:06'),
	(373, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-4\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:20:43'),
	(374, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-5\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:21:11'),
	(375, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-6\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:21:36'),
	(376, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-7\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:22:02'),
	(377, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-8\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:22:29'),
	(378, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-9\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:22:58'),
	(379, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-10\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:23:22'),
	(380, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-11\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:23:48'),
	(381, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-12\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:24:13'),
	(382, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-13\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:24:38'),
	(383, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-14\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:25:02'),
	(384, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-15\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:25:26'),
	(385, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-16\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:25:48'),
	(386, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-17\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:26:12'),
	(387, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-18\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:26:38'),
	(388, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-19\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:27:00'),
	(389, 1, 'admin', 'nfc_chips_added', 'Batch \'NFC-20\': 1 NFC-Chips hinzugefügt, 0 übersprungen', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:27:26'),
	(390, 1, 'admin', 'marker_created', 'Lagergerät \'QR-Code Test\' erstellt mit QR-Code \'QR-0001\'', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:43:32'),
	(391, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 16:44:01'),
	(392, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 19:53:33'),
	(393, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:47:30'),
	(394, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 26 freigegeben', NULL, NULL, NULL, '2025-11-10 20:50:04'),
	(395, 1, 'admin', 'marker_deleted_soft', 'Lagergerät \'NFC Test\' in Papierkorb verschoben', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:50:04'),
	(396, 1, 'admin', 'marker_deleted_permanent', 'Marker \'NFC Test\' endgültig gelöscht', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:50:07'),
	(397, 1, 'admin', 'marker_created', 'Kundengerät \'NFC Test\' erstellt mit NFC-Chip \'04:F9:DA:73:3E:61:80\'', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:50:37'),
	(398, NULL, NULL, 'qr_activated', 'NFC-Chip \'04:F9:DA:73:3E:61:80\' aktiviert beim ersten Scan', NULL, '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 20:51:22'),
	(399, 1, 'admin', 'marker_created', 'Kundengerät \'Test QR\' erstellt mit QR-Code \'QR-0002\'', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:55:20'),
	(400, 1, 'admin', 'marker_created', 'Kundengerät \'NFC Test\' erstellt mit NFC-Chip \'04:F9:DA:73:3E:61:80\'', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:56:52'),
	(401, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 30 freigegeben', NULL, NULL, NULL, '2025-11-10 21:05:31'),
	(402, 1, 'admin', 'marker_deleted_soft', 'Kundengerät \'NFC Test\' in Papierkorb verschoben', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:05:31'),
	(403, 1, 'admin', 'marker_deleted_permanent', 'Marker \'NFC Test\' endgültig gelöscht', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:05:34'),
	(404, 1, 'admin', 'qr_code_released', 'QR-Code für Marker ID 29 freigegeben', NULL, NULL, NULL, '2025-11-10 21:22:21'),
	(405, 1, 'admin', 'marker_deleted_soft', 'Kundengerät \'Test QR\' in Papierkorb verschoben', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:22:21'),
	(406, 1, 'admin', 'marker_deleted_permanent', 'Marker \'Test QR\' endgültig gelöscht', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:22:24'),
	(407, 1, 'admin', 'marker_created', 'Kundengerät \'Test QR\' erstellt mit QR-Code \'QR-0001\'', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:22:43'),
	(408, 1, 'admin', 'marker_created', 'Kundengerät \'NFC Test\' erstellt mit NFC-Chip \'04:F9:DA:73:3E:61:80\'', 32, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:23:20'),
	(409, NULL, NULL, 'qr_activated', 'NFC-Chip \'04:F9:DA:73:3E:61:80\' aktiviert beim ersten Scan', 32, '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 21:23:36'),
	(410, 1, 'admin', 'qr_activated', 'QR-Code \'QR-0001\' aktiviert durch GPS-Update', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:27:52'),
	(411, 1, 'admin', 'marker_updated', 'Marker \'Test QR\' aktualisiert', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:27:52'),
	(412, 1, 'admin', 'status_changed', 'Status geändert: Kein Status → Wartung', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:27:52'),
	(413, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:51:26'),
	(414, 1, 'admin', 'marker_updated', 'Marker \'Test QR\' aktualisiert', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:52:11'),
	(415, 1, 'admin', 'maintenance_added', 'Wartung durchgeführt', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:52:34'),
	(416, 1, 'admin', 'marker_updated', 'Marker \'Test QR\' aktualisiert', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:52:47'),
	(417, 1, 'admin', 'status_changed', 'Status geändert: Wartung → Verfügbar', 31, '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:52:47'),
	(418, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 50.0470304143, 8.97332573291 (Genauigkeit: 9.54m) via NFC', 32, '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 21:53:26'),
	(419, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 06:09:28'),
	(420, 1, 'admin', 'marker_updated', 'Marker \'NFC Test\' aktualisiert', 32, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 06:10:18'),
	(421, 1, 'admin', 'status_changed', 'Status geändert: Kein Status → Verfügbar', 32, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 06:10:18'),
	(422, 1, 'admin', 'marker_updated', 'Marker \'Test QR\' aktualisiert', 31, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 06:30:37'),
	(423, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 09:50:41'),
	(424, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 09:51:10'),
	(425, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 09:52:19'),
	(426, 1, 'admin', 'marker_updated', 'Marker \'NFC Test\' aktualisiert', 32, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 09:52:42'),
	(427, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.9941193777, 9.07231930645 (Genauigkeit: 5m) via NFC', 32, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 12:38:13'),
	(428, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.99410422, 9.07229467155 (Genauigkeit: 5.96m) via NFC', 32, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 12:38:27'),
	(429, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.994079118, 9.0722767067 (Genauigkeit: 5.8m) via NFC', 32, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 12:42:40'),
	(430, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 12:43:09'),
	(431, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.9940832664, 9.07228410127 (Genauigkeit: 5m) via NFC', 32, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 12:43:29'),
	(432, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.9940518386, 9.07270800939 (Genauigkeit: 9.9m) via NFC', 32, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 12:53:47'),
	(433, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.9941566965, 9.07286032447 (Genauigkeit: 6.07m) via NFC', 32, '80.151.166.21', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', '2025-11-11 12:56:09'),
	(434, NULL, NULL, 'gps_captured', 'GPS-Position erfasst: 49.9942572857, 9.07257725732 (Genauigkeit: 5.31m) via NFC', 32, '80.151.166.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/141 Version/16.0 Safari/605.1.15', '2025-11-11 13:48:28'),
	(435, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 16:08:44'),
	(436, 1, 'admin', '1', 'custom_field_added', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 16:10:47'),
	(437, 1, 'admin', '1', 'custom_field_deleted', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 16:11:31'),
	(438, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 19:51:16'),
	(439, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 06:15:34'),
	(440, 1, 'admin', 'messe_badge_add', 'Badge \'KVA 500\' hinzugefügt für Messe-Marker ID: 9', 32, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 06:28:20'),
	(441, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 07:39:16'),
	(442, 1, 'admin', 'nfc_chip_deleted', 'NFC-Chip \'04:5C:83:70:3E:61:81\' gelöscht', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 07:39:35'),
	(443, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 12:14:25'),
	(444, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-12 14:48:58'),
	(445, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 14:50:04'),
	(446, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-12 14:54:29'),
	(447, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-12 22:01:59'),
	(448, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 22:03:16'),
	(449, 1, 'admin', 'bug_report', 'Bug gemeldet: test123', NULL, '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 22:33:20'),
	(450, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 07:53:29'),
	(451, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 11:46:05'),
	(452, 1, 'admin', 'marker_updated', 'Marker \'NFC Test\' aktualisiert', 32, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 12:22:31'),
	(453, 1, 'admin', 'status_changed', 'Status geändert: Vermietet → Auf Messe', 32, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 12:22:31'),
	(454, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 13:52:57'),
	(455, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-13 13:59:43'),
	(456, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-13 14:00:25'),
	(457, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 07:45:59'),
	(458, 1, 'admin', 'bug_deleted', 'Bug-Ticket #19 \'test123\' gelöscht', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 07:47:13'),
	(459, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 08:27:24'),
	(460, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 09:13:48'),
	(461, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 10:13:00'),
	(462, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 13:43:20'),
	(463, 1, 'admin', 'marker_updated', 'Marker \'Test QR\' aktualisiert', 31, '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 13:44:15'),
	(464, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-14 13:44:51'),
	(465, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-15 16:18:45'),
	(466, 1, 'admin', 'login', 'Benutzer angemeldet', NULL, '109.43.114.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-16 06:27:16');

-- Exportiere Struktur von Prozedur d044f149.add_nfc_codes_to_pool
DELIMITER //
CREATE PROCEDURE `add_nfc_codes_to_pool`(
    IN `p_start_number` INT,
    IN `p_count` INT,
    IN `p_prefix` VARCHAR(20),
    IN `p_batch_id` VARCHAR(100)
)
BEGIN
    DECLARE v_counter INT DEFAULT 0;
    DECLARE v_current_number INT;
    DECLARE v_nfc_code VARCHAR(100);
    DECLARE v_token VARCHAR(64);
    
    WHILE v_counter < p_count DO
        SET v_current_number = p_start_number + v_counter;
        SET v_nfc_code = CONCAT(p_prefix, '-', LPAD(v_current_number, 4, '0'));
        SET v_token = SHA2(CONCAT(v_nfc_code, UUID(), NOW()), 256);
        
        INSERT INTO nfc_pool (nfc_code, batch_id, print_batch, public_token)
        VALUES (v_nfc_code, p_batch_id, p_batch_id, v_token);
        
        SET v_counter = v_counter + 1;
    END WHILE;
    
    INSERT INTO activity_log (
        username,
        action,
        details
    ) VALUES (
        'system',
        'nfc_pool_extended',
        CONCAT(p_count, ' NFC-Tags zum Pool hinzugefügt (', p_prefix, '-', LPAD(p_start_number, 4, '0'), ' bis ', p_prefix, '-', LPAD(p_start_number + p_count - 1, 4, '0'), ')')
    );
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.ar_markers
CREATE TABLE IF NOT EXISTS `ar_markers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `ar_pattern` varchar(500) DEFAULT NULL COMMENT 'AR-Pattern-Datei',
  `ar_scale` decimal(10,2) DEFAULT 1.00,
  `ar_rotation_x` decimal(10,2) DEFAULT 0.00,
  `ar_rotation_y` decimal(10,2) DEFAULT 0.00,
  `ar_rotation_z` decimal(10,2) DEFAULT 0.00,
  `ar_model_url` varchar(500) DEFAULT NULL COMMENT '3D-Modell für AR',
  `enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ar_enabled` tinyint(1) DEFAULT 1,
  `ar_instructions` text DEFAULT NULL,
  `distance_threshold` int(11) DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marker_id` (`marker_id`),
  CONSTRAINT `ar_markers_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.ar_markers: ~0 rows (ungefähr)

-- Exportiere Struktur von Ereignis d044f149.auto_cleanup_deleted_markers
DELIMITER //
CREATE EVENT `auto_cleanup_deleted_markers` ON SCHEDULE EVERY 7 DAY STARTS '2025-10-08 02:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL cleanup_deleted_markers()//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.auto_maintenance_events
CREATE TABLE IF NOT EXISTS `auto_maintenance_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspection_id` int(11) NOT NULL,
  `calendar_event_id` int(11) DEFAULT NULL,
  `created_automatically` tinyint(1) NOT NULL DEFAULT 1,
  `notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `inspection_id` (`inspection_id`),
  KEY `calendar_event_id` (`calendar_event_id`),
  CONSTRAINT `auto_maintenance_events_ibfk_1` FOREIGN KEY (`inspection_id`) REFERENCES `inspection_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auto_maintenance_events_ibfk_2` FOREIGN KEY (`calendar_event_id`) REFERENCES `calendar_events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.auto_maintenance_events: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.bug_admin_users
CREATE TABLE IF NOT EXISTS `bug_admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.bug_admin_users: ~1 rows (ungefähr)
INSERT INTO `bug_admin_users` (`id`, `username`, `password`, `email`, `full_name`, `is_active`, `created_at`, `last_login`) VALUES
	(1, 'admin', '$2y$12$Mg55kXgUWMhmqWw0Z129lej77LzE2aVLZuxBu0dH22676pn4PxxHi', 'doofwiescheisse@outlook.de', 'Administrator', 1, '2025-10-05 12:36:37', '2025-10-23 03:59:10');

-- Exportiere Struktur von Tabelle d044f149.bug_comments
CREATE TABLE IF NOT EXISTS `bug_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bug_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bug_id` (`bug_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bug_comments_ibfk_1` FOREIGN KEY (`bug_id`) REFERENCES `bug_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bug_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `bug_admin_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.bug_comments: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.bug_reports
CREATE TABLE IF NOT EXISTS `bug_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `browser_info` text DEFAULT NULL,
  `screenshot_path` varchar(500) DEFAULT NULL,
  `status` enum('offen','in_bearbeitung','erledigt') DEFAULT 'offen',
  `priority` enum('niedrig','mittel','hoch','kritisch') DEFAULT 'mittel',
  `reported_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.bug_reports: ~3 rows (ungefähr)
INSERT INTO `bug_reports` (`id`, `title`, `description`, `email`, `phone`, `page_url`, `browser_info`, `screenshot_path`, `status`, `priority`, `reported_by`, `assigned_to`, `created_at`, `updated_at`, `archived_at`, `notes`) VALUES
	(16, 'Verbesserungen', 'GPS genauer machen\r\nMan kann denn Standort buchen ob wohl der Geo Fence es verbietet\r\nDropdown menü geht auf Mobil geräten nicht\r\nMesse sachen fertig machen', 'admin@example.com', '', 'https://bgg-objekt.de/index.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, 'erledigt', 'mittel', 1, 1, '2025-10-20 14:27:14', '2025-10-22 18:26:08', '2025-10-22 18:26:08', NULL),
	(17, 'https://bgg-objekt.de/delete_inspection.php?id=1&marker_id=19', 'https://bgg-objekt.de/delete_inspection.php?id=1&marker_id=19', 'admin@example.com', '', 'https://bgg-objekt.de/edit_marker.php?id=19', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, 'erledigt', 'niedrig', 1, NULL, '2025-10-21 09:44:47', '2025-10-22 18:27:01', '2025-10-22 18:27:01', NULL),
	(18, 'Messe', 'Wenn man kein Hero-Bild hochläd soll der hintergrund Transparent sein so das man das ganze hintergrundbild sehen kann', 'admin@example.com', '', 'https://bgg-objekt.de/index.php', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', NULL, 'erledigt', 'niedrig', 1, NULL, '2025-10-22 12:36:01', '2025-10-22 18:26:20', '2025-10-22 18:26:20', NULL);

-- Exportiere Struktur von Tabelle d044f149.bulk_operations
CREATE TABLE IF NOT EXISTS `bulk_operations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation_type` varchar(50) NOT NULL,
  `initiated_by` int(11) NOT NULL,
  `target_user_ids` text DEFAULT NULL,
  `parameters` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `results` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_initiated` (`initiated_by`,`created_at`),
  KEY `idx_status` (`status`),
  CONSTRAINT `bulk_operations_ibfk_1` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.bulk_operations: ~0 rows (ungefähr)

-- Exportiere Struktur von Prozedur d044f149.calculate_daily_statistics
DELIMITER //
CREATE PROCEDURE `calculate_daily_statistics`()
BEGIN
    DECLARE v_date DATE;
    SET v_date = CURDATE();
    
    
    INSERT INTO maintenance_statistics (
        date,
        total_markers,
        maintenance_due,
        maintenance_overdue,
        maintenance_completed,
        inspection_due,
        inspection_overdue,
        inspection_completed,
        average_operating_hours,
        total_scans
    )
    SELECT
        v_date,
        (SELECT COUNT(*) FROM markers WHERE deleted_at IS NULL AND is_storage = 0),
        (SELECT COUNT(*) FROM markers WHERE deleted_at IS NULL AND maintenance_required = 1 AND next_maintenance >= CURDATE()),
        (SELECT COUNT(*) FROM markers WHERE deleted_at IS NULL AND maintenance_required = 1 AND next_maintenance < CURDATE()),
        (SELECT COUNT(*) FROM maintenance_history WHERE DATE(maintenance_date) = v_date),
        (SELECT COUNT(*) FROM inspection_schedules WHERE status IN ('fällig')),
        (SELECT COUNT(*) FROM inspection_schedules WHERE status = 'überfällig'),
        (SELECT COUNT(*) FROM inspection_history WHERE DATE(inspection_date) = v_date),
        (SELECT AVG(operating_hours) FROM markers WHERE deleted_at IS NULL AND is_storage = 0),
        (SELECT COUNT(*) FROM qr_scan_history WHERE DATE(scan_timestamp) = v_date)
    ON DUPLICATE KEY UPDATE
        total_markers = VALUES(total_markers),
        maintenance_due = VALUES(maintenance_due),
        maintenance_overdue = VALUES(maintenance_overdue),
        maintenance_completed = VALUES(maintenance_completed),
        inspection_due = VALUES(inspection_due),
        inspection_overdue = VALUES(inspection_overdue),
        inspection_completed = VALUES(inspection_completed),
        average_operating_hours = VALUES(average_operating_hours),
        total_scans = VALUES(total_scans);
END//
DELIMITER ;

-- Exportiere Struktur von Prozedur d044f149.calculate_dashboard_statistics
DELIMITER //
CREATE PROCEDURE `calculate_dashboard_statistics`()
BEGIN
    DECLARE today DATE;
    SET today = CURDATE();
    
    
    INSERT INTO dashboard_statistics (
        stat_date,
        total_markers,
        active_markers,
        inactive_markers,
        storage_devices,
        rental_devices,
        customer_devices,
        repair_devices,
        finished_devices,
        maintenance_due_count,
        maintenance_overdue_count,
        total_scans,
        total_gps_updates,
        new_markers_today,
        completed_maintenances
    )
    SELECT
        today,
        COUNT(*),
        SUM(CASE WHEN is_activated = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN is_activated = 0 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN is_storage = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN is_storage = 0 AND is_customer_device = 0 AND is_repair_device = 0 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN is_customer_device = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN is_repair_device = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN is_finished = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND deleted_at IS NULL THEN 1 ELSE 0 END),
        SUM(CASE WHEN next_maintenance < CURDATE() AND deleted_at IS NULL THEN 1 ELSE 0 END),
        (SELECT COUNT(*) FROM activity_log WHERE action = 'qr_scanned' AND DATE(created_at) = today),
        (SELECT COUNT(*) FROM activity_log WHERE action = 'position_updated' AND DATE(created_at) = today),
        (SELECT COUNT(*) FROM markers WHERE DATE(created_at) = today AND deleted_at IS NULL),
        (SELECT COUNT(*) FROM maintenance_history WHERE DATE(maintenance_date) = today)
    FROM markers
    WHERE deleted_at IS NULL
    ON DUPLICATE KEY UPDATE
        total_markers = VALUES(total_markers),
        active_markers = VALUES(active_markers),
        inactive_markers = VALUES(inactive_markers),
        storage_devices = VALUES(storage_devices),
        rental_devices = VALUES(rental_devices),
        customer_devices = VALUES(customer_devices),
        repair_devices = VALUES(repair_devices),
        finished_devices = VALUES(finished_devices),
        maintenance_due_count = VALUES(maintenance_due_count),
        maintenance_overdue_count = VALUES(maintenance_overdue_count),
        total_scans = VALUES(total_scans),
        total_gps_updates = VALUES(total_gps_updates),
        new_markers_today = VALUES(new_markers_today),
        completed_maintenances = VALUES(completed_maintenances);
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.calendar_events
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` enum('maintenance','deadline','reminder','custom') DEFAULT 'custom',
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `all_day` tinyint(1) DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `reminder_minutes` int(11) DEFAULT NULL,
  `synced_to_outlook` tinyint(1) DEFAULT 0,
  `outlook_event_id` varchar(255) DEFAULT NULL,
  `synced_to_google` tinyint(1) DEFAULT 0,
  `google_event_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `user_id` (`user_id`),
  KEY `start_date` (`start_date`),
  KEY `event_type` (`event_type`),
  CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calendar_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.calendar_events: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.calendar_integrations
CREATE TABLE IF NOT EXISTS `calendar_integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `provider` enum('google','outlook','ical') NOT NULL,
  `calendar_id` varchar(500) DEFAULT NULL,
  `access_token` text DEFAULT NULL COMMENT 'Verschlüsselt',
  `refresh_token` text DEFAULT NULL COMMENT 'Verschlüsselt',
  `token_expires_at` datetime DEFAULT NULL,
  `sync_enabled` tinyint(1) DEFAULT 1,
  `last_sync` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_provider` (`user_id`,`provider`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_calendar_integrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.calendar_integrations: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.calendar_settings
CREATE TABLE IF NOT EXISTS `calendar_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `calendar_enabled` tinyint(1) DEFAULT 0,
  `auto_maintenance_events` tinyint(1) DEFAULT 0 COMMENT 'Automatische Wartungstermine',
  `auto_deadline_events` tinyint(1) DEFAULT 0 COMMENT 'Automatische Deadline-Events',
  `reminder_days_before` int(11) DEFAULT 7 COMMENT 'Tage vor Event erinnern',
  `outlook_token` text DEFAULT NULL,
  `outlook_refresh_token` text DEFAULT NULL,
  `outlook_token_expires` datetime DEFAULT NULL,
  `google_token` text DEFAULT NULL,
  `google_refresh_token` text DEFAULT NULL,
  `ical_url` varchar(500) DEFAULT NULL,
  `last_sync` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `google_calendar_enabled` tinyint(1) DEFAULT 0,
  `outlook_enabled` tinyint(1) DEFAULT 0,
  `ical_enabled` tinyint(1) DEFAULT 1,
  `auto_create_events` tinyint(1) DEFAULT 0,
  `calendar_color` varchar(7) DEFAULT '#007bff',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `calendar_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.calendar_settings: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.calendar_sync
CREATE TABLE IF NOT EXISTS `calendar_sync` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `provider` enum('outlook','google') NOT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `calendar_id` varchar(255) DEFAULT NULL,
  `last_sync` datetime DEFAULT NULL,
  `sync_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_provider` (`user_id`,`provider`),
  KEY `provider` (`provider`),
  CONSTRAINT `calendar_sync_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.calendar_sync: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL COMMENT 'FontAwesome Icon-Klasse',
  `color` varchar(7) DEFAULT '#007bff' COMMENT 'Hex-Farbcode für die Kategorie',
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0 COMMENT 'System-Kategorie kann nicht gelöscht werden',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.categories: ~5 rows (ungefähr)
INSERT INTO `categories` (`id`, `name`, `icon`, `color`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
	(1, 'Generator', 'fa-bolt', '#ffc107', 'Stromerzeugungsgeräte', 1, '2025-10-02 20:09:42', '2025-10-02 20:09:42'),
	(2, 'Baumaschine', 'fa-truck', '#fd7e14', 'Baumaschinen und schweres Gerät', 1, '2025-10-02 20:09:42', '2025-10-02 20:09:42'),
	(3, 'Werkzeug', 'fa-wrench', '#6c757d', 'Handwerkzeuge und Elektrowerkzeuge', 1, '2025-10-02 20:09:42', '2025-10-02 20:09:42'),
	(4, 'Fahrzeug', 'fa-car', '#007bff', 'Fahrzeuge und Transportmittel', 1, '2025-10-02 20:09:42', '2025-10-02 20:09:42'),
	(5, 'Lager', 'fa-warehouse', '#28a745', 'Lagerflächen und Container', 1, '2025-10-02 20:09:42', '2025-10-02 20:09:42');

-- Exportiere Struktur von Ereignis d044f149.cleanup_dashboard_cache
DELIMITER //
CREATE EVENT `cleanup_dashboard_cache` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-02 12:38:42' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  DELETE FROM dashboard_stats_cache WHERE expires_at < NOW();
END//
DELIMITER ;

-- Exportiere Struktur von Prozedur d044f149.cleanup_deleted_markers
DELIMITER //
CREATE PROCEDURE `cleanup_deleted_markers`()
BEGIN
    DECLARE deleted_count INT DEFAULT 0;
    
    
    UPDATE qr_code_pool q
    INNER JOIN markers m ON q.marker_id = m.id
    SET q.is_assigned = 0,
        q.marker_id = NULL,
        q.assigned_at = NULL
    WHERE m.deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND m.deleted_at IS NOT NULL;
    
    
    SELECT COUNT(*) INTO deleted_count
    FROM markers
    WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND deleted_at IS NOT NULL;
    
    
    DELETE FROM markers
    WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND deleted_at IS NOT NULL;
    
    
    INSERT INTO activity_log (
        username,
        action,
        details
    ) VALUES (
        'system',
        'cleanup_old_deleted_markers'
    );
    
END//
DELIMITER ;

-- Exportiere Struktur von Prozedur d044f149.cleanup_expired_trusted_devices
DELIMITER //
CREATE PROCEDURE `cleanup_expired_trusted_devices`()
BEGIN
    DELETE FROM public_view_trusted_devices 
    WHERE expires_at < NOW();
    
    
    INSERT INTO activity_log (
        username,
        action,
        details
    ) VALUES (
        'system',
        'cleanup_expired_trusted_devices',
        CONCAT('Removed expired trusted devices')
    );
END//
DELIMITER ;

-- Exportiere Struktur von Ereignis d044f149.cleanup_inactive_users
DELIMITER //
CREATE EVENT `cleanup_inactive_users` ON SCHEDULE EVERY 5 MINUTE STARTS '2025-10-19 11:44:45' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM active_users WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 MINUTE)//
DELIMITER ;

-- Exportiere Struktur von Ereignis d044f149.cleanup_old_live_updates
DELIMITER //
CREATE EVENT `cleanup_old_live_updates` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-19 11:44:44' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM live_updates WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)//
DELIMITER ;

-- Exportiere Struktur von Prozedur d044f149.cleanup_old_login_attempts
DELIMITER //
CREATE PROCEDURE `cleanup_old_login_attempts`()
BEGIN
    DELETE FROM public_view_login_attempts 
    WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 7 DAY);
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.custom_fields
CREATE TABLE IF NOT EXISTS `custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(100) NOT NULL,
  `field_type` enum('text','textarea','number','date') DEFAULT 'text',
  `required` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `custom_fields_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.custom_fields: ~0 rows (ungefähr)

-- Exportiere Struktur von Ereignis d044f149.daily_email_notifications
DELIMITER //
CREATE EVENT `daily_email_notifications` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-10 07:00:00' ON COMPLETION PRESERVE ENABLE DO CALL queue_notification_emails()//
DELIMITER ;

-- Exportiere Struktur von Ereignis d044f149.daily_inspection_check
DELIMITER //
CREATE EVENT `daily_inspection_check` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-08 06:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL send_inspection_notifications()//
DELIMITER ;

-- Exportiere Struktur von Ereignis d044f149.daily_maintenance_status_update
DELIMITER //
CREATE EVENT `daily_maintenance_status_update` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-25 06:40:10' ON COMPLETION NOT PRESERVE ENABLE DO CALL update_overdue_maintenance_status()//
DELIMITER ;

-- Exportiere Struktur von Ereignis d044f149.daily_statistics_calculation
DELIMITER //
CREATE EVENT `daily_statistics_calculation` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-10 01:00:00' ON COMPLETION PRESERVE ENABLE DO CALL calculate_daily_statistics()//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.dashboard_statistics
CREATE TABLE IF NOT EXISTS `dashboard_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stat_date` date NOT NULL,
  `total_markers` int(11) DEFAULT 0,
  `active_markers` int(11) DEFAULT 0,
  `inactive_markers` int(11) DEFAULT 0,
  `storage_devices` int(11) DEFAULT 0,
  `rental_devices` int(11) DEFAULT 0,
  `customer_devices` int(11) DEFAULT 0,
  `repair_devices` int(11) DEFAULT 0,
  `finished_devices` int(11) DEFAULT 0,
  `maintenance_due_count` int(11) DEFAULT 0,
  `maintenance_overdue_count` int(11) DEFAULT 0,
  `total_scans` int(11) DEFAULT 0,
  `total_gps_updates` int(11) DEFAULT 0,
  `new_markers_today` int(11) DEFAULT 0,
  `completed_maintenances` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_stat_date` (`stat_date`),
  KEY `idx_stat_date` (`stat_date`),
  KEY `idx_dashboard_stats_date` (`stat_date`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tägliche Dashboard-Statistiken';

-- Exportiere Daten aus Tabelle d044f149.dashboard_statistics: ~19 rows (ungefähr)
INSERT INTO `dashboard_statistics` (`id`, `stat_date`, `total_markers`, `active_markers`, `inactive_markers`, `storage_devices`, `rental_devices`, `customer_devices`, `repair_devices`, `finished_devices`, `maintenance_due_count`, `maintenance_overdue_count`, `total_scans`, `total_gps_updates`, `new_markers_today`, `completed_maintenances`, `created_at`) VALUES
	(1, '2025-10-17', 1, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, '2025-10-17 00:05:00'),
	(2, '2025-10-18', 1, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, '2025-10-18 00:05:00'),
	(3, '2025-10-19', 1, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, '2025-10-19 00:05:00'),
	(4, '2025-10-20', 1, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, '2025-10-20 00:05:00'),
	(5, '2025-10-21', 1, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, '2025-10-21 00:05:00'),
	(6, '2025-10-22', 2, 2, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-22 00:05:00'),
	(7, '2025-10-23', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-23 00:05:00'),
	(8, '2025-10-24', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-24 00:05:00'),
	(9, '2025-10-25', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-25 00:05:00'),
	(10, '2025-10-26', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-26 00:05:00'),
	(11, '2025-10-27', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-27 00:05:00'),
	(12, '2025-10-28', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-28 00:05:00'),
	(13, '2025-10-29', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-29 00:05:00'),
	(14, '2025-10-30', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-30 00:05:00'),
	(15, '2025-10-31', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-10-31 00:05:00'),
	(16, '2025-11-01', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-01 00:05:00'),
	(17, '2025-11-02', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-02 00:05:00'),
	(18, '2025-11-03', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-03 00:05:00'),
	(19, '2025-11-04', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-04 00:05:00'),
	(20, '2025-11-05', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-05 00:05:00'),
	(21, '2025-11-06', 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-06 00:05:00'),
	(22, '2025-11-07', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2025-11-07 00:05:00'),
	(23, '2025-11-08', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2025-11-08 00:05:00'),
	(24, '2025-11-09', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2025-11-09 00:05:00'),
	(25, '2025-11-10', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2025-11-10 00:05:00'),
	(26, '2025-11-11', 2, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-11 00:05:00'),
	(27, '2025-11-12', 2, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-12 00:05:00'),
	(28, '2025-11-13', 2, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-13 00:05:00'),
	(29, '2025-11-14', 2, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-14 00:05:00'),
	(30, '2025-11-15', 2, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-15 00:05:00'),
	(31, '2025-11-16', 2, 2, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, '2025-11-16 00:05:00');

-- Exportiere Struktur von Tabelle d044f149.dashboard_stats_cache
CREATE TABLE IF NOT EXISTS `dashboard_stats_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(100) NOT NULL,
  `cache_data` text NOT NULL COMMENT 'JSON mit gecachten Daten',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cache für Dashboard-Statistiken';

-- Exportiere Daten aus Tabelle d044f149.dashboard_stats_cache: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.dashboard_widgets
CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `widget_type` varchar(50) NOT NULL COMMENT 'maintenance_chart, heatmap, statistics, etc.',
  `position` int(11) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `config` text DEFAULT NULL COMMENT 'JSON config für Widget',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `widget_settings` text DEFAULT NULL,
  `widget_position` int(11) DEFAULT 0,
  `widget_size` varchar(20) DEFAULT 'medium',
  PRIMARY KEY (`id`),
  KEY `idx_user_widgets` (`user_id`,`position`),
  CONSTRAINT `fk_widget_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Benutzerdefinierte Dashboard-Widgets';

-- Exportiere Daten aus Tabelle d044f149.dashboard_widgets: ~0 rows (ungefähr)

-- Exportiere Struktur von Prozedur d044f149.delete_qr_code
DELIMITER //
CREATE PROCEDURE `delete_qr_code`(
    IN p_qr_code VARCHAR(100),
    IN p_user_id INT,
    IN p_force_delete BOOLEAN
)
BEGIN
    DECLARE v_is_assigned BOOLEAN;
    DECLARE v_marker_id INT;
    DECLARE v_marker_name VARCHAR(255);
    
    
    SELECT is_assigned, marker_id 
    INTO v_is_assigned, v_marker_id
    FROM qr_code_pool
    WHERE qr_code = p_qr_code;
    
    
    IF v_is_assigned IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'QR-Code nicht gefunden';
    END IF;
    
    
    IF v_is_assigned = 1 AND p_force_delete = 0 THEN
        
        SELECT name INTO v_marker_name
        FROM markers
        WHERE id = v_marker_id;
        
        SIGNAL SQLSTATE '45000';
    END IF;
    
    
    DELETE FROM qr_code_pool WHERE qr_code = p_qr_code;
    
    
    INSERT INTO activity_log (
        user_id,
        username,
        action,
        details
    ) VALUES (
        p_user_id,
        (SELECT username FROM users WHERE id = p_user_id),
        'qr_code_deleted'
    );
    
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.device_usage_log
CREATE TABLE IF NOT EXISTS `device_usage_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `operating_hours_start` decimal(10,2) DEFAULT 0.00,
  `operating_hours_end` decimal(10,2) DEFAULT 0.00,
  `hours_used` decimal(10,2) DEFAULT 0.00,
  `fuel_consumed` decimal(10,2) DEFAULT 0.00,
  `location_changes` int(11) DEFAULT 0,
  `scans` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_marker_date` (`marker_id`,`log_date`),
  KEY `idx_log_date` (`log_date`),
  CONSTRAINT `fk_usage_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tägliche Nutzungsdaten pro Gerät';

-- Exportiere Daten aus Tabelle d044f149.device_usage_log: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.digital_signatures
CREATE TABLE IF NOT EXISTS `digital_signatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) DEFAULT NULL,
  `inspection_id` int(11) DEFAULT NULL,
  `maintenance_id` int(11) DEFAULT NULL,
  `signature_data` longtext NOT NULL,
  `signer_name` varchar(255) NOT NULL,
  `signer_email` varchar(255) DEFAULT NULL,
  `signed_at` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `document_type` varchar(50) NOT NULL,
  `document_hash` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `inspection_id` (`inspection_id`),
  KEY `signed_at` (`signed_at`),
  CONSTRAINT `digital_signatures_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.digital_signatures: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.email_log
CREATE TABLE IF NOT EXISTS `email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `marker_id` int(11) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `marker_id` (`marker_id`),
  CONSTRAINT `email_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `email_log_ibfk_2` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.email_log: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.email_notification_queue
CREATE TABLE IF NOT EXISTS `email_notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_type` enum('maintenance_due','inspection_due','document_expiry','maintenance_overdue','inspection_overdue') NOT NULL,
  `related_id` int(11) NOT NULL COMMENT 'ID des betroffenen Objekts',
  `related_type` enum('marker','inspection','document') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_notification_type` (`notification_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Warteschlange für E-Mail-Benachrichtigungen';

-- Exportiere Daten aus Tabelle d044f149.email_notification_queue: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.escalation_settings
CREATE TABLE IF NOT EXISTS `escalation_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warning_days` int(11) NOT NULL DEFAULT 7 COMMENT 'Tage vor Fälligkeit für Warnung',
  `overdue_days` int(11) NOT NULL DEFAULT 3 COMMENT 'Tage nach Fälligkeit für erste Eskalation',
  `critical_days` int(11) NOT NULL DEFAULT 7 COMMENT 'Tage nach Fälligkeit für kritische Eskalation',
  `notification_emails` text DEFAULT NULL COMMENT 'Kommagetrennte E-Mail-Adressen',
  `enable_escalation` tinyint(1) DEFAULT 1 COMMENT 'Eskalations-System aktiv?',
  `last_check` datetime DEFAULT NULL COMMENT 'Letzter Cron-Check',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Einstellungen für Wartungs-Eskalation';

-- Exportiere Daten aus Tabelle d044f149.escalation_settings: ~0 rows (ungefähr)
INSERT INTO `escalation_settings` (`id`, `warning_days`, `overdue_days`, `critical_days`, `notification_emails`, `enable_escalation`, `last_check`, `created_at`, `updated_at`) VALUES
	(1, 7, 3, 7, '', 1, NULL, '2025-10-11 05:22:21', '2025-10-11 05:22:21');

-- Exportiere Struktur von Ereignis d044f149.event_daily_dashboard_stats
DELIMITER //
CREATE EVENT `event_daily_dashboard_stats` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-17 00:05:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL calculate_dashboard_statistics()//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.geofences
CREATE TABLE IF NOT EXISTS `geofences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `coordinates` text NOT NULL COMMENT 'JSON array of lat/lng coordinates',
  `center_lat` decimal(10,8) NOT NULL,
  `center_lng` decimal(11,8) NOT NULL,
  `radius` int(11) DEFAULT NULL COMMENT 'Radius in Metern (für Kreise)',
  `fence_type` enum('polygon','circle') DEFAULT 'polygon',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `created_by` (`created_by`),
  KEY `is_active` (`is_active`),
  CONSTRAINT `geofences_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `geofence_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `geofences_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.geofences: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.geofence_groups
CREATE TABLE IF NOT EXISTS `geofence_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(20) DEFAULT '#007bff',
  `allow_customer_devices` tinyint(1) DEFAULT 1,
  `allow_rental_devices` tinyint(1) DEFAULT 1,
  `allow_storage_devices` tinyint(1) DEFAULT 1,
  `allow_repair_devices` tinyint(1) DEFAULT 1,
  `allow_only_finished` tinyint(1) DEFAULT 0 COMMENT 'Nur fertige Geräte (is_finished=1) erlauben',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.geofence_groups: ~4 rows (ungefähr)
INSERT INTO `geofence_groups` (`id`, `name`, `description`, `color`, `allow_customer_devices`, `allow_rental_devices`, `allow_storage_devices`, `allow_repair_devices`, `allow_only_finished`, `created_at`, `updated_at`) VALUES
	(1, 'Lager', 'Lagerbereich - Nur Lager- und Mietgeräte', '#28a745', 0, 1, 1, 0, 0, '2025-10-16 07:40:12', '2025-10-16 07:40:12'),
	(3, 'Kundenbereich', 'Kundenbereich - Kundengeräte und Abholung', '#17a2b8', 1, 0, 0, 1, 0, '2025-10-16 07:40:12', '2025-10-16 07:40:12'),
	(4, 'Allgemein', 'Allgemeiner Bereich - Alle Gerätetypen erlaubt', '#0088ff', 0, 0, 1, 0, 0, '2025-10-16 07:40:12', '2025-10-20 15:24:49'),
	(5, 'Mietgeräte Lager', '', '#ff0000', 0, 1, 0, 0, 0, '2025-10-16 16:15:00', '2025-10-16 16:15:00');

-- Exportiere Struktur von Funktion d044f149.get_next_available_qr
DELIMITER //
CREATE FUNCTION `get_next_available_qr`() RETURNS varchar(100) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
    DETERMINISTIC
BEGIN
    DECLARE next_qr VARCHAR(100);
    
    SELECT qr_code INTO next_qr
    FROM qr_code_pool
    WHERE is_assigned = 0
    ORDER BY qr_code
    LIMIT 1;
    
    RETURN next_qr;
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.import_export_history
CREATE TABLE IF NOT EXISTS `import_export_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation` varchar(10) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `record_count` int(11) DEFAULT 0,
  `success_count` int(11) DEFAULT 0,
  `error_count` int(11) DEFAULT 0,
  `errors` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_operation` (`user_id`,`operation`,`created_at`),
  CONSTRAINT `import_export_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.import_export_history: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.inspection_history
CREATE TABLE IF NOT EXISTS `inspection_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspection_schedule_id` int(11) NOT NULL,
  `marker_id` int(11) NOT NULL,
  `inspection_type` varchar(100) NOT NULL,
  `inspection_date` date NOT NULL,
  `inspector_name` varchar(100) DEFAULT NULL,
  `result` enum('bestanden','nicht bestanden','mit Mängeln') DEFAULT 'bestanden',
  `certificate_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `protocol_file` varchar(255) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `inspection_schedule_id` (`inspection_schedule_id`),
  KEY `marker_id` (`marker_id`),
  KEY `inspection_date` (`inspection_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.inspection_history: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.inspection_notifications
CREATE TABLE IF NOT EXISTS `inspection_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspection_id` int(11) NOT NULL,
  `marker_id` int(11) NOT NULL,
  `notification_type` enum('warnung','fällig','überfällig') NOT NULL,
  `sent_to` varchar(255) NOT NULL COMMENT 'E-Mail-Adressen',
  `sent_at` datetime DEFAULT current_timestamp(),
  `days_until_due` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_id` (`inspection_id`),
  KEY `marker_id` (`marker_id`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `fk_inspection_notifications_inspection` FOREIGN KEY (`inspection_id`) REFERENCES `inspection_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inspection_notifications_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.inspection_notifications: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.inspection_schedules
CREATE TABLE IF NOT EXISTS `inspection_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `inspection_type` enum('TÜV','UVV','DGUV','Sicherheitsprüfung','Sonstiges') NOT NULL,
  `inspection_interval_months` int(11) NOT NULL DEFAULT 12,
  `last_inspection` date DEFAULT NULL,
  `next_inspection` date DEFAULT NULL,
  `inspection_authority` varchar(100) DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responsible_person` varchar(100) DEFAULT NULL COMMENT 'Verantwortliche Person',
  `notification_days_before` int(11) DEFAULT 14 COMMENT 'Tage vorher benachrichtigen',
  `last_notification_sent` date DEFAULT NULL COMMENT 'Letzte Benachrichtigung',
  `status` enum('aktuell','fällig','überfällig') DEFAULT 'aktuell',
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `idx_next_inspection` (`next_inspection`),
  KEY `idx_inspection_status` (`status`),
  KEY `idx_next_inspection_date` (`next_inspection`),
  KEY `idx_status` (`status`),
  KEY `idx_next_inspection_status` (`next_inspection`,`status`),
  CONSTRAINT `inspection_schedules_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.inspection_schedules: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.live_updates
CREATE TABLE IF NOT EXISTS `live_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `event_data` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.live_updates: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.login_attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username_or_email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_username_time` (`username_or_email`,`attempt_time`),
  KEY `idx_ip_time` (`ip_address`,`attempt_time`),
  KEY `idx_user_time` (`user_id`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.login_attempts: ~53 rows (ungefähr)
INSERT INTO `login_attempts` (`id`, `username_or_email`, `ip_address`, `user_agent`, `attempt_time`, `success`, `user_id`) VALUES
	(1, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-04 08:05:11', 1, 1),
	(2, 'admin', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:03:25', 1, 1),
	(3, 'testname', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:04:30', 1, 10),
	(4, 'admin', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:08:21', 1, 1),
	(5, 'admin', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-11-04 12:33:12', 1, 1),
	(6, 'admin', '80.187.105.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', '2025-11-04 16:40:18', 1, 1),
	(7, 'admin', '85.13.129.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-04 22:19:30', 1, 1),
	(8, 'admin', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 12:46:26', 1, 1),
	(9, 'admin', '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 21:14:38', 1, 1),
	(10, 'admin', '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-05 21:53:40', 1, 1),
	(11, 'admin', '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 06:00:58', 1, 1),
	(12, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 07:44:13', 1, 1),
	(13, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 11:20:36', 1, 1),
	(14, 'admin', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 12:12:01', 1, 1),
	(15, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:45:19', 1, 1),
	(16, 'admin', '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-07 05:56:18', 1, 1),
	(17, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-07 07:47:04', 1, 1),
	(18, 'admin', '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 12:07:50', 1, 1),
	(19, 'admin', '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:20:09', 1, 1),
	(20, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 15:42:05', 1, 1),
	(21, 'admin', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 15:43:44', 1, 1),
	(22, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 16:18:35', 1, 1),
	(23, 'admin', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-10 16:44:01', 1, 1),
	(24, 'admin', '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 19:53:33', 1, 1),
	(25, 'admin', '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 20:47:30', 1, 1),
	(26, 'admin', '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-10 21:51:26', 1, 1),
	(27, 'admin', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 06:09:28', 1, 1),
	(28, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 09:50:41', 1, 1),
	(29, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 09:51:10', 1, 1),
	(30, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-11 09:52:19', 1, 1),
	(31, 'admin', '80.187.101.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 12:43:09', 1, 1),
	(32, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 16:08:44', 1, 1),
	(33, 'admin', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-11 19:51:16', 1, 1),
	(34, 'admin', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 06:15:34', 1, 1),
	(35, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 07:39:16', 1, 1),
	(36, 'admin', '80.187.101.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 12:14:25', 1, 1),
	(37, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-12 14:48:58', 1, 1),
	(38, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 14:50:04', 1, 1),
	(39, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-12 14:54:29', 1, 1),
	(40, 'admin', '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-12 22:01:59', 1, 1),
	(41, 'admin', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-12 22:03:16', 1, 1),
	(42, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 07:53:29', 1, 1),
	(43, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 11:46:05', 1, 1),
	(44, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-13 13:52:57', 1, 1),
	(45, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-13 13:59:43', 1, 1),
	(46, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-13 14:00:25', 1, 1),
	(47, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 07:45:59', 1, 1),
	(48, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 08:27:24', 1, 1),
	(49, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 09:13:48', 1, 1),
	(50, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 10:13:00', 1, 1),
	(51, 'admin', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-14 13:43:20', 1, 1),
	(52, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-14 13:44:51', 1, 1),
	(53, 'admin', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', '2025-11-15 16:18:45', 1, 1),
	(54, 'admin', '109.43.114.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-16 06:27:16', 1, 1);

-- Exportiere Struktur von Tabelle d044f149.maintenance_checklists
CREATE TABLE IF NOT EXISTS `maintenance_checklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) DEFAULT NULL COMMENT 'NULL = Template für alle Geräte',
  `category` varchar(100) DEFAULT NULL COMMENT 'Gerätekategorie für automatische Zuweisung',
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_template` tinyint(1) DEFAULT 1 COMMENT 'Template oder gerätespezifisch',
  `is_dguv_compliant` tinyint(1) DEFAULT 0 COMMENT 'DGUV 3 konform',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `category` (`category`),
  KEY `created_by` (`created_by`),
  KEY `idx_category` (`category`),
  CONSTRAINT `fk_maintenance_checklists_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_maintenance_checklists_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_checklists: ~8 rows (ungefähr)
INSERT INTO `maintenance_checklists` (`id`, `marker_id`, `category`, `name`, `description`, `is_template`, `is_dguv_compliant`, `created_by`, `created_at`, `updated_at`) VALUES
	(9, NULL, 'Notstromaggregat', 'Wartungscheckliste Elektrotechnischer Anlagenteil', 'Professionelle Wartungscheckliste für Notstromaggregate, Generatoren und elektrotechnische Anlagenteile. Umfasst Stammdatenerfassung, Sichtprüfungen, Messungen, Funktionsprüfungen und Probeläufe gemäß BEG Deutschland Standards.', 1, 1, 1, '2025-10-28 05:19:50', '2025-11-16 05:27:37'),
	(10, NULL, 'Container', 'Container Test Protokoll', 'Vollständiges Testprotokoll für Container-Anlagen. Prüfung von Außenhaut, Auffangwanne, Türen/Klappen und Finish gemäß DIN VDE / EN 60204-1 IEC 204-1', 1, 1, 1, '2025-10-29 11:27:05', '2025-11-16 05:27:37'),
	(13, NULL, 'Notstromaggregat', 'Test Protokoll AUSGANG', 'Test Protokoll für Ausgangskontrollen von Notstromaggregaten. Umfasst Wartungsmaterial, Ausstattung, Füllmedien, Überprüfungen, Aufkleber und Dokumentation.', 1, 1, 1, '2025-10-29 20:33:03', '2025-11-16 05:27:37'),
	(14, NULL, 'Notstromaggregat', 'Wartungscheckliste motortechnischer Anlagenteil', 'Wartungscheckliste für den motortechnischen Anlagenteil von Notstromaggregaten. Umfasst Stammdaten, Betriebsstunden, Motorwartung, Filter, Kühlsystem, Batterie und Lastprobelauf.', 1, 1, 1, '2025-10-29 20:36:28', '2025-11-16 05:27:37'),
	(15, NULL, 'Notstromaggregat', 'Prüfprotokoll für mobile Stromerzeuger DGUV V3', 'DGUV3 Prüfprotokoll für mobile Stromerzeuger gemäß DGUV Information 203-032. Umfasst Sichtprüfung, Messungen, RCD-Prüfung, Erprobungen und Spannungs-/Frequenzprüfung.', 1, 1, 1, '2025-10-30 05:03:01', '2025-11-16 05:27:37'),
	(16, NULL, 'Notstromaggregat', 'Wartungscheckliste Elektrotechnischer Anlagenteil', 'Wartungscheckliste für den elektrotechnischen Anlagenteil von Notstromaggregaten. Umfasst Stammdaten, Betriebsstunden, elektrische Prüfungen, Batterie, Spannungsmessungen und Probeläufe.', 1, 1, 1, '2025-10-30 05:09:00', '2025-11-16 05:27:37'),
	(17, NULL, 'Notstromaggregat', 'Test Protokoll SEA Intern', 'SEA Intern Test Protokoll für Notstromaggregate. Umfasst Aggregate Daten, Funktionen, Ausrüstung, Ein-/Ausgänge, Schaltelemente, Zeiten, Prüfungen und Schutzsysteme.', 1, 1, 1, '2025-10-30 05:16:06', '2025-11-16 05:27:37'),
	(18, NULL, 'Anhänger', 'Test Protokoll Fahrgestelle', 'BGG Deutschland - Umfassende Prüfcheckliste für Fahrgestelle und Anhänger. Enthält Sichtprüfung, Beleuchtungsprüfung (12V/24V), Funktionsprüfung, Beschriftung und Gewichtsmessung gemäß BGG-Standards.', 1, 1, 1, '2025-10-30 05:25:26', '2025-11-16 05:27:37');

-- Exportiere Struktur von Tabelle d044f149.maintenance_checklist_items
CREATE TABLE IF NOT EXISTS `maintenance_checklist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_id` int(11) NOT NULL,
  `item_text` varchar(500) NOT NULL,
  `field_type` enum('checkbox','radio','select','text','textarea','number','date','measurement') DEFAULT 'checkbox' COMMENT 'Typ des Prüfpunkts',
  `field_options` text DEFAULT NULL COMMENT 'JSON Array mit Optionen für radio/select',
  `default_value` varchar(255) DEFAULT NULL COMMENT 'Standardwert für das Feld',
  `item_order` int(11) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 1,
  `requires_photo` tinyint(1) DEFAULT 0,
  `requires_measurement` tinyint(1) DEFAULT 0 COMMENT 'Benötigt Messwert-Eingabe',
  `measurement_unit` varchar(50) DEFAULT NULL COMMENT 'z.B. mm, Ohm, Bar',
  `measurement_min` decimal(10,2) DEFAULT NULL,
  `measurement_max` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `checklist_id` (`checklist_id`),
  KEY `idx_field_type` (`field_type`),
  CONSTRAINT `fk_checklist_items_checklist` FOREIGN KEY (`checklist_id`) REFERENCES `maintenance_checklists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1237 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_checklist_items: ~893 rows (ungefähr)
INSERT INTO `maintenance_checklist_items` (`id`, `checklist_id`, `item_text`, `field_type`, `field_options`, `default_value`, `item_order`, `is_required`, `requires_photo`, `requires_measurement`, `measurement_unit`, `measurement_min`, `measurement_max`, `created_at`) VALUES
	(100, 9, 'Infos/Probleme/Störungen lt. Betreiber abfragen', 'radio', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:50'),
	(101, 9, 'Anlagendokumentation (z.B. Schaltpläne, Wartungshandbuch, Betriebshandbuch usw.) verfügbar?', 'radio', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:51'),
	(102, 9, 'Stammdatenblatt erfassen/ergänzen', 'radio', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:51'),
	(103, 9, 'Betriebsstunden aktuell (h)', 'number', NULL, NULL, 4, 1, 0, 0, 'h', NULL, NULL, '2025-10-28 05:19:51'),
	(104, 9, 'Anzahl Starts', 'number', NULL, NULL, 5, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:51'),
	(105, 9, 'Letzte Wartung am (Datum, Betriebsstunden, durch wen)', 'text', NULL, NULL, 6, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:51'),
	(106, 9, 'Wartungsbuch geführt', 'radio', NULL, NULL, 7, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:51'),
	(107, 9, 'Allgemeiner Anlagenzustand (Sichtprüfung)', 'radio', NULL, NULL, 8, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:52'),
	(108, 9, 'Anlage freischalten', 'radio', NULL, NULL, 9, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:52'),
	(109, 9, 'Signallampen/LED\'s prüfen', 'radio', NULL, NULL, 10, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:52'),
	(110, 9, 'Sichtprüfung der Mess-/Anzeigeninstrumente/Sicherungen', 'radio', NULL, NULL, 11, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:52'),
	(111, 9, 'Kühlwasservorwärmung prüfen', 'radio', NULL, NULL, 12, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:53'),
	(112, 9, 'Zu- und Abluftjalousie prüfen', 'radio', NULL, NULL, 13, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:53'),
	(113, 9, 'El. Leitungen/Anschlüsse auf Festsitz bzw. allg. Funktion/Beschädigung prüfen', 'radio', NULL, NULL, 14, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:53'),
	(114, 9, 'Generator auf Verschmutzung, Beschädigung, Korrosion prüfen (Spannungsfreiheit sicherstellen)', 'radio', NULL, NULL, 15, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:53'),
	(115, 9, 'Sicherheitseinrichtung - Schaltfunktion der Übertemperatur ohne Sensor', 'radio', NULL, NULL, 16, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:54'),
	(116, 9, 'Sicherheitseinrichtung - Öldruckmangel', 'radio', NULL, NULL, 17, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:54'),
	(117, 9, 'Sicherheitseinrichtung - Kühlwasserstand Mangel', 'radio', NULL, NULL, 18, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:54'),
	(118, 9, 'Sicherheitseinrichtung - Kühlwassertemperaturüberwachung', 'radio', NULL, NULL, 19, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:54'),
	(119, 9, 'Batteriespannung', 'measurement', NULL, NULL, 20, 1, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:55'),
	(120, 9, 'Batterie Füllstand/Säuregehalt prüfen/messen', 'radio', NULL, NULL, 21, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:55'),
	(121, 9, 'Batterie Ladespannung vom Ladegerät', 'measurement', NULL, NULL, 22, 1, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:55'),
	(122, 9, 'Spannungseinbruch beim Start', 'measurement', NULL, NULL, 23, 1, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:55'),
	(123, 9, 'Funktion Anlasser prüfen', 'radio', NULL, NULL, 24, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:55'),
	(124, 9, 'Funktion Lichtmaschine Spannung', 'measurement', NULL, NULL, 25, 1, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:55'),
	(125, 9, 'Netzspannung L1 (V)', 'measurement', NULL, NULL, 26, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:55'),
	(126, 9, 'Netzspannung L2 (V)', 'measurement', NULL, NULL, 27, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(127, 9, 'Netzspannung L3 (V)', 'measurement', NULL, NULL, 28, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(128, 9, 'Netzspannung Frequenz', 'measurement', NULL, NULL, 29, 1, 0, 0, 'Hz', NULL, NULL, '2025-10-28 05:19:56'),
	(129, 9, 'Generator Leerlaufspannung L1 (V)', 'measurement', NULL, NULL, 30, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(130, 9, 'Generator Leerlaufspannung L2 (V)', 'measurement', NULL, NULL, 31, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(131, 9, 'Generator Leerlaufspannung L3 (V)', 'measurement', NULL, NULL, 32, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(132, 9, 'Generator Leerlaufspannung Frequenz', 'measurement', NULL, NULL, 33, 1, 0, 0, 'Hz', NULL, NULL, '2025-10-28 05:19:56'),
	(133, 9, 'Funktion des Fehlerstromschutzschalters (Prüftaste) - NUR durch Betreiber!', 'radio', NULL, NULL, 34, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(134, 9, 'Probelauf ohne Last, wenn mit Last nicht möglich - NUR durch Betreiber!', 'radio', NULL, NULL, 35, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:56'),
	(135, 9, 'Probelauf unter Last - Leistung (kW)', 'measurement', NULL, NULL, 36, 0, 0, 0, 'kW', NULL, NULL, '2025-10-28 05:19:57'),
	(136, 9, 'Probelauf unter Last - kVA/cos', 'measurement', NULL, NULL, 37, 0, 0, 0, 'kVA', NULL, NULL, '2025-10-28 05:19:57'),
	(137, 9, 'Probelauf unter Last - L1 Spannung', 'measurement', NULL, NULL, 38, 0, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:57'),
	(138, 9, 'Probelauf unter Last - L1 Strom', 'measurement', NULL, NULL, 39, 0, 0, 0, 'A', NULL, NULL, '2025-10-28 05:19:57'),
	(139, 9, 'Probelauf unter Last - L2 Spannung', 'measurement', NULL, NULL, 40, 0, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:57'),
	(140, 9, 'Probelauf unter Last - L2 Strom', 'measurement', NULL, NULL, 41, 0, 0, 0, 'A', NULL, NULL, '2025-10-28 05:19:57'),
	(141, 9, 'Probelauf unter Last - L3 Spannung', 'measurement', NULL, NULL, 42, 0, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:57'),
	(142, 9, 'Probelauf unter Last - L3 Strom', 'measurement', NULL, NULL, 43, 0, 0, 0, 'A', NULL, NULL, '2025-10-28 05:19:57'),
	(143, 9, 'Probelauf unter Last - Frequenz', 'measurement', NULL, NULL, 44, 0, 0, 0, 'Hz', NULL, NULL, '2025-10-28 05:19:57'),
	(144, 9, 'Fehlstarts - Anzahl Versuche', 'number', NULL, NULL, 45, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:57'),
	(145, 9, 'Fehlermeldung Batterieunterspannung', 'measurement', NULL, NULL, 46, 0, 0, 0, 'V', NULL, NULL, '2025-10-28 05:19:58'),
	(146, 9, 'Startladefunktion prüfen', 'radio', NULL, NULL, 47, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:58'),
	(147, 9, 'Problemlose Synchronisation prüfen', 'radio', NULL, NULL, 48, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:58'),
	(148, 9, 'Netzausfalltest - Übernahme', 'measurement', NULL, NULL, 49, 0, 0, 0, 'sec', NULL, NULL, '2025-10-28 05:19:58'),
	(149, 9, 'Netzausfalltest - Rückschaltzeit', 'measurement', NULL, NULL, 50, 0, 0, 0, 'sec', NULL, NULL, '2025-10-28 05:19:58'),
	(150, 9, 'Netzausfalltest - Nachlauf', 'measurement', NULL, NULL, 51, 0, 0, 0, 'sec', NULL, NULL, '2025-10-28 05:19:58'),
	(151, 9, 'Sprinklerbetrieb - Übernahme', 'measurement', NULL, NULL, 52, 0, 0, 0, 'sec', NULL, NULL, '2025-10-28 05:19:58'),
	(152, 9, 'Sprinklerbetrieb - Nachlauf', 'measurement', NULL, NULL, 53, 0, 0, 0, 'sec', NULL, NULL, '2025-10-28 05:19:58'),
	(153, 9, 'Netzausfall im Probebetrieb prüfen', 'radio', NULL, NULL, 54, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:59'),
	(154, 9, 'Netzausfall im Netzparallelbetrieb prüfen', 'radio', NULL, NULL, 55, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:59'),
	(155, 9, 'Netzausfall im Sprinklerbetrieb prüfen', 'radio', NULL, NULL, 56, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:59'),
	(156, 9, 'Netzkuppelschalter prüfen', 'radio', NULL, NULL, 57, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:19:59'),
	(157, 9, 'Generatorkuppelschalter prüfen', 'radio', NULL, NULL, 58, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(158, 9, 'Hinweis: Tests Pos. 24-37 auf Wunsch des Anlagenverantwortlichen nicht durchgeführt (Datum + Unterschrift)', 'textarea', NULL, NULL, 59, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(159, 9, 'Alles, was wegen Wartung abgeklemmt/abgeschaltet wurde, wieder in Ursprungzustand versetzen', 'radio', NULL, NULL, 60, 1, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(160, 9, 'Zusätzliche Bemerkung 1', 'textarea', NULL, NULL, 61, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(161, 9, 'Zusätzliche Bemerkung 2', 'textarea', NULL, NULL, 62, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(162, 9, 'Zusätzliche Bemerkung 3', 'textarea', NULL, NULL, 63, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(163, 9, 'Zusätzliche Bemerkung 4', 'textarea', NULL, NULL, 64, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(164, 9, 'Allgemeine Bemerkungen zur Wartung', 'textarea', NULL, NULL, 65, 0, 0, 0, NULL, NULL, NULL, '2025-10-28 05:20:00'),
	(165, 10, 'Anlage', 'text', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(166, 10, 'Auftrag', 'text', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(167, 10, 'Type', 'text', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(168, 10, 'Serien Nr.', 'text', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(169, 10, 'Außenhaut - Beulen festgestellt?', 'radio', NULL, NULL, 10, 1, 1, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(170, 10, 'Außenhaut - Kratzer festgestellt?', 'radio', NULL, NULL, 11, 1, 1, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(171, 10, 'Erdungsschrauben - Hersteller', 'text', NULL, NULL, 12, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(172, 10, 'Erdungsschrauben - Anzahl', 'number', NULL, NULL, 13, 1, 0, 0, 'Stück', NULL, NULL, '2025-10-29 11:27:05'),
	(173, 10, 'Erdungsschrauben vollständig?', 'radio', NULL, NULL, 14, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(174, 10, 'Erdung Hinweisblatt vorhanden?', 'radio', NULL, NULL, 15, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(175, 10, 'Ausbesserungslack vorhanden?', 'radio', NULL, NULL, 16, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(176, 10, 'Außenhaut - Sonstiges', 'textarea', NULL, NULL, 17, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(177, 10, 'Auffangwanne - Ecken dicht geschweißt?', 'radio', NULL, NULL, 20, 1, 1, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(178, 10, 'Auffangwanne - Sensor richtig positioniert?', 'radio', NULL, NULL, 21, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(179, 10, 'Auffangwanne - Sonstiges', 'textarea', NULL, NULL, 22, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(180, 10, 'Zugangstüren dicht?', 'radio', NULL, NULL, 30, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(181, 10, 'Zugangstüren schließen?', 'radio', NULL, NULL, 31, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(182, 10, 'Portaltüren dicht?', 'radio', NULL, NULL, 32, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(183, 10, 'Portaltüren schließen?', 'radio', NULL, NULL, 33, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(184, 10, 'Tank Öffnung dicht?', 'radio', NULL, NULL, 34, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(185, 10, 'Tank Klappe dicht?', 'radio', NULL, NULL, 35, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(186, 10, 'Tank Klappe schließt?', 'radio', NULL, NULL, 36, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(187, 10, 'Türen/Klappen - Sonstiges', 'textarea', NULL, NULL, 37, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:05'),
	(188, 10, 'Flugrost entfernt?', 'radio', NULL, NULL, 40, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(189, 10, 'Lackschäden ausgebessert?', 'radio', NULL, NULL, 41, 1, 1, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(190, 10, 'Zubehör nach Auftrag montiert?', 'radio', NULL, NULL, 42, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(191, 10, 'Finisch - Sonstiges', 'textarea', NULL, NULL, 43, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(192, 10, 'Freigabe - Anlage erfüllt die Anforderungen gem. DIN VDE / EN 60204-1 IEC 204-1', 'radio', NULL, NULL, 50, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(193, 10, 'Anlage freigegeben?', 'radio', NULL, NULL, 51, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(194, 10, 'Prüfer (Name)', 'text', NULL, NULL, 52, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(195, 10, 'Technisch Verantwortlicher (Name)', 'text', NULL, NULL, 53, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 11:27:06'),
	(440, 13, 'Anlage', 'text', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(441, 13, 'Auftrag', 'text', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(442, 13, 'Type', 'text', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(443, 13, 'Serien Nr.', 'text', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(444, 13, 'Luftfilter Hauptelement - Nummer', 'text', NULL, NULL, 10, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(445, 13, 'Luftfilter Hauptelement - Hersteller', 'text', NULL, NULL, 11, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(446, 13, 'Luftfilter Hauptelement - Anzahl', 'number', NULL, NULL, 12, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(447, 13, 'Luftfilter Sicherheitselement - Nummer', 'text', NULL, NULL, 13, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(448, 13, 'Luftfilter Sicherheitselement - Hersteller', 'text', NULL, NULL, 14, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(449, 13, 'Luftfilter Sicherheitselement - Anzahl', 'number', NULL, NULL, 15, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(450, 13, 'Ölfilter (Vorfilter) - Nummer', 'text', NULL, NULL, 16, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(451, 13, 'Ölfilter (Vorfilter) - Hersteller', 'text', NULL, NULL, 17, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(452, 13, 'Ölfilter (Vorfilter) - Anzahl', 'number', NULL, NULL, 18, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(453, 13, 'Ölfilter (Hauptfilter) - Nummer', 'text', NULL, NULL, 19, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(454, 13, 'Ölfilter (Hauptfilter) - Hersteller', 'text', NULL, NULL, 20, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(455, 13, 'Ölfilter (Hauptfilter) - Anzahl', 'number', NULL, NULL, 21, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(456, 13, 'Kühlwasserfilter - Nummer', 'text', NULL, NULL, 22, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(457, 13, 'Kühlwasserfilter - Hersteller', 'text', NULL, NULL, 23, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(458, 13, 'Kühlwasserfilter - Anzahl', 'number', NULL, NULL, 24, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(459, 13, 'Kraftstoff Wasserabscheider - Nummer', 'text', NULL, NULL, 25, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(460, 13, 'Kraftstoff Wasserabscheider - Hersteller', 'text', NULL, NULL, 26, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(461, 13, 'Kraftstoff Wasserabscheider - Anzahl', 'number', NULL, NULL, 27, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(462, 13, 'Kraftstoff Vorfilter - Nummer', 'text', NULL, NULL, 28, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(463, 13, 'Kraftstoff Vorfilter - Hersteller', 'text', NULL, NULL, 29, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(464, 13, 'Kraftstoff Vorfilter - Anzahl', 'number', NULL, NULL, 30, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(465, 13, 'Kraftstoff Hauptfilter - Nummer', 'text', NULL, NULL, 31, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(466, 13, 'Kraftstoff Hauptfilter - Hersteller', 'text', NULL, NULL, 32, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(467, 13, 'Kraftstoff Hauptfilter - Anzahl', 'number', NULL, NULL, 33, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(468, 13, 'Keilriemen 1 - Nummer', 'text', NULL, NULL, 34, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(469, 13, 'Keilriemen 1 - Hersteller', 'text', NULL, NULL, 35, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(470, 13, 'Keilriemen 1 - Anzahl', 'number', NULL, NULL, 36, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(471, 13, 'Keilriemen 2 - Nummer', 'text', NULL, NULL, 37, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(472, 13, 'Keilriemen 2 - Hersteller', 'text', NULL, NULL, 38, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(473, 13, 'Keilriemen 2 - Anzahl', 'number', NULL, NULL, 39, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(474, 13, 'Keilriemen 3 - Nummer', 'text', NULL, NULL, 40, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(475, 13, 'Keilriemen 3 - Hersteller', 'text', NULL, NULL, 41, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(476, 13, 'Keilriemen 3 - Anzahl', 'number', NULL, NULL, 42, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:04'),
	(477, 13, 'Batterieschalter vorhanden', 'radio', NULL, NULL, 50, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(478, 13, 'Batterieschalter - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 51, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(479, 13, 'Batterieschalter - Anzahl', 'number', NULL, NULL, 52, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(480, 13, 'Batterie - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 53, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(481, 13, 'Batterie - Anzahl', 'number', NULL, NULL, 54, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(482, 13, 'Batterieladegerät vorhanden', 'radio', NULL, NULL, 55, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(483, 13, 'Batterieladegerät - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 56, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(484, 13, 'Batterieladegerät - Anzahl', 'number', NULL, NULL, 57, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(485, 13, 'Automatische Nachfüllung vorhanden', 'radio', NULL, NULL, 58, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(486, 13, 'Automatische Nachfüllung - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 59, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(487, 13, 'Automatische Nachfüllung - Anzahl', 'number', NULL, NULL, 60, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(488, 13, 'Kraftstoff Umschaltung vorhanden', 'radio', NULL, NULL, 61, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(489, 13, 'Kraftstoff Umschaltung - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 62, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(490, 13, 'Kraftstoff Umschaltung - Anzahl', 'number', NULL, NULL, 63, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(491, 13, 'Kraftstoff DC Pumpe vorhanden', 'radio', NULL, NULL, 64, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(492, 13, 'Kraftstoff DC Pumpe - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 65, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(493, 13, 'Kraftstoff DC Pumpe - Anzahl', 'number', NULL, NULL, 66, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(494, 13, 'Relais Zündung - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 67, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(495, 13, 'Relais Zündung - Anzahl', 'number', NULL, NULL, 68, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(496, 13, 'Relais Starter - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 69, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(497, 13, 'Relais Starter - Anzahl', 'number', NULL, NULL, 70, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(498, 13, 'Starter - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 71, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(499, 13, 'Starter - Anzahl', 'number', NULL, NULL, 72, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(500, 13, 'Lichtmaschine - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 73, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(501, 13, 'Lichtmaschine - Anzahl', 'number', NULL, NULL, 74, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(502, 13, 'Motorvorheizung vorhanden', 'radio', NULL, NULL, 75, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(503, 13, 'Motorvorheizung - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 76, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(504, 13, 'Motorvorheizung - Anzahl', 'number', NULL, NULL, 77, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(505, 13, 'Umwälzpumpe vorhanden', 'radio', NULL, NULL, 78, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(506, 13, 'Umwälzpumpe - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 79, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(507, 13, 'Umwälzpumpe - Anzahl', 'number', NULL, NULL, 80, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(508, 13, 'Ölwechselhandpumpe vorhanden', 'radio', NULL, NULL, 81, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(509, 13, 'Ölwechselhandpumpe - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 82, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(510, 13, 'Ölwechselhandpumpe - Anzahl', 'number', NULL, NULL, 83, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(511, 13, 'Ölwechsel DC Pumpe vorhanden', 'radio', NULL, NULL, 84, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(512, 13, 'Ölwechsel DC Pumpe - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 85, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(513, 13, 'Ölwechsel DC Pumpe - Anzahl', 'number', NULL, NULL, 86, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(514, 13, 'Zündschlüssel vorhanden', 'radio', NULL, NULL, 87, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(515, 13, 'Zündschlüssel - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 88, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(516, 13, 'Zündschlüssel - Anzahl', 'number', NULL, NULL, 89, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(517, 13, 'Gehäuseschlüssel vorhanden', 'radio', NULL, NULL, 90, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(518, 13, 'Gehäuseschlüssel - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 91, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(519, 13, 'Gehäuseschlüssel - Anzahl', 'number', NULL, NULL, 92, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(520, 13, 'Panelschlüssel vorhanden', 'radio', NULL, NULL, 93, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(521, 13, 'Panelschlüssel - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 94, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(522, 13, 'Panelschlüssel - Anzahl', 'number', NULL, NULL, 95, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(523, 13, 'Schaltschrankschlüssel vorhanden', 'radio', NULL, NULL, 96, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(524, 13, 'Schaltschrankschlüssel - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 97, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(525, 13, 'Schaltschrankschlüssel - Anzahl', 'number', NULL, NULL, 98, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(526, 13, 'Stauraumschlüssel vorhanden', 'radio', NULL, NULL, 99, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(527, 13, 'Stauraumschlüssel - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 100, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(528, 13, 'Stauraumschlüssel - Anzahl', 'number', NULL, NULL, 101, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(529, 13, 'Befehlsschalter Schlüssel vorhanden', 'radio', NULL, NULL, 102, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(530, 13, 'Befehlsschalter Schlüssel - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 103, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(531, 13, 'Befehlsschalter Schlüssel - Anzahl', 'number', NULL, NULL, 104, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(532, 13, 'Isolationswächter vorhanden', 'radio', NULL, NULL, 105, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(533, 13, 'Isolationswächter - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 106, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(534, 13, 'Isolationswächter - Anzahl', 'number', NULL, NULL, 107, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(535, 13, 'Tank Niveaugeber el. vorhanden', 'radio', NULL, NULL, 108, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(536, 13, 'Tank Niveaugeber el. - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 109, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(537, 13, 'Tank Niveaugeber el. - Anzahl', 'number', NULL, NULL, 110, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(538, 13, 'Tank Niveaugeber mech. vorhanden', 'radio', NULL, NULL, 111, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(539, 13, 'Tank Niveaugeber mech. - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 112, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(540, 13, 'Tank Niveaugeber mech. - Anzahl', 'number', NULL, NULL, 113, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(541, 13, 'Tankverschluss vorhanden', 'radio', NULL, NULL, 114, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(542, 13, 'Tankverschluss - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 115, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(543, 13, 'Tankverschluss - Anzahl', 'number', NULL, NULL, 116, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(544, 13, 'Sonstiges vorhanden', 'radio', NULL, NULL, 117, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(545, 13, 'Sonstiges - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 118, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(546, 13, 'Sonstiges - Anzahl', 'number', NULL, NULL, 119, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(547, 13, 'Kraftstoff vorhanden', 'radio', NULL, NULL, 150, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(548, 13, 'Kraftstoff - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 151, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(549, 13, 'Kraftstoff - Füllstand (%)', 'number', NULL, NULL, 152, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(550, 13, 'Ad Blue vorhanden', 'radio', NULL, NULL, 153, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(551, 13, 'Ad Blue - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 154, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(552, 13, 'Ad Blue - Füllstand (%)', 'number', NULL, NULL, 155, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(553, 13, 'Kühlwasser vorhanden', 'radio', NULL, NULL, 156, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(554, 13, 'Kühlwasser - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 157, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(555, 13, 'Kühlwasser - Füllstand (%)', 'number', NULL, NULL, 158, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(556, 13, 'Motoröl vorhanden', 'radio', NULL, NULL, 159, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(557, 13, 'Motoröl - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 160, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(558, 13, 'Motoröl - Füllstand (%)', 'number', NULL, NULL, 161, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(559, 13, 'Hydrauliköl vorhanden', 'radio', NULL, NULL, 162, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(560, 13, 'Hydrauliköl - Hersteller/Type/Abmessungen', 'text', NULL, NULL, 163, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(561, 13, 'Hydrauliköl - Füllstand (%)', 'number', NULL, NULL, 164, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:05'),
	(562, 13, 'Fernüberwachung überprüft', 'radio', NULL, NULL, 200, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(563, 13, 'Fernüberwachung - GSM Karte', 'text', NULL, NULL, 201, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(564, 13, 'Im Portal angelegt', 'radio', NULL, NULL, 202, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(565, 13, 'Im Portal angelegt - ID', 'text', NULL, NULL, 203, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(566, 13, 'Batteriespannung überprüft', 'radio', NULL, NULL, 204, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(567, 13, 'Batteriespannung (Volt)', 'number', NULL, NULL, 205, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(568, 13, 'Türverschlüsse leichtgängig', 'radio', NULL, NULL, 206, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(569, 13, 'Scharniere überprüft', 'radio', NULL, NULL, 207, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(570, 13, 'Scharniere - Behandlung (WD40/abgeschmiert)', 'text', NULL, NULL, 208, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(571, 13, 'Tür Dichtgummis auf Dichtheit geprüft und eingestellt', 'radio', NULL, NULL, 209, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(572, 13, 'Gehäuse dicht - überprüft und abgedichtet', 'radio', NULL, NULL, 210, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(573, 13, 'Lackfehler / Beschädigungen ausgebessert', 'radio', NULL, NULL, 211, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(574, 13, 'Staplertaschen - nach Verladung Lack ausbessern', 'radio', NULL, NULL, 212, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(575, 13, 'Kurzbedienungsanleitung vorhanden', 'radio', NULL, NULL, 250, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(576, 13, 'Lastabgang Aufkleber "Öffnung und Anschluss nur durch Elektrofachkraft"', 'radio', NULL, NULL, 251, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(577, 13, 'DGUV Typ Aufkleber "DGUV TYP A / B / C / D"', 'radio', NULL, NULL, 252, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(578, 13, 'DGUV Aufkleber "Nächste Prüfung"', 'radio', NULL, NULL, 253, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(579, 13, 'UVV Aufkleber "Nächste Prüfung"', 'radio', NULL, NULL, 254, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(580, 13, 'Wartungsaufkleber "Nächste Wartung"', 'radio', NULL, NULL, 255, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(581, 13, 'Dokumentation "Nächste Wartung"', 'radio', NULL, NULL, 256, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(582, 13, 'Service und Kundenportal "QR-Code - Portalzugang"', 'radio', NULL, NULL, 257, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:06'),
	(583, 13, 'Motor Werkstatthandbuch vorhanden', 'radio', NULL, NULL, 300, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(584, 13, 'Motor Werkstatthandbuch - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 301, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(585, 13, 'Motor Wartungsplan vorhanden', 'radio', NULL, NULL, 302, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(586, 13, 'Motor Wartungsplan - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 303, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(587, 13, 'Motor Bedienungsanleitung vorhanden', 'radio', NULL, NULL, 304, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(588, 13, 'Motor Bedienungsanleitung - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 305, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(589, 13, 'Generator Bedienungsanleitung vorhanden', 'radio', NULL, NULL, 306, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(590, 13, 'Generator Bedienungsanleitung - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 307, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(591, 13, 'Steuerung Bedienungsanleitung vorhanden', 'radio', NULL, NULL, 308, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(592, 13, 'Steuerung Bedienungsanleitung - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 309, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(593, 13, 'S/N Spez. Wartungssatz vorhanden', 'radio', NULL, NULL, 350, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(594, 13, 'S/N Spez. Wartungssatz - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 351, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(595, 13, 'S/N Spez. Wartungspauschale vorhanden', 'radio', NULL, NULL, 352, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(596, 13, 'S/N Spez. Wartungspauschale - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 353, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(597, 13, 'Wartungssatz 500 Std. vorhanden', 'radio', NULL, NULL, 354, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(598, 13, 'Wartungssatz 500 Std. - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 355, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(599, 13, 'Wartungssatz 1000 Std. vorhanden', 'radio', NULL, NULL, 356, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(600, 13, 'Wartungssatz 1000 Std. - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 357, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(601, 13, 'Wartungssatz 1500 Std. vorhanden', 'radio', NULL, NULL, 358, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(602, 13, 'Wartungssatz 1500 Std. - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 359, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(603, 13, 'Wartungssatz 2000 Std. vorhanden', 'radio', NULL, NULL, 360, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(604, 13, 'Wartungssatz 2000 Std. - Ablage/Dateiname/Art. Nr.', 'text', NULL, NULL, 361, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(605, 13, 'Anlage angelegt', 'radio', NULL, NULL, 362, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:07'),
	(606, 13, 'Anlage freigegeben (Die geprüfte Maschine/Anlage erfüllt die Anforderungen gem. DIN VDE / EN 60204-1 IEC 204-1)', 'radio', NULL, NULL, 400, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(607, 13, 'Prüfer - Name', 'text', NULL, NULL, 401, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(608, 13, 'Prüfer - Datum', 'date', NULL, NULL, 402, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(609, 13, 'Technisch Verantwortlicher - Name', 'text', NULL, NULL, 403, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(610, 13, 'Technisch Verantwortlicher - Datum', 'date', NULL, NULL, 404, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(611, 13, 'Ersatzteile & Service - Name', 'text', NULL, NULL, 405, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(612, 13, 'Ersatzteile & Service - Datum', 'date', NULL, NULL, 406, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(613, 13, 'Einkauf - Name', 'text', NULL, NULL, 407, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(614, 13, 'Einkauf - Datum', 'date', NULL, NULL, 408, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(615, 13, 'Verkauf - Name', 'text', NULL, NULL, 409, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(616, 13, 'Verkauf - Datum', 'date', NULL, NULL, 410, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(617, 13, 'MR / MK - Name', 'text', NULL, NULL, 411, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(618, 13, 'MR / MK - Datum', 'date', NULL, NULL, 412, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:33:08'),
	(619, 14, 'Auftrags-Nr.', 'text', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(620, 14, 'Datum', 'date', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(621, 14, 'Anlage', 'text', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(622, 14, 'Anlage S/Nr.', 'text', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(623, 14, 'Motor', 'text', NULL, NULL, 5, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(624, 14, 'Motor S/Nr.', 'text', NULL, NULL, 6, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(625, 14, 'Generator', 'text', NULL, NULL, 7, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(626, 14, 'Generator S/Nr.', 'text', NULL, NULL, 8, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(627, 14, 'Steuerung', 'text', NULL, NULL, 9, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(628, 14, 'Steuerung S/Nr.', 'text', NULL, NULL, 10, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(629, 14, 'Standort', 'text', NULL, NULL, 11, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(630, 14, 'Kraftstoff', 'select', NULL, NULL, 12, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:28'),
	(631, 14, 'Infos/Probleme/Störungen lt. Betreiber abfragen', 'radio', NULL, NULL, 20, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(632, 14, 'Anlagendokumentation (z. B. Schaltpläne, Wartungshandbuch, Betriebshandbuch usw.) verfügbar? - prüfen', 'radio', NULL, NULL, 21, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(633, 14, 'Stammdatenblatt erfassen/ergänzen', 'radio', NULL, NULL, 22, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(634, 14, 'Betriebsstunden aktuell (h)', 'number', NULL, NULL, 23, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(635, 14, 'Anzahl Starts', 'number', NULL, NULL, 24, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(636, 14, 'Letzte Wartung am - Datum', 'text', NULL, NULL, 25, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(637, 14, 'Letzte Wartung bei - Betriebsstunden', 'text', NULL, NULL, 26, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(638, 14, 'Letzte Wartung durch - Name', 'text', NULL, NULL, 27, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(639, 14, 'Wartungsbuch geführt - prüfen', 'radio', NULL, NULL, 28, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(640, 14, 'Allgemeiner Anlagenzustand (Sichtprüfung) - bewerten', 'radio', NULL, NULL, 29, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(641, 14, 'Anlage freischalten - durchführen', 'radio', NULL, NULL, 30, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(642, 14, 'Probelauf - durchführen', 'radio', NULL, NULL, 31, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(643, 14, 'Motorlaufgeräusch - prüfen', 'radio', NULL, NULL, 32, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(644, 14, 'Dichtheit (Motor, Leitungen, Schläuche) - prüfen', 'radio', NULL, NULL, 33, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(645, 14, 'Drehzahlregler - prüfen', 'radio', NULL, NULL, 34, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(646, 14, 'Sicherheitseinrichtung - Schaltfunktion der Übertemperatur ohne Sensor - prüfen', 'radio', NULL, NULL, 35, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(647, 14, 'Sicherheitseinrichtung - Öldruckmangel - prüfen', 'radio', NULL, NULL, 36, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(648, 14, 'Sicherheitseinrichtung - Kühlwasserstandsmangel - prüfen', 'radio', NULL, NULL, 37, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(649, 14, 'Sicherheitseinrichtung - Kühlwassertemperaturüberwachung - prüfen', 'radio', NULL, NULL, 38, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(650, 14, 'Turbolader Auslaufgeräusch - prüfen', 'radio', NULL, NULL, 39, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(651, 14, 'Motoröl - wechseln', 'radio', NULL, NULL, 40, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(652, 14, 'Öl Filter - reinigen/erneuern', 'radio', NULL, NULL, 41, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(653, 14, 'Kraftstoff Filter - reinigen/erneuern', 'radio', NULL, NULL, 42, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(654, 14, 'Luft Filter/Ölbad - reinigen/erneuern', 'radio', NULL, NULL, 43, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(655, 14, 'Zentrifuge - reinigen', 'radio', NULL, NULL, 44, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(656, 14, 'Kühlwasserstand - prüfen', 'radio', NULL, NULL, 45, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(657, 14, 'Frostschutzgehalt - Frostschutz bis (°C)', 'text', NULL, NULL, 46, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(658, 14, 'Frostschutzgehalt - prüfen/ergänzen', 'radio', NULL, NULL, 47, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(659, 14, 'Kühlwasserpumpe - prüfen', 'radio', NULL, NULL, 48, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(660, 14, 'Kühlwasserthermostat - prüfen', 'radio', NULL, NULL, 49, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(661, 14, 'Ventilspiel - prüfen/einstellen', 'radio', NULL, NULL, 50, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(662, 14, 'Injektoren/Einspritzdüsen - prüfen/einstellen', 'radio', NULL, NULL, 51, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(663, 14, 'Keilriemen - prüfen/einstellen', 'radio', NULL, NULL, 52, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:29'),
	(664, 14, 'Fehlercodes auslesen/löschen - durchführen', 'radio', NULL, NULL, 60, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(665, 14, 'Kraftstoff/-behälter (Sichtprüfung) - prüfen', 'radio', NULL, NULL, 61, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(666, 14, 'Motor-/Generatorlager (Sichtprüfung) - prüfen', 'radio', NULL, NULL, 62, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(667, 14, 'Auffangwanne - prüfen', 'radio', NULL, NULL, 63, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(668, 14, 'Kupplung Motor – Generator (Sichtprüfung) - prüfen', 'radio', NULL, NULL, 64, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(669, 14, 'Batteriespannung (V) - messen', 'number', NULL, NULL, 65, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(670, 14, 'Spannungseinbruch beim Start (V) - messen', 'number', NULL, NULL, 66, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(671, 14, 'Batterie Ladespannung vom Ladegerät (V) - messen', 'number', NULL, NULL, 67, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(672, 14, 'Batterie Füllstand/Säuregehalt - prüfen/messen', 'radio', NULL, NULL, 68, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(673, 14, 'Lastprobelauf - kW', 'number', NULL, NULL, 69, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(674, 14, 'Lastprobelauf - Dauer (Min.)', 'number', NULL, NULL, 70, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(675, 14, 'Lastprobelauf - durchführen', 'radio', NULL, NULL, 71, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(676, 14, 'Alles, was wegen Wartung abgeklemmt/abgeschaltet wurde, wieder in Ursprungszustand versetzen - durchführen', 'radio', NULL, NULL, 72, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(677, 14, 'Bemerkungen', 'textarea', NULL, NULL, 100, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:30'),
	(678, 14, 'Verwendetes Material 1 - Bezeichnung/Artikel', 'text', NULL, NULL, 110, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(679, 14, 'Verwendetes Material 1 - Hersteller/Sorte', 'text', NULL, NULL, 111, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(680, 14, 'Verwendetes Material 1 - Anzahl', 'number', NULL, NULL, 112, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(681, 14, 'Verwendetes Material 2 - Bezeichnung/Artikel', 'text', NULL, NULL, 113, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(682, 14, 'Verwendetes Material 2 - Hersteller/Sorte', 'text', NULL, NULL, 114, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(683, 14, 'Verwendetes Material 2 - Anzahl', 'number', NULL, NULL, 115, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(684, 14, 'Verwendetes Material 3 - Bezeichnung/Artikel', 'text', NULL, NULL, 116, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(685, 14, 'Verwendetes Material 3 - Hersteller/Sorte', 'text', NULL, NULL, 117, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(686, 14, 'Verwendetes Material 3 - Anzahl', 'number', NULL, NULL, 118, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(687, 14, 'Verwendetes Material 4 - Bezeichnung/Artikel', 'text', NULL, NULL, 119, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(688, 14, 'Verwendetes Material 4 - Hersteller/Sorte', 'text', NULL, NULL, 120, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(689, 14, 'Verwendetes Material 4 - Anzahl', 'number', NULL, NULL, 121, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(690, 14, 'Verwendetes Material 5 - Bezeichnung/Artikel', 'text', NULL, NULL, 122, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(691, 14, 'Verwendetes Material 5 - Hersteller/Sorte', 'text', NULL, NULL, 123, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(692, 14, 'Verwendetes Material 5 - Anzahl', 'number', NULL, NULL, 124, 0, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(693, 14, 'Auftraggeber - Name', 'text', NULL, NULL, 150, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(694, 14, 'Auftraggeber - Datum/Unterschrift', 'date', NULL, NULL, 151, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(695, 14, 'Auftragnehmer - Name', 'text', NULL, NULL, 152, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(696, 14, 'Auftragnehmer - Datum/Unterschrift', 'date', NULL, NULL, 153, 1, 0, 0, NULL, NULL, NULL, '2025-10-29 20:36:31'),
	(697, 15, 'Prüfer/Prüferin', 'text', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(698, 15, 'Hersteller/Herstellerin', 'text', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(699, 15, 'Typ', 'text', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(700, 15, 'Baujahr/Serien-Nr.', 'text', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(701, 15, 'Ausführung (A, B, C gemäß DGUV Information 203-032)', 'select', NULL, NULL, 5, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(702, 15, 'Betriebsstunden (h)', 'number', NULL, NULL, 6, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(703, 15, 'Grund der Prüfung', 'select', NULL, NULL, 7, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(704, 15, 'Sichtprüfung - Schäden am Gehäuse', 'radio', NULL, NULL, 20, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(705, 15, 'Sichtprüfung - Beschädigung der zugänglichen Verbindungsleitungen', 'radio', NULL, NULL, 21, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(706, 15, 'Sichtprüfung - Mängel an Biegeschutz und Zugentlastung der Verbindungsleitungen', 'radio', NULL, NULL, 22, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(707, 15, 'Sichtprüfung - Anzeichen von Überlastung und unsachgemäßem Gebrauch', 'radio', NULL, NULL, 23, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(708, 15, 'Sichtprüfung - Unzulässige Eingriffe/Änderungen', 'radio', NULL, NULL, 24, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(709, 15, 'Sichtprüfung - Ordnungsgemäßer Zustand der Schutzabdeckungen', 'radio', NULL, NULL, 25, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(710, 15, 'Sichtprüfung - Sicherheitsbeeinträchtigende Verschmutzung oder Korrosion', 'radio', NULL, NULL, 26, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(711, 15, 'Sichtprüfung - Vorhandensein erforderlicher Luftfilter', 'radio', NULL, NULL, 27, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(712, 15, 'Sichtprüfung - Freie Kühlluft-Öffnungen', 'radio', NULL, NULL, 28, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(713, 15, 'Sichtprüfung - Dichtheit von Kraftstoff-, Schmierstoff- und Kühlsystem', 'radio', NULL, NULL, 29, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(714, 15, 'Sichtprüfung - Einwandfreie Lesbarkeit von Aufschriften und Warnhinweisen', 'radio', NULL, NULL, 30, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(715, 15, 'Sichtprüfung - Keine lockeren PE-/PB-Anschlüsse, keine losen Klemm-/Anschlussverbindungen', 'radio', NULL, NULL, 31, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(716, 15, 'Sichtprüfung - Schutzart des Stromerzeugers IP54 gemäß Abschnitt 3.2', 'radio', NULL, NULL, 32, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(717, 15, 'Sichtprüfung in Ordnung', 'radio', NULL, NULL, 33, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(718, 15, 'Anmerkungen zur Sichtprüfung', 'textarea', NULL, NULL, 34, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:01'),
	(719, 15, 'Messung RPE/RPB - PE/PB der Steckdosen untereinander - Grenzwert (©)', 'text', NULL, NULL, 50, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(720, 15, 'Messung RPE/RPB - PE/PB der Steckdosen untereinander - Istwert (©)', 'number', NULL, NULL, 51, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(721, 15, 'Messung RPE/RPB - PE/PB der Steckdosen untereinander - Mangel', 'radio', NULL, NULL, 52, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(722, 15, 'Messung RPE/RPB - PE/PB der Steckdosen → Klemme PB/PE - Grenzwert (Ω)', 'text', NULL, NULL, 53, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(723, 15, 'Messung RPE/RPB - PE/PB der Steckdosen → Klemme PB/PE - Istwert (Ω)', 'number', NULL, NULL, 54, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(724, 15, 'Messung RPE/RPB - PE/PB der Steckdosen ÃÂ¢ÃÂÃÂ Klemme PB/PE - Mangel', 'radio', NULL, NULL, 55, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(725, 15, 'Isolationsüberwachung - Test/Reset - Test/Hauptschalter löst aus', 'radio', NULL, NULL, 70, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(726, 15, 'Isolationsüberwachung - Test/Reset - Reset', 'radio', NULL, NULL, 71, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(727, 15, 'Isolationsüberwachung - Quittierung (falls vorhanden)', 'radio', NULL, NULL, 72, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(728, 15, 'Messung RISO - aktiver Leiter ÃÂ¢ÃÂÃÂ Klemme PB - Grenzwert (M©)', 'text', NULL, NULL, 80, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(729, 15, 'Messung RISO - aktiver Leiter ÃÂ¢ÃÂÃÂ Klemme PB - Istwert (M©)', 'number', NULL, NULL, 81, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(730, 15, 'Messung RISO - aktiver Leiter ÃÂ¢ÃÂÃÂ Klemme PB - Mangel', 'radio', NULL, NULL, 82, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(731, 15, 'Messung Ableitstrom - Ausführung (mit/ohne Isolationsüberwachung)', 'select', NULL, NULL, 90, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(732, 15, 'Messung Ableitstrom - Grenzwert (mA)', 'text', NULL, NULL, 91, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(733, 15, 'Messung Ableitstrom - Istwert (mA)', 'number', NULL, NULL, 92, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(734, 15, 'Messung Ableitstrom - Mangel', 'radio', NULL, NULL, 93, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(735, 15, 'RCD - Typ (A, F, B, B+)', 'select', NULL, NULL, 100, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(736, 15, 'Anmerkungen zur RCD-Prüfung', 'textarea', NULL, NULL, 101, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(737, 15, 'RCD Nr. 1 - Grenzwert (ms)', 'text', NULL, NULL, 102, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(738, 15, 'RCD Nr. 1 - Istwert (ms)', 'number', NULL, NULL, 103, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(739, 15, 'RCD Nr. 1 - Mangel', 'radio', NULL, NULL, 104, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(740, 15, 'RCD Nr. 2 - Grenzwert (ms)', 'text', NULL, NULL, 105, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(741, 15, 'RCD Nr. 2 - Istwert (ms)', 'number', NULL, NULL, 106, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(742, 15, 'RCD Nr. 2 - Mangel', 'radio', NULL, NULL, 107, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(743, 15, 'RCD Nr. 3 - Grenzwert (ms)', 'text', NULL, NULL, 108, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(744, 15, 'RCD Nr. 3 - Istwert (ms)', 'number', NULL, NULL, 109, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(745, 15, 'RCD Nr. 3 - Mangel', 'radio', NULL, NULL, 110, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(746, 15, 'RCD Nr. 4 - Grenzwert (ms)', 'text', NULL, NULL, 111, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(747, 15, 'RCD Nr. 4 - Istwert (ms)', 'number', NULL, NULL, 112, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(748, 15, 'RCD Nr. 4 - Mangel', 'radio', NULL, NULL, 113, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(749, 15, 'RCD Nr. 5 - Grenzwert (ms)', 'text', NULL, NULL, 114, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(750, 15, 'RCD Nr. 5 - Istwert (ms)', 'number', NULL, NULL, 115, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(751, 15, 'RCD Nr. 5 - Mangel', 'radio', NULL, NULL, 116, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(752, 15, 'RCD Nr. 6 - Grenzwert (ms)', 'text', NULL, NULL, 117, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(753, 15, 'RCD Nr. 6 - Istwert (ms)', 'number', NULL, NULL, 118, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(754, 15, 'RCD Nr. 6 - Mangel', 'radio', NULL, NULL, 119, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(755, 15, 'Erprobung - Starten (von Hand und Elektrostart)', 'radio', NULL, NULL, 150, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(756, 15, 'Erprobung - Runder Motorlauf', 'radio', NULL, NULL, 151, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(757, 15, 'Erprobung - Regelverhalten bei Lastzuschaltung (wenn möglich), schnelle Ausregelung', 'radio', NULL, NULL, 152, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(758, 15, 'Erprobung - Abgase ohne übermäßige Rauchentwicklung', 'radio', NULL, NULL, 153, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(759, 15, 'Anmerkungen zu Erprobungen', 'textarea', NULL, NULL, 154, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(760, 15, 'Spannung U0 - ohne Belastung an jeder Steckdose - Klasse (G1, G2, G3)', 'select', NULL, NULL, 180, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(761, 15, 'Spannung U0 - gemessen (V)', 'number', NULL, NULL, 181, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(762, 15, 'Spannung U0 - Mangel', 'radio', NULL, NULL, 182, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(763, 15, 'Frequenz - Klasse (G1, G2, G3)', 'select', NULL, NULL, 183, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(764, 15, 'Frequenz - gemessen (Hz)', 'number', NULL, NULL, 184, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(765, 15, 'Frequenz - Mangel', 'radio', NULL, NULL, 185, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(766, 15, 'Rechtsdrehfeld - Mangel', 'radio', NULL, NULL, 186, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(767, 15, 'Funktion der Anzeigeinstrumente und der Bedienelemente - Mangel', 'radio', NULL, NULL, 187, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(768, 15, 'Anmerkungen zu Anzeigeinstrumenten', 'textarea', NULL, NULL, 188, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(769, 15, 'Funktion des Betriebsstundenzählers (falls vorhanden) - Mangel', 'radio', NULL, NULL, 189, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(770, 15, 'Anmerkungen zu Betriebsstundenzähler', 'textarea', NULL, NULL, 190, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(771, 15, 'Funktions- und Sicherheitsprüfung mängelfrei?', 'radio', NULL, NULL, 200, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(772, 15, 'Prüfplakette angebracht?', 'radio', NULL, NULL, 201, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(773, 15, 'Nächster Prüftermin', 'date', NULL, NULL, 202, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(774, 15, 'Anmerkungen zur Bewertung', 'textarea', NULL, NULL, 203, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(775, 15, 'Prüfer/Prüferin - Name', 'text', NULL, NULL, 250, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(776, 15, 'Ort', 'text', NULL, NULL, 251, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(777, 15, 'Prüfdatum', 'date', NULL, NULL, 252, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(778, 15, 'Verwendetes Prüf- und Messgerät 1', 'text', NULL, NULL, 253, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(779, 15, 'Verwendetes Prüf- und Messgerät 1 - kalibriert bis', 'date', NULL, NULL, 254, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(780, 15, 'Verwendetes Prüf- und Messgerät 2', 'text', NULL, NULL, 255, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(781, 15, 'Verwendetes Prüf- und Messgerät 2 - kalibriert bis', 'date', NULL, NULL, 256, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(782, 15, 'Verwendetes Prüf- und Messgerät 3', 'text', NULL, NULL, 257, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(783, 15, 'Verwendetes Prüf- und Messgerät 3 - kalibriert bis', 'date', NULL, NULL, 258, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(784, 15, 'Verwendetes Prüf- und Messgerät 4', 'text', NULL, NULL, 259, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(785, 15, 'Verwendetes Prüf- und Messgerät 4 - kalibriert bis', 'date', NULL, NULL, 260, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:03:02'),
	(786, 16, 'Auftrags-Nr.', 'text', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(787, 16, 'Datum', 'date', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(788, 16, 'Anlage', 'text', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(789, 16, 'Anlage S/Nr.', 'text', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(790, 16, 'Motor', 'text', NULL, NULL, 5, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(791, 16, 'Motor S/Nr.', 'text', NULL, NULL, 6, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(792, 16, 'Generator', 'text', NULL, NULL, 7, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(793, 16, 'Generator S/Nr.', 'text', NULL, NULL, 8, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(794, 16, 'Steuerung', 'text', NULL, NULL, 9, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(795, 16, 'Steuerung S/Nr.', 'text', NULL, NULL, 10, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(796, 16, 'Standort', 'text', NULL, NULL, 11, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(797, 16, 'Kraftstoff', 'select', NULL, NULL, 12, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(798, 16, 'Infos/Probleme/Störungen lt. Betreiber - abfragen', 'radio', NULL, NULL, 20, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(799, 16, 'Anlagendokumentation (z. B. Schaltpläne, Wartungshandbuch, Betriebshandbuch usw.) verfügbar? - prüfen', 'radio', NULL, NULL, 21, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(800, 16, 'Stammdatenblatt erfassen/ergänzen', 'radio', NULL, NULL, 22, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(801, 16, 'Betriebsstunden aktuell (h)', 'number', NULL, NULL, 23, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(802, 16, 'Anzahl Starts', 'number', NULL, NULL, 24, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(803, 16, 'Letzte Wartung am - Datum', 'text', NULL, NULL, 25, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(804, 16, 'Letzte Wartung bei - Betriebsstunden', 'text', NULL, NULL, 26, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(805, 16, 'Letzte Wartung durch - Name', 'text', NULL, NULL, 27, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(806, 16, 'Wartungsbuch geführt - prüfen', 'radio', NULL, NULL, 28, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(807, 16, 'Allgemeiner Anlagenzustand (Sichtprüfung) - bewerten', 'radio', NULL, NULL, 29, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(808, 16, 'Anlage freischalten - durchführen', 'radio', NULL, NULL, 30, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(809, 16, 'Signallampen/LED\'s - prüfen', 'radio', NULL, NULL, 31, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(810, 16, 'Sichtprüfung der Mess-/Anzeigeninstrumente/Sicherungen - prüfen', 'radio', NULL, NULL, 32, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(811, 16, 'Kühlwasservorwärmung - prüfen', 'radio', NULL, NULL, 33, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(812, 16, 'Zu- und Abluftjalousie - prüfen', 'radio', NULL, NULL, 34, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(813, 16, 'El. Leitungen/Anschlüsse auf Festsitz bzw. allg. Funktion/Beschädigung - prüfen', 'radio', NULL, NULL, 35, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(814, 16, 'Generator auf Verschmutzung, Beschädigung, Korrosion (Spannungsfreiheit sicherstellen) - prüfen', 'radio', NULL, NULL, 36, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(815, 16, 'Sicherheitseinrichtung - Schaltfunktion der Übertemperatur ohne Sensor - prüfen', 'radio', NULL, NULL, 37, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(816, 16, 'Sicherheitseinrichtung - Öldruckmangel - prüfen', 'radio', NULL, NULL, 38, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(817, 16, 'Sicherheitseinrichtung - Kühlwasserstand Mangel - prüfen', 'radio', NULL, NULL, 39, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(818, 16, 'Sicherheitseinrichtung - Kühlwassertemperaturüberwachung - prüfen', 'radio', NULL, NULL, 40, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(819, 16, 'Batteriespannung (V) - messen', 'number', NULL, NULL, 41, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(820, 16, 'Batterie Füllstand/Säuregehalt - prüfen/messen', 'radio', NULL, NULL, 42, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(821, 16, 'Batterie Ladespannung vom Ladegerät (V) - messen', 'number', NULL, NULL, 43, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(822, 16, 'Spannungseinbruch beim Start (V) - messen', 'number', NULL, NULL, 44, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(823, 16, 'Funktion Anlasser - prüfen', 'radio', NULL, NULL, 45, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(824, 16, 'Funktion Lichtmaschine Spannung (V) - prüfen/messen', 'number', NULL, NULL, 46, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(825, 16, 'Netzspannung L1 (V)', 'number', NULL, NULL, 47, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(826, 16, 'Netzspannung L2 (V)', 'number', NULL, NULL, 48, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(827, 16, 'Netzspannung L3 (V)', 'number', NULL, NULL, 49, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(828, 16, 'Netzspannung Frequenz (Hz)', 'number', NULL, NULL, 50, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(829, 16, 'Generator Leerlaufspannung L1 (V)', 'number', NULL, NULL, 51, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(830, 16, 'Generator Leerlaufspannung L2 (V)', 'number', NULL, NULL, 52, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(831, 16, 'Generator Leerlaufspannung L3 (V)', 'number', NULL, NULL, 53, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(832, 16, 'Generator Leerlaufspannung Frequenz (Hz)', 'number', NULL, NULL, 54, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(833, 16, 'HINWEIS: Pos. 24 – Pos. 37: Durchführung nur durch Betreiber!', 'textarea', NULL, NULL, 70, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(834, 16, 'Funktion des Fehlerstromschutzschalters (Prüftaste) - prüfen', 'radio', NULL, NULL, 71, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(835, 16, 'Probelauf ohne Last, wenn mit Last nicht möglich - durchführen', 'radio', NULL, NULL, 72, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(836, 16, 'Probelauf unter Last - kW', 'number', NULL, NULL, 73, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(837, 16, 'Probelauf unter Last - kVA/cos', 'number', NULL, NULL, 74, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(838, 16, 'Probelauf unter Last - L1 Spannung (V)', 'number', NULL, NULL, 75, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(839, 16, 'Probelauf unter Last - L1 Strom (A)', 'number', NULL, NULL, 76, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(840, 16, 'Probelauf unter Last - L2 Spannung (V)', 'number', NULL, NULL, 77, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(841, 16, 'Probelauf unter Last - L2 Strom (A)', 'number', NULL, NULL, 78, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(842, 16, 'Probelauf unter Last - L3 Spannung (V)', 'number', NULL, NULL, 79, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(843, 16, 'Probelauf unter Last - L3 Strom (A)', 'number', NULL, NULL, 80, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(844, 16, 'Probelauf unter Last - Frequenz (Hz)', 'number', NULL, NULL, 81, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(845, 16, 'Fehlstarts - Anzahl Versuche', 'number', NULL, NULL, 82, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(846, 16, 'Fehlermeldung Batterieunterspannung (V) - prüfen', 'number', NULL, NULL, 83, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(847, 16, 'Startladefunktion - prüfen', 'radio', NULL, NULL, 84, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(848, 16, 'Problemlose Synchronisation - prüfen', 'radio', NULL, NULL, 85, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(849, 16, 'Netzausfalltest - Übernahme (sec)', 'number', NULL, NULL, 86, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(850, 16, 'Netzausfalltest - Rückschaltzeit (sec)', 'number', NULL, NULL, 87, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(851, 16, 'Netzausfalltest - Nachlauf (sec)', 'number', NULL, NULL, 88, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(852, 16, 'Sprinklerbetrieb - Übernahme (sec)', 'number', NULL, NULL, 89, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(853, 16, 'Sprinklerbetrieb - Nachlauf (sec)', 'number', NULL, NULL, 90, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(854, 16, 'Netzausfall im Probebetrieb - prüfen', 'radio', NULL, NULL, 91, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(855, 16, 'Netzausfall im Netzparallelbetrieb - prüfen', 'radio', NULL, NULL, 92, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(856, 16, 'Netzausfall im Sprinklerbetrieb - prüfen', 'radio', NULL, NULL, 93, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(857, 16, 'Netzkuppelschalter - prüfen', 'radio', NULL, NULL, 94, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(858, 16, 'Generatorkuppelschalter - prüfen', 'radio', NULL, NULL, 95, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(859, 16, 'Durchführung der Tests gemäß Pos. 24 - Pos. 37 auf Wunsch des Anlagenverantwortlichen nicht durchgeführt', 'textarea', NULL, NULL, 96, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(860, 16, 'Tests nicht durchgeführt - Datum', 'date', NULL, NULL, 97, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(861, 16, 'Tests nicht durchgeführt - Unterschrift', 'text', NULL, NULL, 98, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(862, 16, 'Alles, was wegen Wartung abgeklemmt/abgeschaltet wurde, wieder in Ursprungszustand versetzen - durchführen', 'radio', NULL, NULL, 99, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(863, 16, 'Zusätzlicher Prüfpunkt 40', 'radio', NULL, NULL, 100, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(864, 16, 'Zusätzlicher Prüfpunkt 41', 'radio', NULL, NULL, 101, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(865, 16, 'Zusätzlicher Prüfpunkt 42', 'radio', NULL, NULL, 102, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(866, 16, 'Zusätzlicher Prüfpunkt 43', 'radio', NULL, NULL, 103, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(867, 16, 'Bemerkungen', 'textarea', NULL, NULL, 150, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(868, 16, 'Verwendetes Material 1 - Bezeichnung/Artikel', 'text', NULL, NULL, 160, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(869, 16, 'Verwendetes Material 1 - Hersteller/Sorte', 'text', NULL, NULL, 161, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(870, 16, 'Verwendetes Material 1 - Anzahl', 'number', NULL, NULL, 162, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(871, 16, 'Verwendetes Material 2 - Bezeichnung/Artikel', 'text', NULL, NULL, 163, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(872, 16, 'Verwendetes Material 2 - Hersteller/Sorte', 'text', NULL, NULL, 164, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(873, 16, 'Verwendetes Material 2 - Anzahl', 'number', NULL, NULL, 165, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(874, 16, 'Verwendetes Material 3 - Bezeichnung/Artikel', 'text', NULL, NULL, 166, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(875, 16, 'Verwendetes Material 3 - Hersteller/Sorte', 'text', NULL, NULL, 167, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(876, 16, 'Verwendetes Material 3 - Anzahl', 'number', NULL, NULL, 168, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(877, 16, 'Verwendetes Material 4 - Bezeichnung/Artikel', 'text', NULL, NULL, 169, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(878, 16, 'Verwendetes Material 4 - Hersteller/Sorte', 'text', NULL, NULL, 170, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(879, 16, 'Verwendetes Material 4 - Anzahl', 'number', NULL, NULL, 171, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(880, 16, 'Verwendetes Material 5 - Bezeichnung/Artikel', 'text', NULL, NULL, 172, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(881, 16, 'Verwendetes Material 5 - Hersteller/Sorte', 'text', NULL, NULL, 173, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(882, 16, 'Verwendetes Material 5 - Anzahl', 'number', NULL, NULL, 174, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(883, 16, 'Auftraggeber - Name', 'text', NULL, NULL, 200, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(884, 16, 'Auftraggeber - Datum/Unterschrift', 'date', NULL, NULL, 201, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(885, 16, 'Auftragnehmer - Name', 'text', NULL, NULL, 202, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(886, 16, 'Auftragnehmer - Datum/Unterschrift', 'date', NULL, NULL, 203, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:09:01'),
	(887, 17, 'Anlage', 'text', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(888, 17, 'Standort', 'text', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(889, 17, 'Type', 'text', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(890, 17, 'Serien Nr.', 'text', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(891, 17, 'Projekt', 'text', NULL, NULL, 5, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(892, 17, 'Auftraggeber', 'text', NULL, NULL, 6, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(893, 17, 'Leistung kVA', 'number', NULL, NULL, 20, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(894, 17, 'Freigegebene Leistung kVA', 'number', NULL, NULL, 21, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(895, 17, 'Cos Phi', 'number', NULL, NULL, 22, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(896, 17, 'Leistung PRP kW', 'number', NULL, NULL, 23, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(897, 17, 'Leistung LTP kW', 'number', NULL, NULL, 24, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(898, 17, 'Leistung CON kW', 'number', NULL, NULL, 25, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(899, 17, 'Nennspannung (V)', 'number', NULL, NULL, 26, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(900, 17, 'Nennstrom (A)', 'number', NULL, NULL, 27, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(901, 17, 'Funktion - Inselbetrieb - Ja', 'radio', NULL, NULL, 40, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(902, 17, 'Funktion - Inselbetrieb - OK', 'radio', NULL, NULL, 41, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(903, 17, 'Funktion - Parallelbetrieb - Ja', 'radio', NULL, NULL, 42, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(904, 17, 'Funktion - Parallelbetrieb - OK', 'radio', NULL, NULL, 43, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(905, 17, 'Funktion - Lastübernahme - Ja', 'radio', NULL, NULL, 44, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(906, 17, 'Funktion - Lastübernahme - OK', 'radio', NULL, NULL, 45, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(907, 17, 'Funktion - Notstrombetrieb - Ja', 'radio', NULL, NULL, 46, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(908, 17, 'Funktion - Notstrombetrieb - OK', 'radio', NULL, NULL, 47, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(909, 17, 'Funktion - Spitzenlast - Ja', 'radio', NULL, NULL, 48, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(910, 17, 'Funktion - Spitzenlast - OK', 'radio', NULL, NULL, 49, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(911, 17, 'Funktion - Aggregate Parallel - Ja', 'radio', NULL, NULL, 50, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(912, 17, 'Funktion - Aggregate Parallel - OK', 'radio', NULL, NULL, 51, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(913, 17, 'Funktion - Powermanagement - Ja', 'radio', NULL, NULL, 52, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(914, 17, 'Funktion - Powermanagement - OK', 'radio', NULL, NULL, 53, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(915, 17, 'Ausrüstung - Motorvorheizung - Ja', 'radio', NULL, NULL, 70, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(916, 17, 'Ausrüstung - Motorvorheizung - OK', 'radio', NULL, NULL, 71, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(917, 17, 'Ausrüstung - Motorvorheizung - Leistung (W)', 'number', NULL, NULL, 72, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(918, 17, 'Ausrüstung - Tankheizung - Ja', 'radio', NULL, NULL, 73, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(919, 17, 'Ausrüstung - Tankheizung - OK', 'radio', NULL, NULL, 74, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(920, 17, 'Ausrüstung - Tankheizung - Leistung (W)', 'number', NULL, NULL, 75, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(921, 17, 'Ausrüstung - Raumheizung - Ja', 'radio', NULL, NULL, 76, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(922, 17, 'Ausrüstung - Raumheizung - OK', 'radio', NULL, NULL, 77, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(923, 17, 'Ausrüstung - Raumheizung - Leistung (W)', 'number', NULL, NULL, 78, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(924, 17, 'Ausrüstung - Zuluft Klappe - Ja', 'radio', NULL, NULL, 79, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(925, 17, 'Ausrüstung - Zuluft Klappe - OK', 'radio', NULL, NULL, 80, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(926, 17, 'Ausrüstung - Abluft Klappe - Ja', 'radio', NULL, NULL, 81, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(927, 17, 'Ausrüstung - Abluft Klappe - OK', 'radio', NULL, NULL, 82, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(928, 17, 'Ausrüstung - Tankpumpe - Ja', 'radio', NULL, NULL, 83, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(929, 17, 'Ausrüstung - Tankpumpe - OK', 'radio', NULL, NULL, 84, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(930, 17, 'Ausrüstung - Kraftstoffpumpe - Ja', 'radio', NULL, NULL, 85, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(931, 17, 'Ausrüstung - Kraftstoffpumpe - OK', 'radio', NULL, NULL, 86, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(932, 17, 'Ausrüstung - Zuluft Lüfter - Ja', 'radio', NULL, NULL, 87, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(933, 17, 'Ausrüstung - Zuluft Lüfter - OK', 'radio', NULL, NULL, 88, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(934, 17, 'Ausrüstung - Abluft Lüfter - Ja', 'radio', NULL, NULL, 89, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(935, 17, 'Ausrüstung - Abluft Lüfter - OK', 'radio', NULL, NULL, 90, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(936, 17, 'Ausrüstung - Rauminstallation - Ja', 'radio', NULL, NULL, 91, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(937, 17, 'Ausrüstung - Rauminstallation - OK', 'radio', NULL, NULL, 92, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(938, 17, 'Eingang Analog - Tagestank - Ja', 'radio', NULL, NULL, 110, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(939, 17, 'Eingang Analog - Tagestank - OK', 'radio', NULL, NULL, 111, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(940, 17, 'Eingang Analog - Tagestank Meldung Res. - Ja', 'radio', NULL, NULL, 112, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(941, 17, 'Eingang Analog - Tagestank Meldung Res. - OK', 'radio', NULL, NULL, 113, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(942, 17, 'Eingang Analog - Tagestank Meldung Res. (%)', 'number', NULL, NULL, 114, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(943, 17, 'Eingang Analog - Tagestank Meldung leer - Ja', 'radio', NULL, NULL, 115, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(944, 17, 'Eingang Analog - Tagestank Meldung leer - OK', 'radio', NULL, NULL, 116, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(945, 17, 'Eingang Analog - Tagestank Meldung leer (%)', 'number', NULL, NULL, 117, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(946, 17, 'Eingang Analog - Vorratstank - Ja', 'radio', NULL, NULL, 118, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(947, 17, 'Eingang Analog - Vorratstank - OK', 'radio', NULL, NULL, 119, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(948, 17, 'Eingang Analog - Vorratstank Meldung Res. - Ja', 'radio', NULL, NULL, 120, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(949, 17, 'Eingang Analog - Vorratstank Meldung Res. - OK', 'radio', NULL, NULL, 121, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(950, 17, 'Eingang Analog - Vorratstank Meldung Res. (%)', 'number', NULL, NULL, 122, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(951, 17, 'Eingang Analog - Vorratstank Meldung leer - Ja', 'radio', NULL, NULL, 123, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(952, 17, 'Eingang Analog - Vorratstank Meldung leer - OK', 'radio', NULL, NULL, 124, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(953, 17, 'Eingang Analog - Vorratstank Meldung leer (%)', 'number', NULL, NULL, 125, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(954, 17, 'Eingang Analog - Motortemperatur - Ja', 'radio', NULL, NULL, 126, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(955, 17, 'Eingang Analog - Motortemperatur - OK', 'radio', NULL, NULL, 127, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(956, 17, 'Eingang Analog - Meldung Motortemperatur - Ja', 'radio', NULL, NULL, 128, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(957, 17, 'Eingang Analog - Meldung Motortemperatur - OK', 'radio', NULL, NULL, 129, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(958, 17, 'Eingang Analog - Öldruck - Ja', 'radio', NULL, NULL, 130, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(959, 17, 'Eingang Analog - Öldruck - OK', 'radio', NULL, NULL, 131, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(960, 17, 'Eingang Analog - Meldung Öldruck - Ja', 'radio', NULL, NULL, 132, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(961, 17, 'Eingang Analog - Meldung Öldruck - OK', 'radio', NULL, NULL, 133, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(962, 17, 'Eingang Analog - Sonstiges 1 - Bezeichnung', 'text', NULL, NULL, 134, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(963, 17, 'Eingang Analog - Sonstiges 1 - Ja', 'radio', NULL, NULL, 135, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(964, 17, 'Eingang Analog - Sonstiges 1 - OK', 'radio', NULL, NULL, 136, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(965, 17, 'Eingang Digital - Sprinkler - Ja', 'radio', NULL, NULL, 150, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(966, 17, 'Eingang Digital - Sprinkler - OK', 'radio', NULL, NULL, 151, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(967, 17, 'Eingang Digital - Kraftstoffhahn - Ja', 'radio', NULL, NULL, 152, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(968, 17, 'Eingang Digital - Kraftstoffhahn - OK', 'radio', NULL, NULL, 153, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(969, 17, 'Eingang Digital - Leckage Tank - Ja', 'radio', NULL, NULL, 154, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(970, 17, 'Eingang Digital - Leckage Tank - OK', 'radio', NULL, NULL, 155, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(971, 17, 'Eingang Digital - Leckage Raum - Ja', 'radio', NULL, NULL, 156, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(972, 17, 'Eingang Digital - Leckage Raum - OK', 'radio', NULL, NULL, 157, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(973, 17, 'Eingang Digital - Wassermangel - Ja', 'radio', NULL, NULL, 158, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(974, 17, 'Eingang Digital - Wassermangel - OK', 'radio', NULL, NULL, 159, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(975, 17, 'Eingang Digital - Sicherungsfall - Ja', 'radio', NULL, NULL, 160, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(976, 17, 'Eingang Digital - Sicherungsfall - OK', 'radio', NULL, NULL, 161, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(977, 17, 'Eingang Digital - Sonstiges 1 - Bezeichnung', 'text', NULL, NULL, 162, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(978, 17, 'Eingang Digital - Sonstiges 1 - Ja', 'radio', NULL, NULL, 163, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(979, 17, 'Eingang Digital - Sonstiges 1 - OK', 'radio', NULL, NULL, 164, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(980, 17, 'Eingang Digital - Sonstiges 2 - Bezeichnung', 'text', NULL, NULL, 165, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(981, 17, 'Eingang Digital - Sonstiges 2 - Ja', 'radio', NULL, NULL, 166, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(982, 17, 'Eingang Digital - Sonstiges 2 - OK', 'radio', NULL, NULL, 167, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(983, 17, 'Eingang Digital - Sonstiges 3 - Bezeichnung', 'text', NULL, NULL, 168, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(984, 17, 'Eingang Digital - Sonstiges 3 - Ja', 'radio', NULL, NULL, 169, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(985, 17, 'Eingang Digital - Sonstiges 3 - OK', 'radio', NULL, NULL, 170, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(986, 17, 'Ausgang - Bereit - Ja', 'radio', NULL, NULL, 190, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(987, 17, 'Ausgang - Bereit - OK', 'radio', NULL, NULL, 191, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(988, 17, 'Ausgang - Quittierte Alarm - Ja', 'radio', NULL, NULL, 192, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(989, 17, 'Ausgang - Quittierte Alarm - OK', 'radio', NULL, NULL, 193, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(990, 17, 'Ausgang - Sammelstörung - Ja', 'radio', NULL, NULL, 194, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(991, 17, 'Ausgang - Sammelstörung - OK', 'radio', NULL, NULL, 195, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(992, 17, 'Ausgang - Run - Ja', 'radio', NULL, NULL, 196, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(993, 17, 'Ausgang - Run - OK', 'radio', NULL, NULL, 197, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(994, 17, 'Ausgang - Start - Ja', 'radio', NULL, NULL, 198, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(995, 17, 'Ausgang - Start - OK', 'radio', NULL, NULL, 199, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(996, 17, 'Ausgang - Leckage - Ja', 'radio', NULL, NULL, 200, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(997, 17, 'Ausgang - Leckage - OK', 'radio', NULL, NULL, 201, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(998, 17, 'Ausgang - Tank leer - Ja', 'radio', NULL, NULL, 202, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(999, 17, 'Ausgang - Tank leer - OK', 'radio', NULL, NULL, 203, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1000, 17, 'Ausgang - GS Ein - Ja', 'radio', NULL, NULL, 204, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1001, 17, 'Ausgang - GS Ein - OK', 'radio', NULL, NULL, 205, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1002, 17, 'Ausgang - NS Ein - Ja', 'radio', NULL, NULL, 206, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1003, 17, 'Ausgang - NS Ein - OK', 'radio', NULL, NULL, 207, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1004, 17, 'Ausgang - Sonstiges 1 - Bezeichnung', 'text', NULL, NULL, 208, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1005, 17, 'Ausgang - Sonstiges 1 - Ja', 'radio', NULL, NULL, 209, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1006, 17, 'Ausgang - Sonstiges 1 - OK', 'radio', NULL, NULL, 210, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1007, 17, 'Ausgang - Sonstiges 2 - Bezeichnung', 'text', NULL, NULL, 211, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1008, 17, 'Ausgang - Sonstiges 2 - Ja', 'radio', NULL, NULL, 212, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1009, 17, 'Ausgang - Sonstiges 2 - OK', 'radio', NULL, NULL, 213, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1010, 17, 'Ausgang - Sonstiges 3 - Bezeichnung', 'text', NULL, NULL, 214, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1011, 17, 'Ausgang - Sonstiges 3 - Ja', 'radio', NULL, NULL, 215, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1012, 17, 'Ausgang - Sonstiges 3 - OK', 'radio', NULL, NULL, 216, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1013, 17, 'Schaltelement - Anlage Sperren - Ja', 'radio', NULL, NULL, 230, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1014, 17, 'Schaltelement - Anlage Sperren - OK', 'radio', NULL, NULL, 231, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1015, 17, 'Schaltelement - Last Lauf parallel - Ja', 'radio', NULL, NULL, 232, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1016, 17, 'Schaltelement - Last Lauf parallel - OK', 'radio', NULL, NULL, 233, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1017, 17, 'Schaltelement - Last Lauf Lastübergabe - Ja', 'radio', NULL, NULL, 234, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1018, 17, 'Schaltelement - Last Lauf Lastübergabe - OK', 'radio', NULL, NULL, 235, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1019, 17, 'Schaltelement - Testnetzausfall - Ja', 'radio', NULL, NULL, 236, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1020, 17, 'Schaltelement - Testnetzausfall - OK', 'radio', NULL, NULL, 237, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1021, 17, 'Schaltelement - Tankpumpe Ein - Ja', 'radio', NULL, NULL, 238, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1022, 17, 'Schaltelement - Tankpumpe Ein - OK', 'radio', NULL, NULL, 239, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1023, 17, 'Schaltelement - Not-Aus 1 - Ja', 'radio', NULL, NULL, 240, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1024, 17, 'Schaltelement - Not-Aus 1 - OK', 'radio', NULL, NULL, 241, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1025, 17, 'Schaltelement - Not-Aus 2 - Ja', 'radio', NULL, NULL, 242, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1026, 17, 'Schaltelement - Not-Aus 2 - OK', 'radio', NULL, NULL, 243, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1027, 17, 'Zeiten - Netzausfall (s)', 'number', NULL, NULL, 260, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1028, 17, 'Zeiten - Netzrückkehr (s)', 'number', NULL, NULL, 261, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1029, 17, 'Zeiten - Nachlauf (s)', 'number', NULL, NULL, 262, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1030, 17, 'Zeiten - Anzahl Start (Stk)', 'number', NULL, NULL, 263, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1031, 17, 'Zeiten Sprinkler - Netzausfall (s)', 'number', NULL, NULL, 264, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1032, 17, 'Zeiten Sprinkler - Netzrückkehr (s)', 'number', NULL, NULL, 265, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1033, 17, 'Zeiten Sprinkler - Nachlauf (s)', 'number', NULL, NULL, 266, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1034, 17, 'Zeiten Sprinkler - Anzahl Start (Stk)', 'number', NULL, NULL, 267, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1035, 17, 'Prüfung - Rschl - Ja', 'radio', NULL, NULL, 280, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1036, 17, 'Prüfung - Rschl - OK', 'radio', NULL, NULL, 281, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1037, 17, 'Prüfung - Isolation - Ja', 'radio', NULL, NULL, 282, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1038, 17, 'Prüfung - Isolation - OK', 'radio', NULL, NULL, 283, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1039, 17, 'Prüfung - FI - Ja', 'radio', NULL, NULL, 284, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1040, 17, 'Prüfung - FI - OK', 'radio', NULL, NULL, 285, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1041, 17, 'Prüfung - Schutzleiter - Ja', 'radio', NULL, NULL, 286, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1042, 17, 'Prüfung - Schutzleiter - OK', 'radio', NULL, NULL, 287, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1043, 17, 'Prüfung - Last - Ja', 'radio', NULL, NULL, 288, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1044, 17, 'Prüfung - Last - OK', 'radio', NULL, NULL, 289, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1045, 17, 'Prüfung - Stoßbelastung - Ja', 'radio', NULL, NULL, 290, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1046, 17, 'Prüfung - Stoßbelastung - OK', 'radio', NULL, NULL, 291, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1047, 17, 'Motorschutz - Kühlwassertemperatur - Ja', 'radio', NULL, NULL, 310, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1048, 17, 'Motorschutz - Kühlwassertemperatur - OK', 'radio', NULL, NULL, 311, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1049, 17, 'Motorschutz - Ladelufttemperatur - Ja', 'radio', NULL, NULL, 312, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1050, 17, 'Motorschutz - Ladelufttemperatur - OK', 'radio', NULL, NULL, 313, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1051, 17, 'Motorschutz - Öldruck - Ja', 'radio', NULL, NULL, 314, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1052, 17, 'Motorschutz - Öldruck - OK', 'radio', NULL, NULL, 315, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1053, 17, 'Motorschutz - Schutz ü. ECU - Ja', 'radio', NULL, NULL, 316, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1054, 17, 'Motorschutz - Schutz ü. ECU - OK', 'radio', NULL, NULL, 317, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1055, 17, 'Motorschutz - Kühlwassermangel - Ja', 'radio', NULL, NULL, 318, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1056, 17, 'Motorschutz - Kühlwassermangel - OK', 'radio', NULL, NULL, 319, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1057, 17, 'Motorschutz - Öltemperatur - Ja', 'radio', NULL, NULL, 320, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1058, 17, 'Motorschutz - Öltemperatur - OK', 'radio', NULL, NULL, 321, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1059, 17, 'Motorschutz - Überdrehzahl - Ja', 'radio', NULL, NULL, 322, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1060, 17, 'Motorschutz - Überdrehzahl - OK', 'radio', NULL, NULL, 323, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:07'),
	(1061, 17, 'Generatorschutz - Rückleistung - Ja', 'radio', NULL, NULL, 340, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1062, 17, 'Generatorschutz - Rückleistung - OK', 'radio', NULL, NULL, 341, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1063, 17, 'Generatorschutz - Überspannung - Ja', 'radio', NULL, NULL, 342, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1064, 17, 'Generatorschutz - Überspannung - OK', 'radio', NULL, NULL, 343, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1065, 17, 'Generatorschutz - Überfrequenz - Ja', 'radio', NULL, NULL, 344, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1066, 17, 'Generatorschutz - Überfrequenz - OK', 'radio', NULL, NULL, 345, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1067, 17, 'Generatorschutz - Überstrom - Ja', 'radio', NULL, NULL, 346, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1068, 17, 'Generatorschutz - Überstrom - OK', 'radio', NULL, NULL, 347, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1069, 17, 'Generatorschutz - Asymmetrie U - Ja', 'radio', NULL, NULL, 348, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1070, 17, 'Generatorschutz - Asymmetrie U - OK', 'radio', NULL, NULL, 349, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1071, 17, 'Generatorschutz - Phasenfolge - Ja', 'radio', NULL, NULL, 350, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1072, 17, 'Generatorschutz - Phasenfolge - OK', 'radio', NULL, NULL, 351, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1073, 17, 'Generatorschutz - Überlast - Ja', 'radio', NULL, NULL, 352, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1074, 17, 'Generatorschutz - Überlast - OK', 'radio', NULL, NULL, 353, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1075, 17, 'Generatorschutz - Unterspannung - Ja', 'radio', NULL, NULL, 354, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1076, 17, 'Generatorschutz - Unterspannung - OK', 'radio', NULL, NULL, 355, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1077, 17, 'Generatorschutz - Unterfrequenz - Ja', 'radio', NULL, NULL, 356, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1078, 17, 'Generatorschutz - Unterfrequenz - OK', 'radio', NULL, NULL, 357, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1079, 17, 'Generatorschutz - Kurzschluss - Ja', 'radio', NULL, NULL, 358, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1080, 17, 'Generatorschutz - Kurzschluss - OK', 'radio', NULL, NULL, 359, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1081, 17, 'Generatorschutz - Asymmetrie I - Ja', 'radio', NULL, NULL, 360, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1082, 17, 'Generatorschutz - Asymmetrie I - OK', 'radio', NULL, NULL, 361, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1083, 17, 'Generator Schalter - Schütz - Ja', 'radio', NULL, NULL, 380, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1084, 17, 'Generator Schalter - Schütz - OK', 'radio', NULL, NULL, 381, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1085, 17, 'Generator Schalter - Schalter - Ja', 'radio', NULL, NULL, 382, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1086, 17, 'Generator Schalter - Schalter - OK', 'radio', NULL, NULL, 383, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1087, 17, 'Generator Schalter - Motorisiert - Ja', 'radio', NULL, NULL, 384, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1088, 17, 'Generator Schalter - Motorisiert - OK', 'radio', NULL, NULL, 385, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1089, 17, 'Generator Schalter - ATS - Ja', 'radio', NULL, NULL, 386, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1090, 17, 'Generator Schalter - ATS - OK', 'radio', NULL, NULL, 387, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1091, 17, 'Generator Schalter - Extern - Ja', 'radio', NULL, NULL, 388, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1092, 17, 'Generator Schalter - Extern - OK', 'radio', NULL, NULL, 389, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1093, 17, 'Generator Schalter - Leistung (A)', 'number', NULL, NULL, 390, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1094, 17, 'Generator Schalter - Drehfeld', 'text', NULL, NULL, 391, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1095, 17, 'Netz Schalter - Schütz - Ja', 'radio', NULL, NULL, 392, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1096, 17, 'Netz Schalter - Schütz - OK', 'radio', NULL, NULL, 393, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1097, 17, 'Netz Schalter - Schalter - Ja', 'radio', NULL, NULL, 394, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1098, 17, 'Netz Schalter - Schalter - OK', 'radio', NULL, NULL, 395, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1099, 17, 'Netz Schalter - Motorisiert - Ja', 'radio', NULL, NULL, 396, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1100, 17, 'Netz Schalter - Motorisiert - OK', 'radio', NULL, NULL, 397, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1101, 17, 'Netz Schalter - ATS - Ja', 'radio', NULL, NULL, 398, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1102, 17, 'Netz Schalter - ATS - OK', 'radio', NULL, NULL, 399, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1103, 17, 'Netz Schalter - Extern - Ja', 'radio', NULL, NULL, 400, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1104, 17, 'Netz Schalter - Extern - OK', 'radio', NULL, NULL, 401, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1105, 17, 'Netz Schalter - Leistung (A)', 'number', NULL, NULL, 402, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1106, 17, 'Netz Schalter - Drehfeld', 'text', NULL, NULL, 403, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1107, 17, 'Netzschutz - Netzfehler 1ph - Ja', 'radio', NULL, NULL, 420, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1108, 17, 'Netzschutz - Netzfehler 1ph - OK', 'radio', NULL, NULL, 421, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1109, 17, 'Netzschutz - Netzfehler 3ph - Ja', 'radio', NULL, NULL, 422, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1110, 17, 'Netzschutz - Netzfehler 3ph - OK', 'radio', NULL, NULL, 423, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1111, 17, 'Netzschutz - Überspannung - Ja', 'radio', NULL, NULL, 424, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1112, 17, 'Netzschutz - Überspannung - OK', 'radio', NULL, NULL, 425, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1113, 17, 'Netzschutz - Überspannung - Wert', 'text', NULL, NULL, 426, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1114, 17, 'Netzschutz - Unterspannung - Ja', 'radio', NULL, NULL, 427, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1115, 17, 'Netzschutz - Unterspannung - OK', 'radio', NULL, NULL, 428, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1116, 17, 'Netzschutz - Unterspannung - Wert', 'text', NULL, NULL, 429, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1117, 17, 'Netzschutz - Überfrequenz - Ja', 'radio', NULL, NULL, 430, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1118, 17, 'Netzschutz - Überfrequenz - OK', 'radio', NULL, NULL, 431, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1119, 17, 'Netzschutz - Überfrequenz - Wert', 'text', NULL, NULL, 432, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1120, 17, 'Netzschutz - Unterfrequenz - Ja', 'radio', NULL, NULL, 433, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1121, 17, 'Netzschutz - Unterfrequenz - OK', 'radio', NULL, NULL, 434, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1122, 17, 'Netzschutz - Unterfrequenz - Wert', 'text', NULL, NULL, 435, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1123, 17, 'Netzschutz - Vektorsprung - Ja', 'radio', NULL, NULL, 436, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1124, 17, 'Netzschutz - Vektorsprung - OK', 'radio', NULL, NULL, 437, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1125, 17, 'Netzschutz - Vektorsprung - Wert', 'text', NULL, NULL, 438, 0, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1126, 17, 'Anlage freigegeben (Die geprüfte Maschine/Anlage erfüllt die Anforderungen gem. DIN VDE / EN 60204-1 IEC 204-1)', 'radio', NULL, NULL, 500, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1127, 17, 'Prüfer - Name', 'text', NULL, NULL, 501, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1128, 17, 'Prüfer - Unterschrift/Datum', 'date', NULL, NULL, 502, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1129, 17, 'Technisch Verantwortlicher - Name', 'text', NULL, NULL, 503, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1130, 17, 'Technisch Verantwortlicher - Unterschrift/Datum', 'date', NULL, NULL, 504, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:16:08'),
	(1131, 18, 'Reflektoren Nr. 1', 'checkbox', NULL, NULL, 1, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:26'),
	(1132, 18, 'Reflektoren Nr. 2', 'checkbox', NULL, NULL, 2, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:26'),
	(1133, 18, 'Reflektoren Nr. 3', 'checkbox', NULL, NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:27'),
	(1134, 18, 'Reflektoren Nr. 4', 'checkbox', NULL, NULL, 4, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:27'),
	(1135, 18, 'Begrenzungsleuchte Fahrwerk Nr. 1', 'checkbox', NULL, NULL, 5, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:27'),
	(1136, 18, 'Begrenzungsleuchte Fahrwerk Nr. 2', 'checkbox', NULL, NULL, 6, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:28'),
	(1137, 18, 'Begrenzungsleuchte Fahrwerk Nr. 3', 'checkbox', NULL, NULL, 7, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:28'),
	(1138, 18, 'Begrenzungsleuchte Fahrwerk Nr. 4', 'checkbox', NULL, NULL, 8, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:28'),
	(1139, 18, 'Begrenzungsleuchte Dach (FW/STW) Nr. 1', 'checkbox', NULL, NULL, 9, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:28'),
	(1140, 18, 'Begrenzungsleuchte Dach (FW/STW) Nr. 2', 'checkbox', NULL, NULL, 10, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:29'),
	(1141, 18, 'Begrenzungsleuchte Dach (FW/STW) Nr. 3', 'checkbox', NULL, NULL, 11, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:29'),
	(1142, 18, 'Begrenzungsleuchte Dach (FW/STW) Nr. 4', 'checkbox', NULL, NULL, 12, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:29'),
	(1143, 18, 'Rückfahrlampen unbeschädigt', 'checkbox', NULL, NULL, 13, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:29'),
	(1144, 18, 'Nummerntafelbeleuchtung unbeschädigt', 'checkbox', NULL, NULL, 14, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:30'),
	(1145, 18, 'Kabelverschraubungen und Kabel - unbeschädigt', 'checkbox', NULL, NULL, 15, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:30'),
	(1146, 18, 'Kabelverschraubungen und Kabel - sauber angeschlossen', 'checkbox', NULL, NULL, 16, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:30'),
	(1147, 18, '12/24 Volt Converter Box - unbeschädigt', 'checkbox', NULL, NULL, 17, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:30'),
	(1148, 18, '12/24 Volt Converter Box - sauber angeschlossen', 'checkbox', NULL, NULL, 18, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:31'),
	(1149, 18, 'Kabelanschlussdosen 12V', 'checkbox', NULL, NULL, 19, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:31'),
	(1150, 18, 'Kabelanschlussdosen 24V', 'checkbox', NULL, NULL, 20, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:31'),
	(1151, 18, 'Kabelhalter an Deichsel - korrekt montiert', 'checkbox', NULL, NULL, 21, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:31'),
	(1152, 18, 'Kabelhalter an Deichsel - funktionell', 'checkbox', NULL, NULL, 22, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:32'),
	(1153, 18, 'Kabelhalter an Deichsel - passt für Schloss', 'checkbox', NULL, NULL, 23, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:32'),
	(1154, 18, 'Seil Handbremse / Seil Bremse unbeschädigt', 'checkbox', NULL, NULL, 24, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:32'),
	(1155, 18, 'Schutztülle Auflaufbremse korrekter Sitz und Befestigung', 'checkbox', NULL, NULL, 25, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:32'),
	(1156, 18, 'Kotflügel - unbeschädigt', 'checkbox', NULL, NULL, 26, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:33'),
	(1157, 18, 'Kotflügel - korrekt montiert', 'checkbox', NULL, NULL, 27, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:33'),
	(1158, 18, 'Bremskeile - 2 Stück vorhanden', 'checkbox', NULL, NULL, 28, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:33'),
	(1159, 18, 'Splinte Deichsel gesteckt', 'checkbox', NULL, NULL, 29, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:34'),
	(1160, 18, 'Rost und Scharfe Kanten - nicht vorhanden', 'checkbox', NULL, NULL, 30, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:34'),
	(1161, 18, 'Abgasklappe in Fahrtrichtung', 'checkbox', NULL, NULL, 31, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:34'),
	(1162, 18, 'Kennzeichenhalter Typ BGG', 'checkbox', NULL, NULL, 32, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:34'),
	(1163, 18, 'Beleuchtung 12V - Blinker links', 'checkbox', NULL, NULL, 33, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:35'),
	(1164, 18, 'Beleuchtung 12V - Blinker rechts', 'checkbox', NULL, NULL, 34, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:35'),
	(1165, 18, 'Beleuchtung 12V - Bremslicht links', 'checkbox', NULL, NULL, 35, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:35'),
	(1166, 18, 'Beleuchtung 12V - Bremslicht rechts', 'checkbox', NULL, NULL, 36, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:35'),
	(1167, 18, 'Beleuchtung 12V - Standlicht links', 'checkbox', NULL, NULL, 37, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:36'),
	(1168, 18, 'Beleuchtung 12V - Standlicht rechts', 'checkbox', NULL, NULL, 38, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:36'),
	(1169, 18, 'Beleuchtung 12V - Umfeld unten (V/H) links', 'checkbox', NULL, NULL, 39, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:36'),
	(1170, 18, 'Beleuchtung 12V - Umfeld unten (V/H) rechts', 'checkbox', NULL, NULL, 40, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:37'),
	(1171, 18, 'Beleuchtung 12V - Nebelrückleuchte', 'checkbox', NULL, NULL, 41, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:37'),
	(1172, 18, 'Beleuchtung 12V - Rückfahrleuchte', 'checkbox', NULL, NULL, 42, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:37'),
	(1173, 18, 'Beleuchtung 12V - Nummernschildbeleuchtung', 'checkbox', NULL, NULL, 43, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:37'),
	(1174, 18, 'Beleuchtung 12V - Umfeld oben (CP) links', 'checkbox', NULL, NULL, 44, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:38'),
	(1175, 18, 'Beleuchtung 12V - Umfeld oben (CP) rechts', 'checkbox', NULL, NULL, 45, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:38'),
	(1176, 18, 'Beleuchtung 12V - Blinker oben (H/CP) links', 'checkbox', NULL, NULL, 46, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:38'),
	(1177, 18, 'Beleuchtung 12V - Blinker oben (H/CP) rechts', 'checkbox', NULL, NULL, 47, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:38'),
	(1178, 18, 'Beleuchtung 12V - Bremslicht oben (CP) links', 'checkbox', NULL, NULL, 48, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:39'),
	(1179, 18, 'Beleuchtung 12V - Bremslicht oben (CP) rechts', 'checkbox', NULL, NULL, 49, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:39'),
	(1180, 18, 'Beleuchtung 12V - Warnblink oben (V/H) links', 'checkbox', NULL, NULL, 50, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:39'),
	(1181, 18, 'Beleuchtung 12V - Warnblink oben (V/H) rechts', 'checkbox', NULL, NULL, 51, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:40'),
	(1182, 18, 'Beleuchtung 24V - Blinker links', 'checkbox', NULL, NULL, 52, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:40'),
	(1183, 18, 'Beleuchtung 24V - Blinker rechts', 'checkbox', NULL, NULL, 53, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:40'),
	(1184, 18, 'Beleuchtung 24V - Bremslicht links', 'checkbox', NULL, NULL, 54, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:41'),
	(1185, 18, 'Beleuchtung 24V - Bremslicht rechts', 'checkbox', NULL, NULL, 55, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:41'),
	(1186, 18, 'Beleuchtung 24V - Standlicht links', 'checkbox', NULL, NULL, 56, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:41'),
	(1187, 18, 'Beleuchtung 24V - Standlicht rechts', 'checkbox', NULL, NULL, 57, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:41'),
	(1188, 18, 'Beleuchtung 24V - Umfeld unten (V/H) links', 'checkbox', NULL, NULL, 58, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:42'),
	(1189, 18, 'Beleuchtung 24V - Umfeld unten (V/H) rechts', 'checkbox', NULL, NULL, 59, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:42'),
	(1190, 18, 'Beleuchtung 24V - Nebelrückleuchte', 'checkbox', NULL, NULL, 60, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:42'),
	(1191, 18, 'Beleuchtung 24V - Rückfahrleuchte', 'checkbox', NULL, NULL, 61, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:42'),
	(1192, 18, 'Beleuchtung 24V - Nummernschildbeleuchtung', 'checkbox', NULL, NULL, 62, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:43'),
	(1193, 18, 'Beleuchtung 24V - Umfeld oben (CP) links', 'checkbox', NULL, NULL, 63, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:43'),
	(1194, 18, 'Beleuchtung 24V - Umfeld oben (CP) rechts', 'checkbox', NULL, NULL, 64, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:43'),
	(1195, 18, 'Beleuchtung 24V - Blinker oben (H/CP) links', 'checkbox', NULL, NULL, 65, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:43'),
	(1196, 18, 'Beleuchtung 24V - Blinker oben (H/CP) rechts', 'checkbox', NULL, NULL, 66, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:44'),
	(1197, 18, 'Beleuchtung 24V - Bremslicht oben (CP) links', 'checkbox', NULL, NULL, 67, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:44'),
	(1198, 18, 'Beleuchtung 24V - Bremslicht oben (CP) rechts', 'checkbox', NULL, NULL, 68, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:44'),
	(1199, 18, 'Beleuchtung 24V - Warnblink oben (V/H) links', 'checkbox', NULL, NULL, 69, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:44'),
	(1200, 18, 'Beleuchtung 24V - Warnblink oben (V/H) rechts', 'checkbox', NULL, NULL, 70, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:45'),
	(1201, 18, 'Stützrad - unbeschädigt', 'checkbox', NULL, NULL, 71, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:45'),
	(1202, 18, 'Stützrad - Ein/Ausfahren', 'checkbox', NULL, NULL, 72, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:45'),
	(1203, 18, 'Stütze vorne links - unbeschädigt', 'checkbox', NULL, NULL, 73, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:45'),
	(1204, 18, 'Stütze vorne links - Ein/Ausfahren', 'checkbox', NULL, NULL, 74, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:46'),
	(1205, 18, 'Stütze vorne rechts - unbeschädigt', 'checkbox', NULL, NULL, 75, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:46'),
	(1206, 18, 'Stütze vorne rechts - Ein/Ausfahren', 'checkbox', NULL, NULL, 76, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:46'),
	(1207, 18, 'Stütze hinten links - unbeschädigt', 'checkbox', NULL, NULL, 77, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:47'),
	(1208, 18, 'Stütze hinten links - Ein/Ausfahren', 'checkbox', NULL, NULL, 78, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:47'),
	(1209, 18, 'Stütze hinten rechts - unbeschädigt', 'checkbox', NULL, NULL, 79, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:47'),
	(1210, 18, 'Stütze hinten rechts - Ein/Ausfahren', 'checkbox', NULL, NULL, 80, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:47'),
	(1211, 18, 'Reifendruck', 'measurement', NULL, NULL, 81, 1, 0, 0, 'Bar', NULL, NULL, '2025-10-30 05:25:48'),
	(1212, 18, 'Handbremse - Position', 'checkbox', NULL, NULL, 82, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:48'),
	(1213, 18, 'Handbremse - Funktion', 'checkbox', NULL, NULL, 83, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:48'),
	(1214, 18, 'Auflaufbremse - Funktion', 'checkbox', NULL, NULL, 84, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:48'),
	(1215, 18, 'Auflaufbremse - % Einschub bis Block', 'measurement', NULL, NULL, 85, 1, 0, 0, 'mm', NULL, NULL, '2025-10-30 05:25:48'),
	(1216, 18, 'Freilauf Räder - ohne Geräusche', 'checkbox', NULL, NULL, 86, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:49'),
	(1217, 18, 'Fahrprobe (Temperatur Verhalten)', 'checkbox', NULL, NULL, 87, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:49'),
	(1218, 18, 'Radbolzen nachgezogen', 'checkbox', NULL, NULL, 88, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:49'),
	(1219, 18, 'Fahrtrichtungszeiger montiert', 'checkbox', NULL, NULL, 89, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:49'),
	(1220, 18, 'Aufkleber Reifenprüfung 50 km', 'checkbox', NULL, NULL, 90, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:49'),
	(1221, 18, 'Aufkleber Neuanlage', 'checkbox', NULL, NULL, 91, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:50'),
	(1222, 18, 'Aufkleber Prüfung vor Start', 'checkbox', NULL, NULL, 92, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:50'),
	(1223, 18, 'Geschwindigkeitsaufkleber', 'radio', NULL, NULL, 93, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:50'),
	(1224, 18, 'Druckaufkleber Reifen', 'checkbox', NULL, NULL, 94, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:50'),
	(1225, 18, 'Belastbarkeit Kotflügel (FW/STW)', 'checkbox', NULL, NULL, 95, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:51'),
	(1226, 18, 'Bügel Dach nicht kranbar', 'checkbox', NULL, NULL, 96, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:51'),
	(1227, 18, 'Stützlast - leer', 'measurement', NULL, NULL, 97, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:51'),
	(1228, 18, 'Stützlast - voll', 'measurement', NULL, NULL, 98, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:51'),
	(1229, 18, 'Achslast links - leer', 'measurement', NULL, NULL, 99, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:51'),
	(1230, 18, 'Achslast links - voll', 'measurement', NULL, NULL, 100, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:51'),
	(1231, 18, 'Achslast rechts - leer', 'measurement', NULL, NULL, 101, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:52'),
	(1232, 18, 'Achslast rechts - voll', 'measurement', NULL, NULL, 102, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:52'),
	(1233, 18, 'Gesamtgewicht - leer', 'measurement', NULL, NULL, 103, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:52'),
	(1234, 18, 'Gesamtgewicht - voll', 'measurement', NULL, NULL, 104, 1, 0, 0, 'kg', NULL, NULL, '2025-10-30 05:25:52'),
	(1235, 18, 'Prüfer', 'text', NULL, NULL, 105, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:52'),
	(1236, 18, 'Datum', 'date', NULL, NULL, 106, 1, 0, 0, NULL, NULL, NULL, '2025-10-30 05:25:52');

-- Exportiere Struktur von Tabelle d044f149.maintenance_checklist_item_options
CREATE TABLE IF NOT EXISTS `maintenance_checklist_item_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checklist_item_id` int(11) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `option_label` varchar(255) NOT NULL,
  `option_order` int(11) DEFAULT 0,
  `is_default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `checklist_item_id` (`checklist_item_id`),
  KEY `idx_checklist_item` (`checklist_item_id`),
  CONSTRAINT `fk_item_options_item` FOREIGN KEY (`checklist_item_id`) REFERENCES `maintenance_checklist_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1789 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_checklist_item_options: ~1.153 rows (ungefähr)
INSERT INTO `maintenance_checklist_item_options` (`id`, `checklist_item_id`, `option_value`, `option_label`, `option_order`, `is_default`) VALUES
	(113, 100, 'ja_io', 'Ja / i.O.', 1, 0),
	(114, 100, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(115, 100, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(116, 100, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(117, 101, 'ja_io', 'Ja / i.O.', 1, 0),
	(118, 101, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(119, 101, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(120, 101, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(121, 102, 'ja_io', 'Ja / i.O.', 1, 0),
	(122, 102, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(123, 102, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(124, 102, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(125, 106, 'ja_io', 'Ja / i.O.', 1, 0),
	(126, 106, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(127, 106, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(128, 106, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(129, 107, 'ja_io', 'Ja / i.O.', 1, 0),
	(130, 107, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(131, 107, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(132, 107, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(133, 108, 'ja_io', 'Ja / i.O.', 1, 0),
	(134, 108, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(135, 108, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(136, 108, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(137, 109, 'ja_io', 'Ja / i.O.', 1, 0),
	(138, 109, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(139, 109, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(140, 109, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(141, 110, 'ja_io', 'Ja / i.O.', 1, 0),
	(142, 110, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(143, 110, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(144, 110, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(145, 111, 'ja_io', 'Ja / i.O.', 1, 0),
	(146, 111, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(147, 111, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(148, 111, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(149, 112, 'ja_io', 'Ja / i.O.', 1, 0),
	(150, 112, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(151, 112, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(152, 112, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(153, 113, 'ja_io', 'Ja / i.O.', 1, 0),
	(154, 113, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(155, 113, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(156, 113, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(157, 114, 'ja_io', 'Ja / i.O.', 1, 0),
	(158, 114, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(159, 114, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(160, 114, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(161, 115, 'ja_io', 'Ja / i.O.', 1, 0),
	(162, 115, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(163, 115, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(164, 115, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(165, 116, 'ja_io', 'Ja / i.O.', 1, 0),
	(166, 116, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(167, 116, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(168, 116, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(169, 117, 'ja_io', 'Ja / i.O.', 1, 0),
	(170, 117, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(171, 117, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(172, 117, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(173, 118, 'ja_io', 'Ja / i.O.', 1, 0),
	(174, 118, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(175, 118, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(176, 118, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(177, 120, 'ja_io', 'Ja / i.O.', 1, 0),
	(178, 120, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(179, 120, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(180, 120, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(181, 123, 'ja_io', 'Ja / i.O.', 1, 0),
	(182, 123, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(183, 123, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(184, 123, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(185, 133, 'ja_io', 'Ja / i.O.', 1, 0),
	(186, 133, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(187, 133, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(188, 133, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(189, 134, 'ja_io', 'Ja / i.O.', 1, 0),
	(190, 134, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(191, 134, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(192, 134, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(193, 146, 'ja_io', 'Ja / i.O.', 1, 0),
	(194, 146, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(195, 146, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(196, 146, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(197, 147, 'ja_io', 'Ja / i.O.', 1, 0),
	(198, 147, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(199, 147, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(200, 147, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(201, 153, 'ja_io', 'Ja / i.O.', 1, 0),
	(202, 153, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(203, 153, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(204, 153, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(205, 154, 'ja_io', 'Ja / i.O.', 1, 0),
	(206, 154, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(207, 154, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(208, 154, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(209, 155, 'ja_io', 'Ja / i.O.', 1, 0),
	(210, 155, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(211, 155, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(212, 155, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(213, 156, 'ja_io', 'Ja / i.O.', 1, 0),
	(214, 156, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(215, 156, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(216, 156, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(217, 157, 'ja_io', 'Ja / i.O.', 1, 0),
	(218, 157, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(219, 157, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(220, 157, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(221, 159, 'ja_io', 'Ja / i.O.', 1, 0),
	(222, 159, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(223, 159, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(224, 159, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(225, 169, 'ja_io', 'Ja / i.O.', 1, 1),
	(226, 170, 'ja_io', 'Ja / i.O.', 1, 1),
	(227, 173, 'ja_io', 'Ja / i.O.', 1, 1),
	(228, 174, 'ja_io', 'Ja / i.O.', 1, 1),
	(229, 175, 'ja_io', 'Ja / i.O.', 1, 1),
	(232, 169, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(233, 170, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(234, 173, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(235, 174, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(236, 175, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(239, 169, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(240, 170, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(241, 173, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(242, 174, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(243, 175, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(246, 177, 'ja', 'Ja', 1, 1),
	(247, 178, 'ja', 'Ja', 1, 1),
	(249, 177, 'nein', 'Nein', 2, 0),
	(250, 178, 'nein', 'Nein', 2, 0),
	(252, 180, 'ja', 'Ja', 1, 1),
	(253, 181, 'ja', 'Ja', 1, 1),
	(254, 182, 'ja', 'Ja', 1, 1),
	(255, 183, 'ja', 'Ja', 1, 1),
	(256, 184, 'ja', 'Ja', 1, 1),
	(257, 185, 'ja', 'Ja', 1, 1),
	(258, 186, 'ja', 'Ja', 1, 1),
	(259, 180, 'nein', 'Nein', 2, 0),
	(260, 181, 'nein', 'Nein', 2, 0),
	(261, 182, 'nein', 'Nein', 2, 0),
	(262, 183, 'nein', 'Nein', 2, 0),
	(263, 184, 'nein', 'Nein', 2, 0),
	(264, 185, 'nein', 'Nein', 2, 0),
	(265, 186, 'nein', 'Nein', 2, 0),
	(266, 188, 'ja', 'Ja', 1, 1),
	(267, 189, 'ja', 'Ja', 1, 1),
	(268, 190, 'ja', 'Ja', 1, 1),
	(269, 188, 'nein', 'Nein', 2, 0),
	(270, 189, 'nein', 'Nein', 2, 0),
	(271, 190, 'nein', 'Nein', 2, 0),
	(272, 192, 'erfuellt', 'Erfüllt', 1, 1),
	(273, 193, 'erfuellt', 'Erfüllt', 1, 1),
	(275, 192, 'nicht_erfuellt', 'Nicht erfüllt', 2, 0),
	(276, 193, 'nicht_erfuellt', 'Nicht erfüllt', 2, 0),
	(278, 477, 'ja', 'Ja', 1, 0),
	(279, 482, 'ja', 'Ja', 1, 0),
	(280, 485, 'ja', 'Ja', 1, 0),
	(281, 488, 'ja', 'Ja', 1, 0),
	(282, 491, 'ja', 'Ja', 1, 0),
	(283, 502, 'ja', 'Ja', 1, 0),
	(284, 505, 'ja', 'Ja', 1, 0),
	(285, 508, 'ja', 'Ja', 1, 0),
	(286, 511, 'ja', 'Ja', 1, 0),
	(287, 514, 'ja', 'Ja', 1, 0),
	(288, 517, 'ja', 'Ja', 1, 0),
	(289, 520, 'ja', 'Ja', 1, 0),
	(290, 523, 'ja', 'Ja', 1, 0),
	(291, 526, 'ja', 'Ja', 1, 0),
	(292, 529, 'ja', 'Ja', 1, 0),
	(293, 532, 'ja', 'Ja', 1, 0),
	(294, 535, 'ja', 'Ja', 1, 0),
	(295, 538, 'ja', 'Ja', 1, 0),
	(296, 541, 'ja', 'Ja', 1, 0),
	(297, 544, 'ja', 'Ja', 1, 0),
	(298, 547, 'ja', 'Ja', 1, 0),
	(299, 550, 'ja', 'Ja', 1, 0),
	(300, 553, 'ja', 'Ja', 1, 0),
	(301, 556, 'ja', 'Ja', 1, 0),
	(302, 559, 'ja', 'Ja', 1, 0),
	(303, 575, 'ja', 'Ja', 1, 0),
	(304, 583, 'ja', 'Ja', 1, 0),
	(305, 585, 'ja', 'Ja', 1, 0),
	(306, 587, 'ja', 'Ja', 1, 0),
	(307, 589, 'ja', 'Ja', 1, 0),
	(308, 591, 'ja', 'Ja', 1, 0),
	(309, 593, 'ja', 'Ja', 1, 0),
	(310, 595, 'ja', 'Ja', 1, 0),
	(311, 597, 'ja', 'Ja', 1, 0),
	(312, 599, 'ja', 'Ja', 1, 0),
	(313, 601, 'ja', 'Ja', 1, 0),
	(314, 603, 'ja', 'Ja', 1, 0),
	(341, 477, 'nein', 'Nein', 2, 0),
	(342, 482, 'nein', 'Nein', 2, 0),
	(343, 485, 'nein', 'Nein', 2, 0),
	(344, 488, 'nein', 'Nein', 2, 0),
	(345, 491, 'nein', 'Nein', 2, 0),
	(346, 502, 'nein', 'Nein', 2, 0),
	(347, 505, 'nein', 'Nein', 2, 0),
	(348, 508, 'nein', 'Nein', 2, 0),
	(349, 511, 'nein', 'Nein', 2, 0),
	(350, 514, 'nein', 'Nein', 2, 0),
	(351, 517, 'nein', 'Nein', 2, 0),
	(352, 520, 'nein', 'Nein', 2, 0),
	(353, 523, 'nein', 'Nein', 2, 0),
	(354, 526, 'nein', 'Nein', 2, 0),
	(355, 529, 'nein', 'Nein', 2, 0),
	(356, 532, 'nein', 'Nein', 2, 0),
	(357, 535, 'nein', 'Nein', 2, 0),
	(358, 538, 'nein', 'Nein', 2, 0),
	(359, 541, 'nein', 'Nein', 2, 0),
	(360, 544, 'nein', 'Nein', 2, 0),
	(361, 547, 'nein', 'Nein', 2, 0),
	(362, 550, 'nein', 'Nein', 2, 0),
	(363, 553, 'nein', 'Nein', 2, 0),
	(364, 556, 'nein', 'Nein', 2, 0),
	(365, 559, 'nein', 'Nein', 2, 0),
	(366, 575, 'nein', 'Nein', 2, 0),
	(367, 583, 'nein', 'Nein', 2, 0),
	(368, 585, 'nein', 'Nein', 2, 0),
	(369, 587, 'nein', 'Nein', 2, 0),
	(370, 589, 'nein', 'Nein', 2, 0),
	(371, 591, 'nein', 'Nein', 2, 0),
	(372, 593, 'nein', 'Nein', 2, 0),
	(373, 595, 'nein', 'Nein', 2, 0),
	(374, 597, 'nein', 'Nein', 2, 0),
	(375, 599, 'nein', 'Nein', 2, 0),
	(376, 601, 'nein', 'Nein', 2, 0),
	(377, 603, 'nein', 'Nein', 2, 0),
	(404, 562, 'ja_io', 'Ja / i.O.', 1, 0),
	(405, 564, 'ja_io', 'Ja / i.O.', 1, 0),
	(406, 566, 'ja_io', 'Ja / i.O.', 1, 0),
	(407, 568, 'ja_io', 'Ja / i.O.', 1, 0),
	(408, 569, 'ja_io', 'Ja / i.O.', 1, 0),
	(409, 571, 'ja_io', 'Ja / i.O.', 1, 0),
	(410, 572, 'ja_io', 'Ja / i.O.', 1, 0),
	(411, 573, 'ja_io', 'Ja / i.O.', 1, 0),
	(412, 574, 'ja_io', 'Ja / i.O.', 1, 0),
	(413, 576, 'ja_io', 'Ja / i.O.', 1, 0),
	(414, 577, 'ja_io', 'Ja / i.O.', 1, 0),
	(415, 578, 'ja_io', 'Ja / i.O.', 1, 0),
	(416, 579, 'ja_io', 'Ja / i.O.', 1, 0),
	(417, 580, 'ja_io', 'Ja / i.O.', 1, 0),
	(418, 581, 'ja_io', 'Ja / i.O.', 1, 0),
	(419, 582, 'ja_io', 'Ja / i.O.', 1, 0),
	(420, 605, 'ja_io', 'Ja / i.O.', 1, 0),
	(421, 606, 'ja_io', 'Ja / i.O.', 1, 0),
	(435, 562, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(436, 564, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(437, 566, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(438, 568, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(439, 569, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(440, 571, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(441, 572, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(442, 573, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(443, 574, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(444, 576, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(445, 577, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(446, 578, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(447, 579, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(448, 580, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(449, 581, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(450, 582, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(451, 605, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(452, 606, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(466, 562, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(467, 564, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(468, 566, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(469, 568, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(470, 569, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(471, 571, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(472, 572, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(473, 573, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(474, 574, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(475, 576, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(476, 577, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(477, 578, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(478, 579, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(479, 580, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(480, 581, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(481, 582, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(482, 605, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(483, 606, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(497, 562, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(498, 564, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(499, 566, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(500, 568, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(501, 569, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(502, 571, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(503, 572, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(504, 573, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(505, 574, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(506, 576, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(507, 577, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(508, 578, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(509, 579, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(510, 580, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(511, 581, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(512, 582, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(513, 605, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(514, 606, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(528, 630, 'diesel', 'Diesel', 1, 1),
	(529, 630, 'benzin', 'Benzin', 2, 0),
	(530, 630, 'gas', 'Gas', 3, 0),
	(531, 631, 'ja_io', 'Ja / i.O.', 1, 0),
	(532, 632, 'ja_io', 'Ja / i.O.', 1, 0),
	(533, 633, 'ja_io', 'Ja / i.O.', 1, 0),
	(534, 639, 'ja_io', 'Ja / i.O.', 1, 0),
	(535, 640, 'ja_io', 'Ja / i.O.', 1, 0),
	(536, 641, 'ja_io', 'Ja / i.O.', 1, 0),
	(537, 642, 'ja_io', 'Ja / i.O.', 1, 0),
	(538, 643, 'ja_io', 'Ja / i.O.', 1, 0),
	(539, 644, 'ja_io', 'Ja / i.O.', 1, 0),
	(540, 645, 'ja_io', 'Ja / i.O.', 1, 0),
	(541, 646, 'ja_io', 'Ja / i.O.', 1, 0),
	(542, 647, 'ja_io', 'Ja / i.O.', 1, 0),
	(543, 648, 'ja_io', 'Ja / i.O.', 1, 0),
	(544, 649, 'ja_io', 'Ja / i.O.', 1, 0),
	(545, 650, 'ja_io', 'Ja / i.O.', 1, 0),
	(546, 651, 'ja_io', 'Ja / i.O.', 1, 0),
	(547, 652, 'ja_io', 'Ja / i.O.', 1, 0),
	(548, 653, 'ja_io', 'Ja / i.O.', 1, 0),
	(549, 654, 'ja_io', 'Ja / i.O.', 1, 0),
	(550, 655, 'ja_io', 'Ja / i.O.', 1, 0),
	(551, 656, 'ja_io', 'Ja / i.O.', 1, 0),
	(552, 658, 'ja_io', 'Ja / i.O.', 1, 0),
	(553, 659, 'ja_io', 'Ja / i.O.', 1, 0),
	(554, 660, 'ja_io', 'Ja / i.O.', 1, 0),
	(555, 661, 'ja_io', 'Ja / i.O.', 1, 0),
	(556, 662, 'ja_io', 'Ja / i.O.', 1, 0),
	(557, 663, 'ja_io', 'Ja / i.O.', 1, 0),
	(558, 664, 'ja_io', 'Ja / i.O.', 1, 0),
	(559, 665, 'ja_io', 'Ja / i.O.', 1, 0),
	(560, 666, 'ja_io', 'Ja / i.O.', 1, 0),
	(561, 667, 'ja_io', 'Ja / i.O.', 1, 0),
	(562, 668, 'ja_io', 'Ja / i.O.', 1, 0),
	(563, 672, 'ja_io', 'Ja / i.O.', 1, 0),
	(564, 675, 'ja_io', 'Ja / i.O.', 1, 0),
	(565, 676, 'ja_io', 'Ja / i.O.', 1, 0),
	(594, 631, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(595, 632, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(596, 633, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(597, 639, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(598, 640, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(599, 641, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(600, 642, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(601, 643, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(602, 644, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(603, 645, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(604, 646, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(605, 647, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(606, 648, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(607, 649, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(608, 650, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(609, 651, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(610, 652, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(611, 653, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(612, 654, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(613, 655, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(614, 656, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(615, 658, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(616, 659, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(617, 660, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(618, 661, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(619, 662, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(620, 663, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(621, 664, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(622, 665, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(623, 666, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(624, 667, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(625, 668, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(626, 672, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(627, 675, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(628, 676, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(657, 631, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(658, 632, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(659, 633, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(660, 639, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(661, 640, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(662, 641, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(663, 642, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(664, 643, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(665, 644, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(666, 645, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(667, 646, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(668, 647, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(669, 648, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(670, 649, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(671, 650, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(672, 651, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(673, 652, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(674, 653, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(675, 654, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(676, 655, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(677, 656, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(678, 658, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(679, 659, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(680, 660, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(681, 661, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(682, 662, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(683, 663, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(684, 664, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(685, 665, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(686, 666, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(687, 667, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(688, 668, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(689, 672, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(690, 675, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(691, 676, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(720, 631, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(721, 632, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(722, 633, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(723, 639, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(724, 640, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(725, 641, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(726, 642, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(727, 643, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(728, 644, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(729, 645, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(730, 646, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(731, 647, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(732, 648, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(733, 649, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(734, 650, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(735, 651, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(736, 652, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(737, 653, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(738, 654, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(739, 655, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(740, 656, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(741, 658, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(742, 659, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(743, 660, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(744, 661, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(745, 662, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(746, 663, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(747, 664, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(748, 665, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(749, 666, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(750, 667, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(751, 668, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(752, 672, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(753, 675, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(754, 676, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(783, 701, 'a', 'A', 1, 0),
	(784, 701, 'b', 'B', 2, 0),
	(785, 701, 'c', 'C', 3, 0),
	(786, 703, 'wiederholungspruefung', 'Wiederholungsprüfung', 1, 1),
	(787, 703, 'instandsetzung', 'Instandsetzung/Reparatur', 2, 0),
	(788, 735, 'a', 'A', 1, 1),
	(789, 735, 'f', 'F', 2, 0),
	(790, 735, 'b', 'B', 3, 0),
	(791, 735, 'b_plus', 'B+', 4, 0),
	(792, 731, 'ohne_iso', 'ohne Isolationsüberwachung', 1, 1),
	(793, 731, 'mit_iso', 'mit Isolationsüberwachung', 2, 0),
	(794, 760, 'g1', 'G1', 1, 1),
	(795, 763, 'g1', 'G1', 1, 1),
	(797, 760, 'g2', 'G2', 2, 0),
	(798, 763, 'g2', 'G2', 2, 0),
	(800, 760, 'g3', 'G3', 3, 0),
	(801, 763, 'g3', 'G3', 3, 0),
	(803, 704, 'ja', 'Ja', 1, 0),
	(804, 705, 'ja', 'Ja', 1, 0),
	(805, 706, 'ja', 'Ja', 1, 0),
	(806, 707, 'ja', 'Ja', 1, 0),
	(807, 708, 'ja', 'Ja', 1, 0),
	(808, 709, 'ja', 'Ja', 1, 0),
	(809, 710, 'ja', 'Ja', 1, 0),
	(810, 711, 'ja', 'Ja', 1, 0),
	(811, 712, 'ja', 'Ja', 1, 0),
	(812, 713, 'ja', 'Ja', 1, 0),
	(813, 714, 'ja', 'Ja', 1, 0),
	(814, 715, 'ja', 'Ja', 1, 0),
	(815, 716, 'ja', 'Ja', 1, 0),
	(816, 717, 'ja', 'Ja', 1, 0),
	(817, 721, 'ja', 'Ja', 1, 0),
	(818, 724, 'ja', 'Ja', 1, 0),
	(819, 725, 'ja', 'Ja', 1, 0),
	(820, 726, 'ja', 'Ja', 1, 0),
	(821, 727, 'ja', 'Ja', 1, 0),
	(822, 730, 'ja', 'Ja', 1, 0),
	(823, 734, 'ja', 'Ja', 1, 0),
	(824, 739, 'ja', 'Ja', 1, 0),
	(825, 742, 'ja', 'Ja', 1, 0),
	(826, 745, 'ja', 'Ja', 1, 0),
	(827, 748, 'ja', 'Ja', 1, 0),
	(828, 751, 'ja', 'Ja', 1, 0),
	(829, 754, 'ja', 'Ja', 1, 0),
	(830, 755, 'ja', 'Ja', 1, 0),
	(831, 756, 'ja', 'Ja', 1, 0),
	(832, 757, 'ja', 'Ja', 1, 0),
	(833, 758, 'ja', 'Ja', 1, 0),
	(834, 762, 'ja', 'Ja', 1, 0),
	(835, 765, 'ja', 'Ja', 1, 0),
	(836, 766, 'ja', 'Ja', 1, 0),
	(837, 767, 'ja', 'Ja', 1, 0),
	(838, 769, 'ja', 'Ja', 1, 0),
	(839, 771, 'ja', 'Ja', 1, 0),
	(840, 772, 'ja', 'Ja', 1, 0),
	(866, 704, 'nein', 'Nein', 2, 0),
	(867, 705, 'nein', 'Nein', 2, 0),
	(868, 706, 'nein', 'Nein', 2, 0),
	(869, 707, 'nein', 'Nein', 2, 0),
	(870, 708, 'nein', 'Nein', 2, 0),
	(871, 709, 'nein', 'Nein', 2, 0),
	(872, 710, 'nein', 'Nein', 2, 0),
	(873, 711, 'nein', 'Nein', 2, 0),
	(874, 712, 'nein', 'Nein', 2, 0),
	(875, 713, 'nein', 'Nein', 2, 0),
	(876, 714, 'nein', 'Nein', 2, 0),
	(877, 715, 'nein', 'Nein', 2, 0),
	(878, 716, 'nein', 'Nein', 2, 0),
	(879, 717, 'nein', 'Nein', 2, 0),
	(880, 721, 'nein', 'Nein', 2, 0),
	(881, 724, 'nein', 'Nein', 2, 0),
	(882, 725, 'nein', 'Nein', 2, 0),
	(883, 726, 'nein', 'Nein', 2, 0),
	(884, 727, 'nein', 'Nein', 2, 0),
	(885, 730, 'nein', 'Nein', 2, 0),
	(886, 734, 'nein', 'Nein', 2, 0),
	(887, 739, 'nein', 'Nein', 2, 0),
	(888, 742, 'nein', 'Nein', 2, 0),
	(889, 745, 'nein', 'Nein', 2, 0),
	(890, 748, 'nein', 'Nein', 2, 0),
	(891, 751, 'nein', 'Nein', 2, 0),
	(892, 754, 'nein', 'Nein', 2, 0),
	(893, 755, 'nein', 'Nein', 2, 0),
	(894, 756, 'nein', 'Nein', 2, 0),
	(895, 757, 'nein', 'Nein', 2, 0),
	(896, 758, 'nein', 'Nein', 2, 0),
	(897, 762, 'nein', 'Nein', 2, 0),
	(898, 765, 'nein', 'Nein', 2, 0),
	(899, 766, 'nein', 'Nein', 2, 0),
	(900, 767, 'nein', 'Nein', 2, 0),
	(901, 769, 'nein', 'Nein', 2, 0),
	(902, 771, 'nein', 'Nein', 2, 0),
	(903, 772, 'nein', 'Nein', 2, 0),
	(929, 797, 'diesel', 'Diesel', 1, 1),
	(930, 797, 'benzin', 'Benzin', 2, 0),
	(931, 797, 'gas', 'Gas', 3, 0),
	(932, 798, 'ja_io', 'Ja / i.O.', 1, 0),
	(933, 799, 'ja_io', 'Ja / i.O.', 1, 0),
	(934, 800, 'ja_io', 'Ja / i.O.', 1, 0),
	(935, 806, 'ja_io', 'Ja / i.O.', 1, 0),
	(936, 807, 'ja_io', 'Ja / i.O.', 1, 0),
	(937, 808, 'ja_io', 'Ja / i.O.', 1, 0),
	(938, 809, 'ja_io', 'Ja / i.O.', 1, 0),
	(939, 810, 'ja_io', 'Ja / i.O.', 1, 0),
	(940, 811, 'ja_io', 'Ja / i.O.', 1, 0),
	(941, 812, 'ja_io', 'Ja / i.O.', 1, 0),
	(942, 813, 'ja_io', 'Ja / i.O.', 1, 0),
	(943, 814, 'ja_io', 'Ja / i.O.', 1, 0),
	(944, 815, 'ja_io', 'Ja / i.O.', 1, 0),
	(945, 816, 'ja_io', 'Ja / i.O.', 1, 0),
	(946, 817, 'ja_io', 'Ja / i.O.', 1, 0),
	(947, 818, 'ja_io', 'Ja / i.O.', 1, 0),
	(948, 820, 'ja_io', 'Ja / i.O.', 1, 0),
	(949, 823, 'ja_io', 'Ja / i.O.', 1, 0),
	(950, 834, 'ja_io', 'Ja / i.O.', 1, 0),
	(951, 835, 'ja_io', 'Ja / i.O.', 1, 0),
	(952, 847, 'ja_io', 'Ja / i.O.', 1, 0),
	(953, 848, 'ja_io', 'Ja / i.O.', 1, 0),
	(954, 854, 'ja_io', 'Ja / i.O.', 1, 0),
	(955, 855, 'ja_io', 'Ja / i.O.', 1, 0),
	(956, 856, 'ja_io', 'Ja / i.O.', 1, 0),
	(957, 857, 'ja_io', 'Ja / i.O.', 1, 0),
	(958, 858, 'ja_io', 'Ja / i.O.', 1, 0),
	(959, 862, 'ja_io', 'Ja / i.O.', 1, 0),
	(960, 863, 'ja_io', 'Ja / i.O.', 1, 0),
	(961, 864, 'ja_io', 'Ja / i.O.', 1, 0),
	(962, 865, 'ja_io', 'Ja / i.O.', 1, 0),
	(963, 866, 'ja_io', 'Ja / i.O.', 1, 0),
	(995, 798, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(996, 799, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(997, 800, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(998, 806, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(999, 807, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1000, 808, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1001, 809, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1002, 810, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1003, 811, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1004, 812, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1005, 813, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1006, 814, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1007, 815, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1008, 816, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1009, 817, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1010, 818, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1011, 820, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1012, 823, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1013, 834, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1014, 835, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1015, 847, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1016, 848, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1017, 854, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1018, 855, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1019, 856, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1020, 857, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1021, 858, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1022, 862, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1023, 863, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1024, 864, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1025, 865, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1026, 866, 'nein_nio', 'Nein / n.i.O.', 2, 0),
	(1058, 798, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1059, 799, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1060, 800, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1061, 806, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1062, 807, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1063, 808, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1064, 809, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1065, 810, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1066, 811, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1067, 812, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1068, 813, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1069, 814, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1070, 815, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1071, 816, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1072, 817, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1073, 818, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1074, 820, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1075, 823, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1076, 834, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1077, 835, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1078, 847, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1079, 848, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1080, 854, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1081, 855, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1082, 856, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1083, 857, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1084, 858, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1085, 862, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1086, 863, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1087, 864, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1088, 865, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1089, 866, 'nicht_zutreffend', 'Nicht zutreffend', 3, 0),
	(1121, 798, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1122, 799, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1123, 800, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1124, 806, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1125, 807, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1126, 808, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1127, 809, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1128, 810, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1129, 811, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1130, 812, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1131, 813, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1132, 814, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1133, 815, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1134, 816, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1135, 817, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1136, 818, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1137, 820, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1138, 823, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1139, 834, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1140, 835, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1141, 847, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1142, 848, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1143, 854, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1144, 855, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1145, 856, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1146, 857, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1147, 858, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1148, 862, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1149, 863, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1150, 864, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1151, 865, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1152, 866, 'siehe_bemerkung', 'Siehe Bemerkung', 4, 0),
	(1184, 901, 'ja', 'Ja', 1, 0),
	(1185, 902, 'ja', 'Ja', 1, 0),
	(1186, 903, 'ja', 'Ja', 1, 0),
	(1187, 904, 'ja', 'Ja', 1, 0),
	(1188, 905, 'ja', 'Ja', 1, 0),
	(1189, 906, 'ja', 'Ja', 1, 0),
	(1190, 907, 'ja', 'Ja', 1, 0),
	(1191, 908, 'ja', 'Ja', 1, 0),
	(1192, 909, 'ja', 'Ja', 1, 0),
	(1193, 910, 'ja', 'Ja', 1, 0),
	(1194, 911, 'ja', 'Ja', 1, 0),
	(1195, 912, 'ja', 'Ja', 1, 0),
	(1196, 913, 'ja', 'Ja', 1, 0),
	(1197, 914, 'ja', 'Ja', 1, 0),
	(1198, 915, 'ja', 'Ja', 1, 0),
	(1199, 916, 'ja', 'Ja', 1, 0),
	(1200, 918, 'ja', 'Ja', 1, 0),
	(1201, 919, 'ja', 'Ja', 1, 0),
	(1202, 921, 'ja', 'Ja', 1, 0),
	(1203, 922, 'ja', 'Ja', 1, 0),
	(1204, 924, 'ja', 'Ja', 1, 0),
	(1205, 925, 'ja', 'Ja', 1, 0),
	(1206, 926, 'ja', 'Ja', 1, 0),
	(1207, 927, 'ja', 'Ja', 1, 0),
	(1208, 928, 'ja', 'Ja', 1, 0),
	(1209, 929, 'ja', 'Ja', 1, 0),
	(1210, 930, 'ja', 'Ja', 1, 0),
	(1211, 931, 'ja', 'Ja', 1, 0),
	(1212, 932, 'ja', 'Ja', 1, 0),
	(1213, 933, 'ja', 'Ja', 1, 0),
	(1214, 934, 'ja', 'Ja', 1, 0),
	(1215, 935, 'ja', 'Ja', 1, 0),
	(1216, 936, 'ja', 'Ja', 1, 0),
	(1217, 937, 'ja', 'Ja', 1, 0),
	(1218, 938, 'ja', 'Ja', 1, 0),
	(1219, 939, 'ja', 'Ja', 1, 0),
	(1220, 940, 'ja', 'Ja', 1, 0),
	(1221, 941, 'ja', 'Ja', 1, 0),
	(1222, 943, 'ja', 'Ja', 1, 0),
	(1223, 944, 'ja', 'Ja', 1, 0),
	(1224, 946, 'ja', 'Ja', 1, 0),
	(1225, 947, 'ja', 'Ja', 1, 0),
	(1226, 948, 'ja', 'Ja', 1, 0),
	(1227, 949, 'ja', 'Ja', 1, 0),
	(1228, 951, 'ja', 'Ja', 1, 0),
	(1229, 952, 'ja', 'Ja', 1, 0),
	(1230, 954, 'ja', 'Ja', 1, 0),
	(1231, 955, 'ja', 'Ja', 1, 0),
	(1232, 956, 'ja', 'Ja', 1, 0),
	(1233, 957, 'ja', 'Ja', 1, 0),
	(1234, 958, 'ja', 'Ja', 1, 0),
	(1235, 959, 'ja', 'Ja', 1, 0),
	(1236, 960, 'ja', 'Ja', 1, 0),
	(1237, 961, 'ja', 'Ja', 1, 0),
	(1238, 963, 'ja', 'Ja', 1, 0),
	(1239, 964, 'ja', 'Ja', 1, 0),
	(1240, 965, 'ja', 'Ja', 1, 0),
	(1241, 966, 'ja', 'Ja', 1, 0),
	(1242, 967, 'ja', 'Ja', 1, 0),
	(1243, 968, 'ja', 'Ja', 1, 0),
	(1244, 969, 'ja', 'Ja', 1, 0),
	(1245, 970, 'ja', 'Ja', 1, 0),
	(1246, 971, 'ja', 'Ja', 1, 0),
	(1247, 972, 'ja', 'Ja', 1, 0),
	(1248, 973, 'ja', 'Ja', 1, 0),
	(1249, 974, 'ja', 'Ja', 1, 0),
	(1250, 975, 'ja', 'Ja', 1, 0),
	(1251, 976, 'ja', 'Ja', 1, 0),
	(1252, 978, 'ja', 'Ja', 1, 0),
	(1253, 979, 'ja', 'Ja', 1, 0),
	(1254, 981, 'ja', 'Ja', 1, 0),
	(1255, 982, 'ja', 'Ja', 1, 0),
	(1256, 984, 'ja', 'Ja', 1, 0),
	(1257, 985, 'ja', 'Ja', 1, 0),
	(1258, 986, 'ja', 'Ja', 1, 0),
	(1259, 987, 'ja', 'Ja', 1, 0),
	(1260, 988, 'ja', 'Ja', 1, 0),
	(1261, 989, 'ja', 'Ja', 1, 0),
	(1262, 990, 'ja', 'Ja', 1, 0),
	(1263, 991, 'ja', 'Ja', 1, 0),
	(1264, 992, 'ja', 'Ja', 1, 0),
	(1265, 993, 'ja', 'Ja', 1, 0),
	(1266, 994, 'ja', 'Ja', 1, 0),
	(1267, 995, 'ja', 'Ja', 1, 0),
	(1268, 996, 'ja', 'Ja', 1, 0),
	(1269, 997, 'ja', 'Ja', 1, 0),
	(1270, 998, 'ja', 'Ja', 1, 0),
	(1271, 999, 'ja', 'Ja', 1, 0),
	(1272, 1000, 'ja', 'Ja', 1, 0),
	(1273, 1001, 'ja', 'Ja', 1, 0),
	(1274, 1002, 'ja', 'Ja', 1, 0),
	(1275, 1003, 'ja', 'Ja', 1, 0),
	(1276, 1005, 'ja', 'Ja', 1, 0),
	(1277, 1006, 'ja', 'Ja', 1, 0),
	(1278, 1008, 'ja', 'Ja', 1, 0),
	(1279, 1009, 'ja', 'Ja', 1, 0),
	(1280, 1011, 'ja', 'Ja', 1, 0),
	(1281, 1012, 'ja', 'Ja', 1, 0),
	(1282, 1013, 'ja', 'Ja', 1, 0),
	(1283, 1014, 'ja', 'Ja', 1, 0),
	(1284, 1015, 'ja', 'Ja', 1, 0),
	(1285, 1016, 'ja', 'Ja', 1, 0),
	(1286, 1017, 'ja', 'Ja', 1, 0),
	(1287, 1018, 'ja', 'Ja', 1, 0),
	(1288, 1019, 'ja', 'Ja', 1, 0),
	(1289, 1020, 'ja', 'Ja', 1, 0),
	(1290, 1021, 'ja', 'Ja', 1, 0),
	(1291, 1022, 'ja', 'Ja', 1, 0),
	(1292, 1023, 'ja', 'Ja', 1, 0),
	(1293, 1024, 'ja', 'Ja', 1, 0),
	(1294, 1025, 'ja', 'Ja', 1, 0),
	(1295, 1026, 'ja', 'Ja', 1, 0),
	(1296, 1035, 'ja', 'Ja', 1, 0),
	(1297, 1036, 'ja', 'Ja', 1, 0),
	(1298, 1037, 'ja', 'Ja', 1, 0),
	(1299, 1038, 'ja', 'Ja', 1, 0),
	(1300, 1039, 'ja', 'Ja', 1, 0),
	(1301, 1040, 'ja', 'Ja', 1, 0),
	(1302, 1041, 'ja', 'Ja', 1, 0),
	(1303, 1042, 'ja', 'Ja', 1, 0),
	(1304, 1043, 'ja', 'Ja', 1, 0),
	(1305, 1044, 'ja', 'Ja', 1, 0),
	(1306, 1045, 'ja', 'Ja', 1, 0),
	(1307, 1046, 'ja', 'Ja', 1, 0),
	(1308, 1047, 'ja', 'Ja', 1, 0),
	(1309, 1048, 'ja', 'Ja', 1, 0),
	(1310, 1049, 'ja', 'Ja', 1, 0),
	(1311, 1050, 'ja', 'Ja', 1, 0),
	(1312, 1051, 'ja', 'Ja', 1, 0),
	(1313, 1052, 'ja', 'Ja', 1, 0),
	(1314, 1053, 'ja', 'Ja', 1, 0),
	(1315, 1054, 'ja', 'Ja', 1, 0),
	(1316, 1055, 'ja', 'Ja', 1, 0),
	(1317, 1056, 'ja', 'Ja', 1, 0),
	(1318, 1057, 'ja', 'Ja', 1, 0),
	(1319, 1058, 'ja', 'Ja', 1, 0),
	(1320, 1059, 'ja', 'Ja', 1, 0),
	(1321, 1060, 'ja', 'Ja', 1, 0),
	(1322, 1061, 'ja', 'Ja', 1, 0),
	(1323, 1062, 'ja', 'Ja', 1, 0),
	(1324, 1063, 'ja', 'Ja', 1, 0),
	(1325, 1064, 'ja', 'Ja', 1, 0),
	(1326, 1065, 'ja', 'Ja', 1, 0),
	(1327, 1066, 'ja', 'Ja', 1, 0),
	(1328, 1067, 'ja', 'Ja', 1, 0),
	(1329, 1068, 'ja', 'Ja', 1, 0),
	(1330, 1069, 'ja', 'Ja', 1, 0),
	(1331, 1070, 'ja', 'Ja', 1, 0),
	(1332, 1071, 'ja', 'Ja', 1, 0),
	(1333, 1072, 'ja', 'Ja', 1, 0),
	(1334, 1073, 'ja', 'Ja', 1, 0),
	(1335, 1074, 'ja', 'Ja', 1, 0),
	(1336, 1075, 'ja', 'Ja', 1, 0),
	(1337, 1076, 'ja', 'Ja', 1, 0),
	(1338, 1077, 'ja', 'Ja', 1, 0),
	(1339, 1078, 'ja', 'Ja', 1, 0),
	(1340, 1079, 'ja', 'Ja', 1, 0),
	(1341, 1080, 'ja', 'Ja', 1, 0),
	(1342, 1081, 'ja', 'Ja', 1, 0),
	(1343, 1082, 'ja', 'Ja', 1, 0),
	(1344, 1083, 'ja', 'Ja', 1, 0),
	(1345, 1084, 'ja', 'Ja', 1, 0),
	(1346, 1085, 'ja', 'Ja', 1, 0),
	(1347, 1086, 'ja', 'Ja', 1, 0),
	(1348, 1087, 'ja', 'Ja', 1, 0),
	(1349, 1088, 'ja', 'Ja', 1, 0),
	(1350, 1089, 'ja', 'Ja', 1, 0),
	(1351, 1090, 'ja', 'Ja', 1, 0),
	(1352, 1091, 'ja', 'Ja', 1, 0),
	(1353, 1092, 'ja', 'Ja', 1, 0),
	(1354, 1095, 'ja', 'Ja', 1, 0),
	(1355, 1096, 'ja', 'Ja', 1, 0),
	(1356, 1097, 'ja', 'Ja', 1, 0),
	(1357, 1098, 'ja', 'Ja', 1, 0),
	(1358, 1099, 'ja', 'Ja', 1, 0),
	(1359, 1100, 'ja', 'Ja', 1, 0),
	(1360, 1101, 'ja', 'Ja', 1, 0),
	(1361, 1102, 'ja', 'Ja', 1, 0),
	(1362, 1103, 'ja', 'Ja', 1, 0),
	(1363, 1104, 'ja', 'Ja', 1, 0),
	(1364, 1107, 'ja', 'Ja', 1, 0),
	(1365, 1108, 'ja', 'Ja', 1, 0),
	(1366, 1109, 'ja', 'Ja', 1, 0),
	(1367, 1110, 'ja', 'Ja', 1, 0),
	(1368, 1111, 'ja', 'Ja', 1, 0),
	(1369, 1112, 'ja', 'Ja', 1, 0),
	(1370, 1114, 'ja', 'Ja', 1, 0),
	(1371, 1115, 'ja', 'Ja', 1, 0),
	(1372, 1117, 'ja', 'Ja', 1, 0),
	(1373, 1118, 'ja', 'Ja', 1, 0),
	(1374, 1120, 'ja', 'Ja', 1, 0),
	(1375, 1121, 'ja', 'Ja', 1, 0),
	(1376, 1123, 'ja', 'Ja', 1, 0),
	(1377, 1124, 'ja', 'Ja', 1, 0),
	(1378, 1126, 'ja', 'Ja', 1, 0),
	(1439, 901, 'nein', 'Nein', 2, 0),
	(1440, 902, 'nein', 'Nein', 2, 0),
	(1441, 903, 'nein', 'Nein', 2, 0),
	(1442, 904, 'nein', 'Nein', 2, 0),
	(1443, 905, 'nein', 'Nein', 2, 0),
	(1444, 906, 'nein', 'Nein', 2, 0),
	(1445, 907, 'nein', 'Nein', 2, 0),
	(1446, 908, 'nein', 'Nein', 2, 0),
	(1447, 909, 'nein', 'Nein', 2, 0),
	(1448, 910, 'nein', 'Nein', 2, 0),
	(1449, 911, 'nein', 'Nein', 2, 0),
	(1450, 912, 'nein', 'Nein', 2, 0),
	(1451, 913, 'nein', 'Nein', 2, 0),
	(1452, 914, 'nein', 'Nein', 2, 0),
	(1453, 915, 'nein', 'Nein', 2, 0),
	(1454, 916, 'nein', 'Nein', 2, 0),
	(1455, 918, 'nein', 'Nein', 2, 0),
	(1456, 919, 'nein', 'Nein', 2, 0),
	(1457, 921, 'nein', 'Nein', 2, 0),
	(1458, 922, 'nein', 'Nein', 2, 0),
	(1459, 924, 'nein', 'Nein', 2, 0),
	(1460, 925, 'nein', 'Nein', 2, 0),
	(1461, 926, 'nein', 'Nein', 2, 0),
	(1462, 927, 'nein', 'Nein', 2, 0),
	(1463, 928, 'nein', 'Nein', 2, 0),
	(1464, 929, 'nein', 'Nein', 2, 0),
	(1465, 930, 'nein', 'Nein', 2, 0),
	(1466, 931, 'nein', 'Nein', 2, 0),
	(1467, 932, 'nein', 'Nein', 2, 0),
	(1468, 933, 'nein', 'Nein', 2, 0),
	(1469, 934, 'nein', 'Nein', 2, 0),
	(1470, 935, 'nein', 'Nein', 2, 0),
	(1471, 936, 'nein', 'Nein', 2, 0),
	(1472, 937, 'nein', 'Nein', 2, 0),
	(1473, 938, 'nein', 'Nein', 2, 0),
	(1474, 939, 'nein', 'Nein', 2, 0),
	(1475, 940, 'nein', 'Nein', 2, 0),
	(1476, 941, 'nein', 'Nein', 2, 0),
	(1477, 943, 'nein', 'Nein', 2, 0),
	(1478, 944, 'nein', 'Nein', 2, 0),
	(1479, 946, 'nein', 'Nein', 2, 0),
	(1480, 947, 'nein', 'Nein', 2, 0),
	(1481, 948, 'nein', 'Nein', 2, 0),
	(1482, 949, 'nein', 'Nein', 2, 0),
	(1483, 951, 'nein', 'Nein', 2, 0),
	(1484, 952, 'nein', 'Nein', 2, 0),
	(1485, 954, 'nein', 'Nein', 2, 0),
	(1486, 955, 'nein', 'Nein', 2, 0),
	(1487, 956, 'nein', 'Nein', 2, 0),
	(1488, 957, 'nein', 'Nein', 2, 0),
	(1489, 958, 'nein', 'Nein', 2, 0),
	(1490, 959, 'nein', 'Nein', 2, 0),
	(1491, 960, 'nein', 'Nein', 2, 0),
	(1492, 961, 'nein', 'Nein', 2, 0),
	(1493, 963, 'nein', 'Nein', 2, 0),
	(1494, 964, 'nein', 'Nein', 2, 0),
	(1495, 965, 'nein', 'Nein', 2, 0),
	(1496, 966, 'nein', 'Nein', 2, 0),
	(1497, 967, 'nein', 'Nein', 2, 0),
	(1498, 968, 'nein', 'Nein', 2, 0),
	(1499, 969, 'nein', 'Nein', 2, 0),
	(1500, 970, 'nein', 'Nein', 2, 0),
	(1501, 971, 'nein', 'Nein', 2, 0),
	(1502, 972, 'nein', 'Nein', 2, 0),
	(1503, 973, 'nein', 'Nein', 2, 0),
	(1504, 974, 'nein', 'Nein', 2, 0),
	(1505, 975, 'nein', 'Nein', 2, 0),
	(1506, 976, 'nein', 'Nein', 2, 0),
	(1507, 978, 'nein', 'Nein', 2, 0),
	(1508, 979, 'nein', 'Nein', 2, 0),
	(1509, 981, 'nein', 'Nein', 2, 0),
	(1510, 982, 'nein', 'Nein', 2, 0),
	(1511, 984, 'nein', 'Nein', 2, 0),
	(1512, 985, 'nein', 'Nein', 2, 0),
	(1513, 986, 'nein', 'Nein', 2, 0),
	(1514, 987, 'nein', 'Nein', 2, 0),
	(1515, 988, 'nein', 'Nein', 2, 0),
	(1516, 989, 'nein', 'Nein', 2, 0),
	(1517, 990, 'nein', 'Nein', 2, 0),
	(1518, 991, 'nein', 'Nein', 2, 0),
	(1519, 992, 'nein', 'Nein', 2, 0),
	(1520, 993, 'nein', 'Nein', 2, 0),
	(1521, 994, 'nein', 'Nein', 2, 0),
	(1522, 995, 'nein', 'Nein', 2, 0),
	(1523, 996, 'nein', 'Nein', 2, 0),
	(1524, 997, 'nein', 'Nein', 2, 0),
	(1525, 998, 'nein', 'Nein', 2, 0),
	(1526, 999, 'nein', 'Nein', 2, 0),
	(1527, 1000, 'nein', 'Nein', 2, 0),
	(1528, 1001, 'nein', 'Nein', 2, 0),
	(1529, 1002, 'nein', 'Nein', 2, 0),
	(1530, 1003, 'nein', 'Nein', 2, 0),
	(1531, 1005, 'nein', 'Nein', 2, 0),
	(1532, 1006, 'nein', 'Nein', 2, 0),
	(1533, 1008, 'nein', 'Nein', 2, 0),
	(1534, 1009, 'nein', 'Nein', 2, 0),
	(1535, 1011, 'nein', 'Nein', 2, 0),
	(1536, 1012, 'nein', 'Nein', 2, 0),
	(1537, 1013, 'nein', 'Nein', 2, 0),
	(1538, 1014, 'nein', 'Nein', 2, 0),
	(1539, 1015, 'nein', 'Nein', 2, 0),
	(1540, 1016, 'nein', 'Nein', 2, 0),
	(1541, 1017, 'nein', 'Nein', 2, 0),
	(1542, 1018, 'nein', 'Nein', 2, 0),
	(1543, 1019, 'nein', 'Nein', 2, 0),
	(1544, 1020, 'nein', 'Nein', 2, 0),
	(1545, 1021, 'nein', 'Nein', 2, 0),
	(1546, 1022, 'nein', 'Nein', 2, 0),
	(1547, 1023, 'nein', 'Nein', 2, 0),
	(1548, 1024, 'nein', 'Nein', 2, 0),
	(1549, 1025, 'nein', 'Nein', 2, 0),
	(1550, 1026, 'nein', 'Nein', 2, 0),
	(1551, 1035, 'nein', 'Nein', 2, 0),
	(1552, 1036, 'nein', 'Nein', 2, 0),
	(1553, 1037, 'nein', 'Nein', 2, 0),
	(1554, 1038, 'nein', 'Nein', 2, 0),
	(1555, 1039, 'nein', 'Nein', 2, 0),
	(1556, 1040, 'nein', 'Nein', 2, 0),
	(1557, 1041, 'nein', 'Nein', 2, 0),
	(1558, 1042, 'nein', 'Nein', 2, 0),
	(1559, 1043, 'nein', 'Nein', 2, 0),
	(1560, 1044, 'nein', 'Nein', 2, 0),
	(1561, 1045, 'nein', 'Nein', 2, 0),
	(1562, 1046, 'nein', 'Nein', 2, 0),
	(1563, 1047, 'nein', 'Nein', 2, 0),
	(1564, 1048, 'nein', 'Nein', 2, 0),
	(1565, 1049, 'nein', 'Nein', 2, 0),
	(1566, 1050, 'nein', 'Nein', 2, 0),
	(1567, 1051, 'nein', 'Nein', 2, 0),
	(1568, 1052, 'nein', 'Nein', 2, 0),
	(1569, 1053, 'nein', 'Nein', 2, 0),
	(1570, 1054, 'nein', 'Nein', 2, 0),
	(1571, 1055, 'nein', 'Nein', 2, 0),
	(1572, 1056, 'nein', 'Nein', 2, 0),
	(1573, 1057, 'nein', 'Nein', 2, 0),
	(1574, 1058, 'nein', 'Nein', 2, 0),
	(1575, 1059, 'nein', 'Nein', 2, 0),
	(1576, 1060, 'nein', 'Nein', 2, 0),
	(1577, 1061, 'nein', 'Nein', 2, 0),
	(1578, 1062, 'nein', 'Nein', 2, 0),
	(1579, 1063, 'nein', 'Nein', 2, 0),
	(1580, 1064, 'nein', 'Nein', 2, 0),
	(1581, 1065, 'nein', 'Nein', 2, 0),
	(1582, 1066, 'nein', 'Nein', 2, 0),
	(1583, 1067, 'nein', 'Nein', 2, 0),
	(1584, 1068, 'nein', 'Nein', 2, 0),
	(1585, 1069, 'nein', 'Nein', 2, 0),
	(1586, 1070, 'nein', 'Nein', 2, 0),
	(1587, 1071, 'nein', 'Nein', 2, 0),
	(1588, 1072, 'nein', 'Nein', 2, 0),
	(1589, 1073, 'nein', 'Nein', 2, 0),
	(1590, 1074, 'nein', 'Nein', 2, 0),
	(1591, 1075, 'nein', 'Nein', 2, 0),
	(1592, 1076, 'nein', 'Nein', 2, 0),
	(1593, 1077, 'nein', 'Nein', 2, 0),
	(1594, 1078, 'nein', 'Nein', 2, 0),
	(1595, 1079, 'nein', 'Nein', 2, 0),
	(1596, 1080, 'nein', 'Nein', 2, 0),
	(1597, 1081, 'nein', 'Nein', 2, 0),
	(1598, 1082, 'nein', 'Nein', 2, 0),
	(1599, 1083, 'nein', 'Nein', 2, 0),
	(1600, 1084, 'nein', 'Nein', 2, 0),
	(1601, 1085, 'nein', 'Nein', 2, 0),
	(1602, 1086, 'nein', 'Nein', 2, 0),
	(1603, 1087, 'nein', 'Nein', 2, 0),
	(1604, 1088, 'nein', 'Nein', 2, 0),
	(1605, 1089, 'nein', 'Nein', 2, 0),
	(1606, 1090, 'nein', 'Nein', 2, 0),
	(1607, 1091, 'nein', 'Nein', 2, 0),
	(1608, 1092, 'nein', 'Nein', 2, 0),
	(1609, 1095, 'nein', 'Nein', 2, 0),
	(1610, 1096, 'nein', 'Nein', 2, 0),
	(1611, 1097, 'nein', 'Nein', 2, 0),
	(1612, 1098, 'nein', 'Nein', 2, 0),
	(1613, 1099, 'nein', 'Nein', 2, 0),
	(1614, 1100, 'nein', 'Nein', 2, 0),
	(1615, 1101, 'nein', 'Nein', 2, 0),
	(1616, 1102, 'nein', 'Nein', 2, 0),
	(1617, 1103, 'nein', 'Nein', 2, 0),
	(1618, 1104, 'nein', 'Nein', 2, 0),
	(1619, 1107, 'nein', 'Nein', 2, 0),
	(1620, 1108, 'nein', 'Nein', 2, 0),
	(1621, 1109, 'nein', 'Nein', 2, 0),
	(1622, 1110, 'nein', 'Nein', 2, 0),
	(1623, 1111, 'nein', 'Nein', 2, 0),
	(1624, 1112, 'nein', 'Nein', 2, 0),
	(1625, 1114, 'nein', 'Nein', 2, 0),
	(1626, 1115, 'nein', 'Nein', 2, 0),
	(1627, 1117, 'nein', 'Nein', 2, 0),
	(1628, 1118, 'nein', 'Nein', 2, 0),
	(1629, 1120, 'nein', 'Nein', 2, 0),
	(1630, 1121, 'nein', 'Nein', 2, 0),
	(1631, 1123, 'nein', 'Nein', 2, 0),
	(1632, 1124, 'nein', 'Nein', 2, 0),
	(1633, 1126, 'nein', 'Nein', 2, 0),
	(1694, 1131, 'ok', 'OK', 1, 0),
	(1695, 1132, 'ok', 'OK', 1, 0),
	(1696, 1133, 'ok', 'OK', 1, 0),
	(1697, 1134, 'ok', 'OK', 1, 0),
	(1698, 1135, 'ok', 'OK', 1, 0),
	(1699, 1136, 'ok', 'OK', 1, 0),
	(1700, 1137, 'ok', 'OK', 1, 0),
	(1701, 1138, 'ok', 'OK', 1, 0),
	(1702, 1139, 'ok', 'OK', 1, 0),
	(1703, 1140, 'ok', 'OK', 1, 0),
	(1704, 1141, 'ok', 'OK', 1, 0),
	(1705, 1142, 'ok', 'OK', 1, 0),
	(1706, 1143, 'ok', 'OK', 1, 0),
	(1707, 1144, 'ok', 'OK', 1, 0),
	(1708, 1145, 'ok', 'OK', 1, 0),
	(1709, 1146, 'ok', 'OK', 1, 0),
	(1710, 1147, 'ok', 'OK', 1, 0),
	(1711, 1148, 'ok', 'OK', 1, 0),
	(1712, 1149, 'ok', 'OK', 1, 0),
	(1713, 1150, 'ok', 'OK', 1, 0),
	(1714, 1151, 'ok', 'OK', 1, 0),
	(1715, 1152, 'ok', 'OK', 1, 0),
	(1716, 1153, 'ok', 'OK', 1, 0),
	(1717, 1154, 'ok', 'OK', 1, 0),
	(1718, 1155, 'ok', 'OK', 1, 0),
	(1719, 1156, 'ok', 'OK', 1, 0),
	(1720, 1157, 'ok', 'OK', 1, 0),
	(1721, 1158, 'ok', 'OK', 1, 0),
	(1722, 1159, 'ok', 'OK', 1, 0),
	(1723, 1160, 'ok', 'OK', 1, 0),
	(1724, 1161, 'ok', 'OK', 1, 0),
	(1725, 1162, 'ok', 'OK', 1, 0),
	(1726, 1163, 'ok', 'OK', 1, 0),
	(1727, 1164, 'ok', 'OK', 1, 0),
	(1728, 1165, 'ok', 'OK', 1, 0),
	(1729, 1166, 'ok', 'OK', 1, 0),
	(1730, 1167, 'ok', 'OK', 1, 0),
	(1731, 1168, 'ok', 'OK', 1, 0),
	(1732, 1169, 'ok', 'OK', 1, 0),
	(1733, 1170, 'ok', 'OK', 1, 0),
	(1734, 1171, 'ok', 'OK', 1, 0),
	(1735, 1172, 'ok', 'OK', 1, 0),
	(1736, 1173, 'ok', 'OK', 1, 0),
	(1737, 1174, 'ok', 'OK', 1, 0),
	(1738, 1175, 'ok', 'OK', 1, 0),
	(1739, 1176, 'ok', 'OK', 1, 0),
	(1740, 1177, 'ok', 'OK', 1, 0),
	(1741, 1178, 'ok', 'OK', 1, 0),
	(1742, 1179, 'ok', 'OK', 1, 0),
	(1743, 1180, 'ok', 'OK', 1, 0),
	(1744, 1181, 'ok', 'OK', 1, 0),
	(1745, 1182, 'ok', 'OK', 1, 0),
	(1746, 1183, 'ok', 'OK', 1, 0),
	(1747, 1184, 'ok', 'OK', 1, 0),
	(1748, 1185, 'ok', 'OK', 1, 0),
	(1749, 1186, 'ok', 'OK', 1, 0),
	(1750, 1187, 'ok', 'OK', 1, 0),
	(1751, 1188, 'ok', 'OK', 1, 0),
	(1752, 1189, 'ok', 'OK', 1, 0),
	(1753, 1190, 'ok', 'OK', 1, 0),
	(1754, 1191, 'ok', 'OK', 1, 0),
	(1755, 1192, 'ok', 'OK', 1, 0),
	(1756, 1193, 'ok', 'OK', 1, 0),
	(1757, 1194, 'ok', 'OK', 1, 0),
	(1758, 1195, 'ok', 'OK', 1, 0),
	(1759, 1196, 'ok', 'OK', 1, 0),
	(1760, 1197, 'ok', 'OK', 1, 0),
	(1761, 1198, 'ok', 'OK', 1, 0),
	(1762, 1199, 'ok', 'OK', 1, 0),
	(1763, 1200, 'ok', 'OK', 1, 0),
	(1764, 1201, 'ok', 'OK', 1, 0),
	(1765, 1202, 'ok', 'OK', 1, 0),
	(1766, 1203, 'ok', 'OK', 1, 0),
	(1767, 1204, 'ok', 'OK', 1, 0),
	(1768, 1205, 'ok', 'OK', 1, 0),
	(1769, 1206, 'ok', 'OK', 1, 0),
	(1770, 1207, 'ok', 'OK', 1, 0),
	(1771, 1208, 'ok', 'OK', 1, 0),
	(1772, 1209, 'ok', 'OK', 1, 0),
	(1773, 1210, 'ok', 'OK', 1, 0),
	(1774, 1212, 'ok', 'OK', 1, 0),
	(1775, 1213, 'ok', 'OK', 1, 0),
	(1776, 1214, 'ok', 'OK', 1, 0),
	(1777, 1216, 'ok', 'OK', 1, 0),
	(1778, 1217, 'ok', 'OK', 1, 0),
	(1779, 1218, 'ok', 'OK', 1, 0),
	(1780, 1219, 'ok', 'OK', 1, 0),
	(1781, 1220, 'ok', 'OK', 1, 0),
	(1782, 1221, 'ok', 'OK', 1, 0),
	(1783, 1222, 'ok', 'OK', 1, 0),
	(1784, 1223, '80_kmh', '80 km/h', 1, 0),
	(1785, 1223, '100_kmh', '100 km/h', 2, 0),
	(1786, 1224, 'ok', 'OK', 1, 0),
	(1787, 1225, 'ok', 'OK', 1, 0),
	(1788, 1226, 'ok', 'OK', 1, 0);

-- Exportiere Struktur von Tabelle d044f149.maintenance_checklist_results
CREATE TABLE IF NOT EXISTS `maintenance_checklist_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maintenance_id` int(11) NOT NULL COMMENT 'Referenz zu maintenance_history',
  `checklist_item_id` int(11) NOT NULL,
  `status` enum('ok','warning','fail','not_checked','custom') DEFAULT 'not_checked',
  `selected_option` varchar(255) DEFAULT NULL COMMENT 'Ausgewählte Option bei radio/select',
  `text_value` text DEFAULT NULL COMMENT 'Text-Antwort bei text/textarea',
  `date_value` date DEFAULT NULL COMMENT 'Datums-Wert bei date-Feldern',
  `number_value` decimal(10,2) DEFAULT NULL COMMENT 'Numerischer Wert',
  `notes` text DEFAULT NULL,
  `measurement_value` decimal(10,2) DEFAULT NULL,
  `photo_path` varchar(500) DEFAULT NULL,
  `checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `maintenance_id` (`maintenance_id`),
  KEY `checklist_item_id` (`checklist_item_id`),
  CONSTRAINT `fk_checklist_results_item` FOREIGN KEY (`checklist_item_id`) REFERENCES `maintenance_checklist_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_checklist_results_maintenance` FOREIGN KEY (`maintenance_id`) REFERENCES `maintenance_history` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_checklist_results: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.maintenance_history
CREATE TABLE IF NOT EXISTS `maintenance_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `checklist_data` longtext DEFAULT NULL COMMENT 'JSON-Daten der Checklisten-Items',
  `signature_data` longtext DEFAULT NULL COMMENT 'Base64 encoded signature für diese Wartung',
  `checklist_id` int(11) DEFAULT NULL COMMENT 'Verwendete Checkliste',
  `technician_name` varchar(255) DEFAULT NULL COMMENT 'Name des Technikers',
  `duration_minutes` int(11) DEFAULT NULL COMMENT 'Dauer der Wartung in Minuten',
  `pdf_report_path` varchar(500) DEFAULT NULL COMMENT 'Pfad zum generierten PDF-Report',
  `status` enum('draft','in_progress','completed','cancelled') DEFAULT 'in_progress',
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_checklist_id` (`checklist_id`),
  KEY `idx_status` (`status`),
  KEY `idx_performed_by` (`performed_by`),
  KEY `idx_maintenance_marker_id` (`marker_id`),
  KEY `idx_maintenance_date` (`maintenance_date`),
  KEY `idx_maintenance_marker` (`marker_id`),
  KEY `idx_maintenance_created` (`created_at`),
  KEY `idx_maintenance_history_date` (`maintenance_date`),
  KEY `idx_maintenance_history_marker_id` (`marker_id`),
  KEY `idx_maintenance_marker_status` (`marker_id`,`status`),
  KEY `idx_maintenance_date_status` (`maintenance_date`,`status`),
  CONSTRAINT `maintenance_history_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_history: ~1 rows (ungefähr)
INSERT INTO `maintenance_history` (`id`, `marker_id`, `maintenance_date`, `description`, `performed_by`, `created_at`, `updated_at`, `notes`, `checklist_data`, `signature_data`, `checklist_id`, `technician_name`, `duration_minutes`, `pdf_report_path`, `status`) VALUES
	(8, 31, '2025-11-10', 'Ölwechsel', 1, '2025-11-10 20:52:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'in_progress');

-- Exportiere Struktur von Tabelle d044f149.maintenance_notifications
CREATE TABLE IF NOT EXISTS `maintenance_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) DEFAULT NULL,
  `notification_type` enum('warning','overdue','critical') NOT NULL COMMENT 'Warnung, Überfällig, Kritisch',
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_to` varchar(255) DEFAULT NULL COMMENT 'E-Mail-Adressen',
  `days_overdue` int(11) DEFAULT 0 COMMENT 'Tage überfällig (0 = Warnung)',
  `email_sent` tinyint(1) DEFAULT 0 COMMENT 'E-Mail erfolgreich versendet?',
  `error_message` text DEFAULT NULL COMMENT 'Fehlermeldung falls E-Mail fehlschlug',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_notification_type` (`notification_type`),
  CONSTRAINT `fk_maintenance_notif_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Wartungs-Eskalations-Benachrichtigungen';

-- Exportiere Daten aus Tabelle d044f149.maintenance_notifications: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.maintenance_notifications_old
CREATE TABLE IF NOT EXISTS `maintenance_notifications_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent_at` datetime NOT NULL,
  `devices_count` int(11) NOT NULL COMMENT 'Anzahl der Geräte mit fälliger Wartung',
  `users_notified` int(11) NOT NULL COMMENT 'Anzahl der benachrichtigten Benutzer',
  `inspections_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Protokoll der Wartungsbenachrichtigungen';

-- Exportiere Daten aus Tabelle d044f149.maintenance_notifications_old: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.maintenance_sets
CREATE TABLE IF NOT EXISTS `maintenance_sets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_sets: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.maintenance_set_fields
CREATE TABLE IF NOT EXISTS `maintenance_set_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maintenance_set_id` int(11) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` enum('text','textarea','number','date','checkbox','select') DEFAULT 'text',
  `field_options` text DEFAULT NULL COMMENT 'JSON für Select-Optionen',
  `is_required` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `maintenance_set_id` (`maintenance_set_id`),
  CONSTRAINT `fk_msf_maintenance_set` FOREIGN KEY (`maintenance_set_id`) REFERENCES `maintenance_sets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_set_fields: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.maintenance_set_values
CREATE TABLE IF NOT EXISTS `maintenance_set_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `maintenance_set_field_id` int(11) NOT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_marker_field` (`marker_id`,`maintenance_set_field_id`),
  KEY `marker_id` (`marker_id`),
  KEY `maintenance_set_field_id` (`maintenance_set_field_id`),
  CONSTRAINT `fk_msv_maintenance_set_field` FOREIGN KEY (`maintenance_set_field_id`) REFERENCES `maintenance_set_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.maintenance_set_values: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.maintenance_statistics
CREATE TABLE IF NOT EXISTS `maintenance_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_markers` int(11) DEFAULT 0,
  `maintenance_due` int(11) DEFAULT 0,
  `maintenance_overdue` int(11) DEFAULT 0,
  `maintenance_completed` int(11) DEFAULT 0,
  `inspection_due` int(11) DEFAULT 0,
  `inspection_overdue` int(11) DEFAULT 0,
  `inspection_completed` int(11) DEFAULT 0,
  `average_operating_hours` decimal(10,2) DEFAULT 0.00,
  `total_scans` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tägliche Statistiken für Dashboard';

-- Exportiere Daten aus Tabelle d044f149.maintenance_statistics: ~0 rows (ungefähr)
INSERT INTO `maintenance_statistics` (`id`, `date`, `total_markers`, `maintenance_due`, `maintenance_overdue`, `maintenance_completed`, `inspection_due`, `inspection_overdue`, `inspection_completed`, `average_operating_hours`, `total_scans`, `created_at`) VALUES
	(1, '2025-10-10', 0, 0, 0, 0, 0, 0, 0, NULL, 0, '2025-10-09 23:00:00');

-- Exportiere Struktur von Tabelle d044f149.markers
CREATE TABLE IF NOT EXISTS `markers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `is_storage` tinyint(1) DEFAULT 0,
  `rental_status` enum('verfuegbar','vermietet','wartung','reparatur','auf_messe') DEFAULT 'verfuegbar',
  `operating_hours` decimal(10,2) DEFAULT 0.00,
  `fuel_level` int(11) DEFAULT 0,
  `maintenance_interval_months` int(11) DEFAULT 6,
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `maintenance_required` tinyint(1) DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_multi_device` tinyint(1) DEFAULT 0 COMMENT 'Mehrere Geräte an einem Standort',
  `is_activated` tinyint(1) DEFAULT 0 COMMENT 'Wurde der Marker vor Ort aktiviert?',
  `public_token` varchar(64) DEFAULT NULL,
  `nfc_enabled` tinyint(1) DEFAULT 0 COMMENT 'Nutzt dieser Marker NFC statt QR-Code?',
  `nfc_chip_id` varchar(100) DEFAULT NULL COMMENT 'NFC-Chip-ID (falls NFC aktiviert)',
  `marker_type` enum('qr_code','nfc_chip') DEFAULT 'qr_code' COMMENT 'Art des Markers',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `is_customer_device` tinyint(1) DEFAULT 0 COMMENT 'Ist das Gerät ein Kundengerät?',
  `customer_name` varchar(255) DEFAULT NULL COMMENT 'Name des Kunden',
  `order_number` varchar(100) DEFAULT NULL COMMENT 'Auftragsnummer',
  `weclapp_entity_id` varchar(100) DEFAULT NULL COMMENT 'Weclapp Entity-ID für direkte Verlinkung zum Auftrag',
  `is_repair_device` tinyint(1) DEFAULT 0 COMMENT 'Ist das Gerät ein Reparaturgerät?',
  `repair_description` text DEFAULT NULL COMMENT 'Beschreibung der Reparatur',
  `is_finished` tinyint(1) DEFAULT 0 COMMENT 'Ist das Gerät fertig? (nur Kunden/Reparatur)',
  `finished_at` datetime DEFAULT NULL COMMENT 'Zeitpunkt der Fertigstellung',
  `finished_by` int(11) DEFAULT NULL COMMENT 'Benutzer der das Gerät fertig markiert hat',
  `fuel_unit` enum('percent','liter') DEFAULT 'percent' COMMENT 'Einheit für Kraftstofffüllstand',
  `fuel_capacity` decimal(10,2) DEFAULT NULL COMMENT 'Tank-Kapazität in Liter (für Umrechnung)',
  `finished_icon_url` varchar(255) DEFAULT NULL,
  `cluster_enabled` tinyint(1) DEFAULT 1,
  `cluster_priority` int(11) DEFAULT 0,
  `maintenance_set_id` int(11) DEFAULT NULL,
  `gps_latitude` decimal(10,8) DEFAULT NULL COMMENT 'GPS Breitengrad',
  `gps_longitude` decimal(11,8) DEFAULT NULL COMMENT 'GPS Längengrad',
  `gps_captured_at` datetime DEFAULT NULL COMMENT 'Wann wurde GPS erfasst?',
  `gps_captured_by` varchar(20) DEFAULT NULL COMMENT 'QR oder NFC - welche Methode?',
  `gps_accuracy` decimal(10,2) DEFAULT NULL COMMENT 'GPS Genauigkeit in Metern',
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_code` (`qr_code`),
  UNIQUE KEY `public_token` (`public_token`),
  UNIQUE KEY `unique_nfc_chip` (`nfc_chip_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_qr_code` (`qr_code`),
  KEY `idx_marker_activated` (`is_activated`),
  KEY `idx_markers_deleted` (`deleted_at`),
  KEY `idx_markers_qr_code` (`qr_code`),
  KEY `idx_next_maintenance` (`next_maintenance`),
  KEY `idx_maintenance_storage` (`next_maintenance`,`is_storage`),
  KEY `idx_is_customer_device` (`is_customer_device`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_serial_number` (`serial_number`),
  KEY `idx_category` (`category`),
  KEY `idx_rental_status` (`rental_status`),
  KEY `idx_is_storage` (`is_storage`),
  KEY `idx_is_activated` (`is_activated`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_repair_device` (`is_repair_device`),
  KEY `idx_is_finished` (`is_finished`),
  KEY `idx_fuel_unit` (`fuel_unit`),
  KEY `idx_finished_at` (`finished_at`),
  KEY `idx_markers_finished` (`is_finished`,`finished_at`),
  KEY `idx_markers_repair` (`is_repair_device`),
  KEY `idx_nfc_chip_id` (`nfc_chip_id`),
  KEY `idx_marker_type` (`marker_type`),
  KEY `idx_weclapp_entity_id` (`weclapp_entity_id`),
  KEY `idx_markers_category` (`category`),
  KEY `idx_markers_rental_status` (`rental_status`),
  KEY `idx_markers_marker_type` (`marker_type`),
  KEY `idx_markers_created_at` (`created_at`),
  KEY `idx_markers_deleted_at` (`deleted_at`),
  KEY `idx_markers_composite` (`deleted_at`,`category`,`rental_status`,`created_at`),
  KEY `idx_markers_name` (`name`),
  KEY `idx_markers_serial` (`serial_number`),
  KEY `idx_markers_id_name` (`id`,`name`),
  KEY `idx_markers_created` (`created_at`),
  KEY `idx_markers_is_multi_device` (`is_multi_device`),
  KEY `idx_markers_is_storage` (`is_storage`),
  KEY `idx_markers_category_status` (`category`,`rental_status`),
  KEY `idx_markers_name_serial` (`name`(50),`serial_number`(50)),
  KEY `idx_markers_serial_number` (`serial_number`),
  KEY `idx_markers_category_rental_status` (`category`,`rental_status`),
  KEY `idx_markers_type_storage` (`marker_type`,`is_storage`),
  KEY `maintenance_set_id` (`maintenance_set_id`),
  FULLTEXT KEY `ft_search` (`name`,`category`,`serial_number`,`qr_code`),
  CONSTRAINT `markers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.markers: ~2 rows (ungefähr)
INSERT INTO `markers` (`id`, `qr_code`, `name`, `category`, `serial_number`, `is_storage`, `rental_status`, `operating_hours`, `fuel_level`, `maintenance_interval_months`, `last_maintenance`, `next_maintenance`, `maintenance_required`, `latitude`, `longitude`, `created_by`, `created_at`, `updated_at`, `is_multi_device`, `is_activated`, `public_token`, `nfc_enabled`, `nfc_chip_id`, `marker_type`, `deleted_at`, `deleted_by`, `is_customer_device`, `customer_name`, `order_number`, `weclapp_entity_id`, `is_repair_device`, `repair_description`, `is_finished`, `finished_at`, `finished_by`, `fuel_unit`, `fuel_capacity`, `finished_icon_url`, `cluster_enabled`, `cluster_priority`, `maintenance_set_id`, `gps_latitude`, `gps_longitude`, `gps_captured_at`, `gps_captured_by`, `gps_accuracy`) VALUES
	(31, 'QR-0001', 'Test QR', 'Generator', 'B25.12345', 0, 'verfuegbar', 100.00, 5, 6, '2025-11-10', '2026-05-10', 0, 49.99422600, 9.07244900, 1, '2025-11-10 20:22:43', '2025-11-14 12:44:15', 0, 1, 'dac44d3aca53460fbc1c84934582d7ee9432c0124335e3248444f24a093ad6da', 0, NULL, 'qr_code', NULL, NULL, 1, 'Test Kunde QR', '', '', 0, NULL, 0, NULL, NULL, 'percent', NULL, NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
	(32, '04:F9:DA:73:3E:61:80', 'NFC Test', 'Generator', 'B25.123456', 0, 'auf_messe', 10.00, 60, 6, '2025-11-10', '2026-05-10', 0, 49.99425729, 9.07257726, 1, '2025-11-10 20:23:20', '2025-11-13 11:22:15', 0, 1, 'e7ddab87b394c67c23018d9e323625812cbd3ec75d19df847dd6d4bd362d5a74', 1, '04:F9:DA:73:3E:61:80', 'nfc_chip', NULL, NULL, 1, 'Test Kunde NFC', '', '', 0, NULL, 0, NULL, NULL, 'liter', 120.00, NULL, 1, 0, NULL, 49.99425729, 9.07257726, '2025-11-11 13:48:28', 'NFC', 5.31);

-- Exportiere Struktur von Tabelle d044f149.marker_3d_models
CREATE TABLE IF NOT EXISTS `marker_3d_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) DEFAULT NULL,
  `model_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_format` varchar(10) NOT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `model_settings` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `file_format` (`file_format`),
  KEY `idx_3d_models_marker` (`marker_id`),
  KEY `idx_3d_models_format` (`file_format`),
  CONSTRAINT `marker_3d_models_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `marker_3d_models_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.marker_3d_models: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.marker_custom_values
CREATE TABLE IF NOT EXISTS `marker_custom_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `field_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_marker_field` (`marker_id`,`field_id`),
  KEY `field_id` (`field_id`),
  FULLTEXT KEY `ft_custom_search` (`field_value`),
  CONSTRAINT `marker_custom_values_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marker_custom_values_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.marker_custom_values: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.marker_documents
CREATE TABLE IF NOT EXISTS `marker_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `marker_name` varchar(255) DEFAULT NULL,
  `document_type` varchar(50) NOT NULL DEFAULT 'other',
  `document_name` varchar(255) NOT NULL,
  `document_path` varchar(500) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0 COMMENT 'Für Öffentlichkeit freigegeben?',
  `public_description` text DEFAULT NULL COMMENT 'Beschreibung für öffentliche Ansicht',
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL COMMENT 'Ablaufdatum des Dokuments',
  `notification_days_before` int(11) DEFAULT 14 COMMENT 'Tage vorher benachrichtigen',
  `last_notification_sent` date DEFAULT NULL COMMENT 'Letzte Benachrichtigung',
  `document_status` enum('aktuell','läuft_ab','abgelaufen') DEFAULT 'aktuell',
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_is_public` (`is_public`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_document_status` (`document_status`),
  CONSTRAINT `marker_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dokumente zu Markern - mit Freigabe-Option für Public View';

-- Exportiere Daten aus Tabelle d044f149.marker_documents: ~1 rows (ungefähr)
INSERT INTO `marker_documents` (`id`, `marker_id`, `marker_name`, `document_type`, `document_name`, `document_path`, `file_name`, `file_size`, `mime_type`, `uploaded_by`, `is_public`, `public_description`, `uploaded_at`, `expiry_date`, `notification_days_before`, `last_notification_sent`, `document_status`) VALUES
	(1, 25, 'dfghfgd', 'other', 'test', 'uploads/documents/dfghfgd/doc_690c82b4ee34f.png', 'Screenshot 2025-10-16 121730.png', 144706, 'image/png', 1, 0, 'test bild zeug', '2025-11-06 12:12:52', NULL, 14, NULL, 'aktuell');

-- Exportiere Struktur von Tabelle d044f149.marker_history
CREATE TABLE IF NOT EXISTS `marker_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'created, updated, deleted, restored, etc.',
  `field_name` varchar(100) DEFAULT NULL COMMENT 'Name des geänderten Feldes',
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `change_details` text DEFAULT NULL COMMENT 'JSON mit allen Änderungen',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_action` (`action`),
  KEY `idx_marker_history_marker_created` (`marker_id`,`created_at`),
  CONSTRAINT `fk_marker_history_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_marker_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Änderungshistorie für Marker';

-- Exportiere Daten aus Tabelle d044f149.marker_history: ~15 rows (ungefähr)
INSERT INTO `marker_history` (`id`, `marker_id`, `user_id`, `username`, `action`, `field_name`, `old_value`, `new_value`, `change_details`, `ip_address`, `user_agent`, `created_at`) VALUES
	(14, 31, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "Test QR", "new": "Test QR"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.12345", "new": "B25.12345"}, "rental_status": {"old": "wartung", "new": "verfuegbar"}, "operating_hours": {"old": 0.00, "new": 0.00}, "fuel_level": {"old": 0, "new": 0}, "latitude": {"old": 50.10160000, "new": 50.10160000}, "longitude": {"old": 8.62850000, "new": 8.62850000}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde QR", "new": "Test Kunde QR"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-10 21:52:47'),
	(15, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": null, "new": "verfuegbar"}, "operating_hours": {"old": 0.00, "new": 10.00}, "fuel_level": {"old": 0, "new": 60}, "latitude": {"old": 50.04703041, "new": 50.04703041}, "longitude": {"old": 8.97332573, "new": 8.97332573}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 06:10:18'),
	(16, 31, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "Test QR", "new": "Test QR"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.12345", "new": "B25.12345"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 0.00, "new": 100.00}, "fuel_level": {"old": 0, "new": 5}, "latitude": {"old": 50.10160000, "new": 50.10160000}, "longitude": {"old": 8.62850000, "new": 8.62850000}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde QR", "new": "Test Kunde QR"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 06:30:37'),
	(17, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 50.04703041, "new": 49.99409400}, "longitude": {"old": 8.97332573, "new": 9.07226800}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 09:52:42'),
	(18, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99409400, "new": 49.99411938}, "longitude": {"old": 9.07226800, "new": 9.07231931}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 12:38:13'),
	(19, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99411938, "new": 49.99410422}, "longitude": {"old": 9.07231931, "new": 9.07229467}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 12:38:27'),
	(20, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99410422, "new": 49.99407912}, "longitude": {"old": 9.07229467, "new": 9.07227671}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 12:42:40'),
	(21, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99407912, "new": 49.99408327}, "longitude": {"old": 9.07227671, "new": 9.07228410}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 12:43:29'),
	(22, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99408327, "new": 49.99405184}, "longitude": {"old": 9.07228410, "new": 9.07270801}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 12:53:46'),
	(23, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99405184, "new": 49.99415670}, "longitude": {"old": 9.07270801, "new": 9.07286032}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 12:56:09'),
	(24, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99415670, "new": 49.99425729}, "longitude": {"old": 9.07286032, "new": 9.07257726}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 13:48:28'),
	(25, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "vermietet"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99425729, "new": 49.99425729}, "longitude": {"old": 9.07257726, "new": 9.07257726}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-11 16:09:00'),
	(26, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "vermietet", "new": "verfuegbar"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99425729, "new": 49.99425729}, "longitude": {"old": 9.07257726, "new": 9.07257726}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-13 12:22:09'),
	(27, 32, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "NFC Test", "new": "NFC Test"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.123456", "new": "B25.123456"}, "rental_status": {"old": "verfuegbar", "new": "auf_messe"}, "operating_hours": {"old": 10.00, "new": 10.00}, "fuel_level": {"old": 60, "new": 60}, "latitude": {"old": 49.99425729, "new": 49.99425729}, "longitude": {"old": 9.07257726, "new": 9.07257726}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde NFC", "new": "Test Kunde NFC"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-13 12:22:15'),
	(28, 31, NULL, NULL, 'updated', NULL, NULL, NULL, '{"name": {"old": "Test QR", "new": "Test QR"}, "category": {"old": "Generator", "new": "Generator"}, "serial_number": {"old": "B25.12345", "new": "B25.12345"}, "rental_status": {"old": "verfuegbar", "new": "verfuegbar"}, "operating_hours": {"old": 100.00, "new": 100.00}, "fuel_level": {"old": 5, "new": 5}, "latitude": {"old": 50.10160000, "new": 49.99422600}, "longitude": {"old": 8.62850000, "new": 9.07244900}, "is_storage": {"old": 0, "new": 0}, "is_customer_device": {"old": 1, "new": 1}, "customer_name": {"old": "Test Kunde QR", "new": "Test Kunde QR"}, "order_number": {"old": "", "new": ""}, "is_finished": {"old": 0, "new": 0}}', NULL, NULL, '2025-11-14 13:44:15');

-- Exportiere Struktur von Tabelle d044f149.marker_images
CREATE TABLE IF NOT EXISTS `marker_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  CONSTRAINT `marker_images_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.marker_images: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.marker_serial_numbers
CREATE TABLE IF NOT EXISTS `marker_serial_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_marker_serial_numbers_marker_id` (`marker_id`),
  CONSTRAINT `marker_serial_numbers_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.marker_serial_numbers: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.marker_tags
CREATE TABLE IF NOT EXISTS `marker_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `tag_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `tag_name` (`tag_name`),
  KEY `created_by` (`created_by`),
  KEY `idx_tag_search` (`tag_name`,`marker_id`),
  CONSTRAINT `marker_tags_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marker_tags_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.marker_tags: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.marker_templates
CREATE TABLE IF NOT EXISTS `marker_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_storage` tinyint(1) DEFAULT 0,
  `rental_status` enum('verfuegbar','vermietet','wartung') DEFAULT 'verfuegbar',
  `maintenance_interval_months` int(11) DEFAULT 6,
  `is_multi_device` tinyint(1) DEFAULT 0,
  `is_customer_device` tinyint(1) DEFAULT 0,
  `is_repair_device` tinyint(1) DEFAULT 0,
  `fuel_unit` enum('percent','liter') DEFAULT 'percent',
  `fuel_capacity` decimal(10,2) DEFAULT NULL,
  `custom_fields` text DEFAULT NULL COMMENT 'JSON mit Custom Field Werten',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_category` (`category`),
  CONSTRAINT `fk_marker_template_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vorlagen für Marker-Erstellung';

-- Exportiere Daten aus Tabelle d044f149.marker_templates: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.messe_config
CREATE TABLE IF NOT EXISTS `messe_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Messe-Name',
  `is_active` tinyint(1) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `lead_email` varchar(255) DEFAULT NULL COMMENT 'Email-Adresse für Lead-Benachrichtigungen',
  `logo_path` varchar(500) DEFAULT NULL,
  `hero_image_path` varchar(500) DEFAULT NULL,
  `font_family` varchar(255) DEFAULT '''Segoe UI'', sans-serif',
  `welcome_text` text DEFAULT NULL,
  `footer_text` varchar(255) DEFAULT '© 2025 Ihr Unternehmen',
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  `background_color` varchar(20) DEFAULT '#ffffff',
  `text_color` varchar(20) DEFAULT '#000000',
  `accent_color` varchar(20) DEFAULT '#007bff',
  `primary_color` varchar(7) DEFAULT '#667eea',
  `secondary_color` varchar(7) DEFAULT '#764ba2',
  `button_color` varchar(7) DEFAULT '#28a745',
  `background_style` enum('solid','gradient','image') DEFAULT 'gradient',
  `background_image_path` varchar(500) DEFAULT NULL,
  `show_3d_models` tinyint(1) DEFAULT 1,
  `show_lead_capture` tinyint(1) DEFAULT 1,
  `thank_you_message` text DEFAULT 'Vielen Dank für Ihr Interesse! Wir melden uns bei Ihnen.',
  `created_at` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lead_email` (`lead_email`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.messe_config: ~1 rows (ungefähr)
INSERT INTO `messe_config` (`id`, `name`, `is_active`, `start_date`, `end_date`, `description`, `lead_email`, `logo_path`, `hero_image_path`, `font_family`, `welcome_text`, `footer_text`, `social_links`, `background_color`, `text_color`, `accent_color`, `primary_color`, `secondary_color`, `button_color`, `background_style`, `background_image_path`, `show_3d_models`, `show_lead_capture`, `thank_you_message`, `created_at`, `deleted_at`, `deleted_by`) VALUES
	(6, 'Test', 1, '2025-11-01', '2025-11-02', NULL, 'test@email.de', NULL, NULL, '\'Segoe UI\', sans-serif', NULL, '© 2025 Ihr Unternehmen', NULL, '#ffffff', '#000000', '#007bff', '#667eea', '#764ba2', '#28a745', 'gradient', NULL, 1, 1, 'Vielen Dank für Ihr Interesse! Wir melden uns bei Ihnen.', '2025-11-01 13:23:34', NULL, NULL);

-- Exportiere Struktur von Tabelle d044f149.messe_leads
CREATE TABLE IF NOT EXISTS `messe_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messe_id` int(11) NOT NULL,
  `marker_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `interested_in` varchar(255) DEFAULT NULL COMMENT 'Welches Gerät',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `messe_id` (`messe_id`),
  KEY `marker_id` (`marker_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `messe_leads_ibfk_1` FOREIGN KEY (`messe_id`) REFERENCES `messe_config` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.messe_leads: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.messe_markers
CREATE TABLE IF NOT EXISTS `messe_markers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messe_id` int(11) NOT NULL,
  `marker_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `custom_title` varchar(255) DEFAULT NULL COMMENT 'Überschreibt Marker-Name',
  `custom_description` text DEFAULT NULL,
  `model_3d_path` varchar(500) DEFAULT NULL COMMENT 'Pfad zum 3D-Modell',
  `device_image` varchar(500) DEFAULT NULL COMMENT 'Pfad zum Gerätebild',
  `additional_info` text DEFAULT NULL COMMENT 'Zusätzliche Informationen für dieses Gerät (Aufstellbedingungen, PRP, ESP, etc.)',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `messe_id` (`messe_id`),
  KEY `marker_id` (`marker_id`),
  CONSTRAINT `messe_markers_ibfk_1` FOREIGN KEY (`messe_id`) REFERENCES `messe_config` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messe_markers_ibfk_2` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.messe_markers: ~1 rows (ungefähr)
INSERT INTO `messe_markers` (`id`, `messe_id`, `marker_id`, `display_order`, `is_featured`, `custom_title`, `custom_description`, `model_3d_path`, `device_image`, `additional_info`, `created_at`) VALUES
	(10, 6, 32, 0, 0, NULL, NULL, NULL, NULL, NULL, '2025-11-13 12:22:15');

-- Exportiere Struktur von Tabelle d044f149.messe_marker_badges
CREATE TABLE IF NOT EXISTS `messe_marker_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messe_marker_id` int(11) NOT NULL,
  `badge_text` varchar(100) NOT NULL COMMENT 'Text des Badges (z.B. "01", "Wassergekühlt")',
  `badge_icon` varchar(100) DEFAULT NULL COMMENT 'Icon-Klasse (z.B. "fas fa-water")',
  `badge_color` varchar(20) DEFAULT '#FFD700' COMMENT 'Hintergrundfarbe des Badges',
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `messe_marker_id` (`messe_marker_id`),
  KEY `idx_display_order` (`display_order`),
  CONSTRAINT `messe_marker_badges_ibfk_1` FOREIGN KEY (`messe_marker_id`) REFERENCES `messe_markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.messe_marker_badges: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.messe_marker_fields
CREATE TABLE IF NOT EXISTS `messe_marker_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messe_marker_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL COMMENT 'Name des Feldes (z.B. "Leistung", "Gewicht")',
  `field_value` text NOT NULL,
  `field_icon` varchar(50) DEFAULT NULL COMMENT 'FontAwesome Icon',
  `display_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `messe_marker_id` (`messe_marker_id`),
  CONSTRAINT `messe_marker_fields_ibfk_1` FOREIGN KEY (`messe_marker_id`) REFERENCES `messe_markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.messe_marker_fields: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.messe_scan_stats
CREATE TABLE IF NOT EXISTS `messe_scan_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messe_id` int(11) NOT NULL,
  `marker_id` int(11) NOT NULL,
  `scan_count` int(11) DEFAULT 1,
  `unique_visitors` int(11) DEFAULT 1,
  `last_scan` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `messe_id` (`messe_id`),
  KEY `marker_id` (`marker_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `messe_scan_stats_ibfk_1` FOREIGN KEY (`messe_id`) REFERENCES `messe_config` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messe_scan_stats_ibfk_2` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.messe_scan_stats: ~6 rows (ungefähr)
INSERT INTO `messe_scan_stats` (`id`, `messe_id`, `marker_id`, `scan_count`, `unique_visitors`, `last_scan`, `ip_address`, `created_at`) VALUES
	(9, 6, 32, 11, 1, '2025-11-12 14:59:41', '80.151.166.21', '2025-11-11 16:09:12'),
	(10, 6, 32, 11, 1, '2025-11-12 22:34:27', '109.43.114.174', '2025-11-11 19:51:21'),
	(11, 6, 32, 7, 1, '2025-11-13 17:10:42', '80.187.101.214', '2025-11-12 07:40:01'),
	(12, 6, 32, 1, 1, '2025-11-12 12:12:58', '80.187.71.255', '2025-11-12 12:12:58'),
	(13, 6, 32, 1, 1, '2025-11-12 13:13:02', '80.187.65.46', '2025-11-12 13:13:02'),
	(14, 6, 32, 1, 1, '2025-11-15 13:12:17', '178.24.120.36', '2025-11-15 13:12:17');

-- Exportiere Struktur von Tabelle d044f149.nfc_chip_pool
CREATE TABLE IF NOT EXISTS `nfc_chip_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nfc_chip_id` varchar(100) NOT NULL,
  `is_assigned` tinyint(1) DEFAULT 0,
  `assigned_to_marker_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `assigned_at` datetime DEFAULT NULL,
  `batch_name` varchar(100) DEFAULT NULL COMMENT 'Batch-Name für Organisation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nfc_chip_id` (`nfc_chip_id`),
  KEY `idx_is_assigned` (`is_assigned`),
  KEY `idx_assigned_to_marker` (`assigned_to_marker_id`),
  KEY `idx_batch_name` (`batch_name`),
  CONSTRAINT `nfc_chip_pool_ibfk_1` FOREIGN KEY (`assigned_to_marker_id`) REFERENCES `markers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pool verfügbarer NFC-Chip-IDs';

-- Exportiere Daten aus Tabelle d044f149.nfc_chip_pool: ~19 rows (ungefähr)
INSERT INTO `nfc_chip_pool` (`id`, `nfc_chip_id`, `is_assigned`, `assigned_to_marker_id`, `created_at`, `assigned_at`, `batch_name`) VALUES
	(5, '04:F9:DA:73:3E:61:80', 1, 32, '2025-11-10 15:21:59', '2025-11-10 21:23:20', 'NFC-1'),
	(6, '04:DE:8B:68:4E:61:80', 0, NULL, '2025-11-10 15:25:35', NULL, 'NFC-2'),
	(7, '04:54:E2:68:4E:61:80', 0, NULL, '2025-11-10 16:19:06', NULL, 'NFC-3'),
	(8, '04:CA:31:68:4E:61:81', 0, NULL, '2025-11-10 16:20:43', NULL, 'NFC-4'),
	(9, '04:E7:EA:69:4E:61:80', 0, NULL, '2025-11-10 16:21:11', NULL, 'NFC-5'),
	(10, '04:E0:42:6A:4E:61:80', 0, NULL, '2025-11-10 16:21:36', NULL, 'NFC-6'),
	(11, '04:65:97:69:4E:61:81', 0, NULL, '2025-11-10 16:22:02', NULL, 'NFC-7'),
	(12, '04:0B:8A:68:4E:61:80', 0, NULL, '2025-11-10 16:22:29', NULL, 'NFC-8'),
	(13, '04:BC:AC:69:4E:61:80', 0, NULL, '2025-11-10 16:22:58', NULL, 'NFC-9'),
	(14, '04:46:F3:73:3E:61:80', 0, NULL, '2025-11-10 16:23:22', NULL, 'NFC-10'),
	(15, '04:AC:3E:6A:4E:61:80', 0, NULL, '2025-11-10 16:23:48', NULL, 'NFC-11'),
	(16, '04:5E:46:69:4E:61:80', 0, NULL, '2025-11-10 16:24:13', NULL, 'NFC-12'),
	(17, '04:96:4E:74:3E:61:80', 0, NULL, '2025-11-10 16:24:38', NULL, 'NFC-13'),
	(18, '04:E1:AD:69:4E:61:80', 0, NULL, '2025-11-10 16:25:02', NULL, 'NFC-14'),
	(19, '04:75:5B:70:3E:61:80', 0, NULL, '2025-11-10 16:25:26', NULL, 'NFC-15'),
	(20, '04:FB:A3:66:4E:61:80', 0, NULL, '2025-11-10 16:25:48', NULL, 'NFC-16'),
	(21, '04:39:A6:65:4E:61:80', 0, NULL, '2025-11-10 16:26:12', NULL, 'NFC-17'),
	(22, '04:21:46:69:4E:61:80', 0, NULL, '2025-11-10 16:26:38', NULL, 'NFC-18'),
	(23, '04:FC:9F:65:4E:61:80', 0, NULL, '2025-11-10 16:27:00', NULL, 'NFC-19');

-- Exportiere Struktur von Tabelle d044f149.nfc_pool
CREATE TABLE IF NOT EXISTS `nfc_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nfc_code` varchar(100) NOT NULL COMMENT 'NFC-Code Nummer (z.B. NFC-0001)',
  `nfc_uid` varchar(100) DEFAULT NULL COMMENT 'Eindeutige Chip-UID (wird beim ersten Scan erfasst)',
  `batch_id` varchar(100) DEFAULT NULL,
  `is_assigned` tinyint(1) DEFAULT 0 COMMENT 'Ist der NFC-Tag bereits einem Marker zugewiesen?',
  `is_activated` tinyint(1) DEFAULT 0 COMMENT 'Wurde der NFC-Tag vor Ort aktiviert?',
  `marker_id` int(11) DEFAULT NULL COMMENT 'Zugewiesener Marker (wenn assigned)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_at` timestamp NULL DEFAULT NULL COMMENT 'Wann wurde er zugewiesen?',
  `print_batch` varchar(50) DEFAULT NULL COMMENT 'Druck-Batch zur Identifikation',
  `public_token` varchar(64) DEFAULT NULL COMMENT 'Eindeutiger Token für öffentlichen Zugriff',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nfc_code` (`nfc_code`),
  UNIQUE KEY `public_token` (`public_token`),
  KEY `idx_is_assigned` (`is_assigned`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `fk_nfc_pool_marker` (`marker_id`),
  CONSTRAINT `fk_nfc_pool_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pool für NFC-Tags analog zu QR-Codes';

-- Exportiere Daten aus Tabelle d044f149.nfc_pool: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.nfc_scan_history
CREATE TABLE IF NOT EXISTS `nfc_scan_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `nfc_chip_id` varchar(100) NOT NULL,
  `scan_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_scan_timestamp` (`scan_timestamp`),
  KEY `idx_nfc_chip_id` (`nfc_chip_id`),
  CONSTRAINT `fk_nfc_scan_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historie aller NFC-Chip-Scans';

-- Exportiere Daten aus Tabelle d044f149.nfc_scan_history: ~36 rows (ungefähr)
INSERT INTO `nfc_scan_history` (`id`, `marker_id`, `nfc_chip_id`, `scan_timestamp`, `ip_address`, `user_agent`) VALUES
	(1, 32, '04:F9:DA:73:3E:61:80', '2025-11-10 20:31:12', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(2, 32, '04:F9:DA:73:3E:61:80', '2025-11-10 20:31:14', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(3, 32, '04:F9:DA:73:3E:61:80', '2025-11-10 20:34:34', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(4, 32, '04:F9:DA:73:3E:61:80', '2025-11-10 20:53:21', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(5, 32, '04:F9:DA:73:3E:61:80', '2025-11-10 20:53:29', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(6, 32, '04:F9:DA:73:3E:61:80', '2025-11-10 22:18:25', '172.226.110.26', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
	(7, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 05:08:52', '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(8, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 05:10:57', '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(9, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 05:49:28', '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(10, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 05:49:37', '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(11, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 06:43:33', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(12, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 06:50:48', '80.187.71.93', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1'),
	(13, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 08:50:56', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(14, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 10:22:29', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(15, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 10:22:34', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(16, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 10:47:47', '104.28.62.44', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3 Mobile/15E148 Safari/604.1'),
	(17, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:15:14', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(18, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:16:35', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(19, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:21:17', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(20, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:21:20', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(21, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:21:39', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(22, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:22:58', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(23, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:30:22', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(24, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:30:25', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(25, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:37:51', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(26, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:37:58', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(27, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:38:16', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(28, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:38:30', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(29, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:42:27', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(30, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:42:34', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(31, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:43:23', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(32, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:53:31', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7'),
	(33, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 11:55:46', '80.151.166.21', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1'),
	(34, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 12:48:08', '80.151.166.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) EdgiOS/141 Version/16.0 Safari/605.1.15'),
	(35, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 14:06:07', '80.187.64.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1'),
	(36, 32, '04:F9:DA:73:3E:61:80', '2025-11-11 17:01:31', '178.24.120.36', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1');

-- Exportiere Struktur von Tabelle d044f149.notification_queue
CREATE TABLE IF NOT EXISTS `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `notification_type` varchar(50) NOT NULL,
  `notification_data` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `notification_queue_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.notification_queue: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.password_history
CREATE TABLE IF NOT EXISTS `password_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_changed` (`user_id`,`changed_at`),
  CONSTRAINT `password_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.password_history: ~1 rows (ungefähr)
INSERT INTO `password_history` (`id`, `user_id`, `password_hash`, `changed_at`) VALUES
	(1, 10, '$2y$12$a0SQJtEN8Og6awIUV9KD4e1W4zmJmH5qU4x1EHKwzOJusrCoK1dEu', '2025-11-04 12:08:10');

-- Exportiere Struktur von Tabelle d044f149.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=434 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.permissions: ~174 rows (ungefähr)
INSERT INTO `permissions` (`id`, `permission_key`, `display_name`, `description`, `category`) VALUES
	(1, 'markers_view', 'Marker ansehen', 'Kann Marker und deren Details ansehen', 'Marker'),
	(2, 'markers_create', 'Marker erstellen', 'Kann neue Marker erstellen', 'Marker'),
	(3, 'markers_edit', 'Marker bearbeiten', 'Kann bestehende Marker bearbeiten', 'Marker'),
	(4, 'markers_delete', 'Marker löschen', 'Kann Marker löschen (in Papierkorb)', 'Marker'),
	(5, 'markers_change_status', 'Status ändern', 'Kann Mietstatus von Geräten ändern', 'Marker'),
	(6, 'markers_update_position', 'Position aktualisieren', 'Kann GPS-Position aktualisieren', 'Marker'),
	(7, 'maintenance_add', 'Wartung durchführen', 'Kann Wartungen durchführen und eintragen', 'Wartung'),
	(8, 'maintenance_view_history', 'Wartungshistorie ansehen', 'Kann Wartungshistorie ansehen', 'Wartung'),
	(9, 'users_manage', 'Benutzer verwalten', 'Kann Benutzer erstellen, bearbeiten und löschen', 'Benutzer'),
	(10, 'roles_manage', 'Rollen verwalten', 'Kann Rollen und Berechtigungen verwalten', 'Rollen'),
	(11, 'settings_manage', 'Systemeinstellungen', 'Kann Systemeinstellungen ändern', 'System'),
	(23, 'markers_view_all', 'Alle Marker anzeigen', 'Kann alle Marker im System sehen', 'Marker'),
	(24, 'markers_view_own', 'Eigene Marker anzeigen', 'Kann nur selbst erstellte Marker sehen', 'Marker'),
	(25, 'markers_export', 'Marker exportieren', 'Kann Markerdaten exportieren (JSON)', 'Marker'),
	(26, 'markers_import', 'Marker importieren', 'Kann Markerdaten importieren', 'Marker'),
	(27, 'maintenance_view_all', 'Alle Wartungen anzeigen', 'Kann Wartungshistorie aller Geräte sehen', 'Wartung'),
	(28, 'maintenance_edit', 'Wartungen bearbeiten', 'Kann Wartungseinträge bearbeiten', 'Wartung'),
	(29, 'maintenance_delete', 'Wartung löschen', 'Kann Wartungseinträge löschen', 'Wartung'),
	(30, 'reports_view', 'Berichte ansehen', 'Kann Systemberichte und Statistiken ansehen', 'Berichte'),
	(31, 'reports_create', 'Berichte erstellen', 'Kann eigene Berichte erstellen', 'Berichte'),
	(32, 'reports_export', 'Berichte exportieren', 'Kann Berichte exportieren', 'Berichte'),
	(33, 'dashboard_access', 'Dashboard Zugriff', 'Kann das Dashboard sehen', 'Dashboard'),
	(34, 'map_view', 'Karte ansehen', 'Kann die Kartenansicht nutzen', 'System'),
	(35, 'advanced_search', 'Erweiterte Suche', 'Kann erweiterte Suchfunktionen nutzen', 'System'),
	(36, 'notifications_manage', 'Benachrichtigungen verwalten', 'Kann eigene Benachrichtigungseinstellungen verwalten', 'System'),
	(37, 'system_logs_view', 'Systemlogs ansehen', 'Kann Systemlogs und Aktivitäten einsehen', 'System'),
	(38, 'system_backup', 'System-Backup', 'Kann System-Backups erstellen', 'System'),
	(39, 'categories_manage', 'Kategorien verwalten', 'Kann Kategorien erstellen und bearbeiten', 'System'),
	(40, 'qr_scan', 'QR-Code Scannen', 'Kann QR-Codes scannen und Marker aktivieren', 'Marker'),
	(41, 'location_update', 'Standort aktualisieren', 'Kann GPS-Positionen von Markern aktualisieren', 'Marker'),
	(42, 'images_manage', 'Bilder verwalten', 'Kann Bilder hochladen und löschen', 'Medien'),
	(43, 'dashboard_view', 'Dashboard anzeigen', 'Zugriff auf das Statistik-Dashboard', 'Dashboard'),
	(44, 'dashboard_export', 'Dashboard exportieren', 'Dashboard-Daten als PDF/Excel exportieren', 'Dashboard'),
	(45, 'markers_bulk_edit', 'Marker Massenbearbeitung', 'Kann mehrere Marker gleichzeitig bearbeiten', 'Marker'),
	(46, 'maintenance_reports', 'Wartungsberichte', 'Wartungsberichte erstellen und exportieren', 'Wartung'),
	(47, 'status_override', 'Status überschreiben', 'Automatische Status-Änderungen überschreiben', 'Status'),
	(48, 'status_history', 'Status-Historie anzeigen', 'Verlauf aller Status-Änderungen einsehen', 'Status'),
	(49, 'images_upload', 'Bilder hochladen', 'Bilder zu Markern hochladen', 'Medien'),
	(50, 'images_delete', 'Bilder löschen', 'Bilder von Markern entfernen', 'Medien'),
	(51, 'documents_upload', 'Dokumente hochladen', 'Dokumente zu Markern hochladen', 'Medien'),
	(52, 'documents_delete', 'Dokumente löschen', 'Dokumente von Markern entfernen', 'Medien'),
	(53, 'reports_generate', 'Berichte erstellen', 'Systemweite Berichte generieren', 'Berichte'),
	(54, 'reports_schedule', 'Reports planen', 'Berechtigung zum Planen automatischer Report-Generierung', 'Reports'),
	(55, 'logs_view', 'System-Logs anzeigen', 'Berechtigung zum Einsehen der System-Logs', 'System'),
	(56, 'logs_export', 'Logs exportieren', 'Log-Dateien exportieren', 'System'),
	(57, 'notifications_send', 'Benachrichtigungen senden', 'Manuelle Benachrichtigungen versenden', 'Benachrichtigungen'),
	(67, 'custom_fields_manage', 'Custom Fields verwalten', 'Eigene Felder erstellen und bearbeiten', 'System'),
	(68, 'activity_log_view', 'Aktivitätsprotokoll ansehen', 'Zugriff auf das Aktivitätsprotokoll', 'System'),
	(73, 'comments_add', 'Kommentare schreiben', 'Kommentare zu Markern hinzufügen', 'Marker'),
	(74, 'comments_delete', 'Kommentare löschen', 'Eigene und fremde Kommentare löschen', 'Marker'),
	(77, 'statistics_view', 'Statistiken ansehen', 'Nutzungsstatistiken einsehen', 'Berichte'),
	(78, 'settings_dark_mode', 'Dark Mode', 'Dark Mode verwenden', 'System'),
	(82, 'comments_edit', 'Kommentare bearbeiten', 'Eigene Kommentare bearbeiten', 'Marker'),
	(91, 'inactive_markers_view', 'Nicht-aktivierte Marker ansehen', 'Kann Liste nicht-aktivierter Marker sehen', 'Marker'),
	(92, 'qr_generate', 'QR-Codes generieren', 'Kann neue QR-Codes im Pool generieren', 'QR-Codes'),
	(93, 'qr_print', 'QR-Codes drucken', 'Kann QR-Codes drucken/exportieren', 'QR-Codes'),
	(94, 'qr_manage', 'QR-Code Pool verwalten', 'Kann QR-Code Pool verwalten und Codes deaktivieren', 'QR-Codes'),
	(100, 'users_view', 'Benutzer ansehen', 'Kann Benutzerliste einsehen', 'Benutzer'),
	(101, 'users_create', 'Benutzer erstellen', 'Berechtigung zum Erstellen neuer Benutzer', 'Benutzer'),
	(102, 'users_edit', 'Benutzer bearbeiten', 'Kann Benutzer bearbeiten', 'Benutzer'),
	(103, 'users_delete', 'Benutzer löschen', 'Berechtigung zum Löschen von Benutzern', 'Benutzer'),
	(104, 'users_change_role', 'Rolle ändern', 'Kann Benutzerrollen ändern', 'Benutzer'),
	(105, 'users_reset_password', 'Passwort zurücksetzen', 'Kann Benutzerpasswörter zurücksetzen', 'Benutzer'),
	(110, 'roles_view', 'Rollen ansehen', 'Kann Rollenliste einsehen', 'Rollen'),
	(111, 'roles_create', 'Rollen erstellen', 'Berechtigung zum Erstellen neuer Rollen', 'Rollen'),
	(112, 'roles_edit', 'Rollen bearbeiten', 'Kann Rollen bearbeiten', 'Rollen'),
	(113, 'roles_delete', 'Rollen löschen', 'Berechtigung zum Löschen von Rollen (außer System-Rollen)', 'Rollen'),
	(114, 'roles_assign_permissions', 'Berechtigungen zuweisen', 'Kann Permissions zu Rollen zuweisen', 'Rollen'),
	(120, 'documents_view', 'Dokumente ansehen', 'Kann Dokumente herunterladen und ansehen', 'Medien'),
	(121, 'documents_edit', 'Dokumente bearbeiten', 'Kann Dokumenteigenschaften bearbeiten', 'Medien'),
	(130, 'settings_view', 'Einstellungen ansehen', 'Kann Systemeinstellungen einsehen', 'System'),
	(131, 'settings_edit', 'Einstellungen bearbeiten', 'Kann Systemeinstellungen ändern', 'System'),
	(140, 'qr_view', 'QR-Codes ansehen', 'Kann QR-Code Pool einsehen', 'QR-Codes'),
	(141, 'qr_assign', 'QR-Codes zuweisen', 'Berechtigung zum manuellen Zuweisen von QR-Codes zu Markern', 'QR-Codes'),
	(142, 'qr_batch_print', 'QR-Codes stapelweise drucken', 'Berechtigung zum Stapeldruck mehrerer QR-Codes', 'QR-Codes'),
	(143, 'qr_scan_history_view', 'Scan-Historie ansehen', 'Kann QR-Code Scan-Historie einsehen', 'QR-Codes'),
	(144, 'qr_branding_manage', 'QR-Branding verwalten', 'Kann Branding-Templates für QR-Codes erstellen', 'QR-Codes'),
	(146, 'dashboard_analytics', 'Analytics Dashboard', 'Zugriff auf erweiterte Analytics', 'Dashboard'),
	(147, 'documents_expiry_manage', 'Dokumenten-Ablauf verwalten', 'Kann Ablaufdaten für Dokumente setzen', 'Medien'),
	(151, 'markers_duplicate', 'Marker duplizieren', 'Berechtigung zum Duplizieren von Markern mit allen Eigenschaften', 'Marker'),
	(152, 'markers_history_view', 'Marker-Historie anzeigen', 'Berechtigung zum Anzeigen der vollständigen Marker-Historie und Änderungsprotokolle', 'Marker'),
	(153, 'marker_templates_manage', 'Marker-Templates verwalten', 'Kann Templates erstellen und verwalten', 'Marker'),
	(154, 'marker_templates_use', 'Marker-Templates verwenden', 'Kann Templates beim Erstellen verwenden', 'Marker'),
	(155, 'maintenance_timeline_view', 'Wartungszeitleiste ansehen', 'Kann Wartungsübersicht mit Zeitleiste sehen', 'Wartung'),
	(156, 'maintenance_signature', 'Wartung signieren', 'Kann Wartungen mit digitaler Signatur bestätigen', 'Wartung'),
	(157, 'signature_manage', 'Signatur verwalten', 'Kann eigene digitale Signatur erstellen/ändern', 'Benutzer'),
	(158, 'dashboard_charts_view', 'Dashboard-Diagramme ansehen', 'Kann erweiterte Dashboard-Statistiken mit Diagrammen sehen', 'Dashboard'),
	(159, 'geofence_restrictions_manage', 'Geo-Fence Einschränkungen verwalten', 'Kann Geräte-Einschränkungen für Geo-Fences verwalten', 'Geo-Fences'),
	(160, 'activity_log_advanced', 'Erweitertes Aktivitätsprotokoll', 'Kann alle erweiterten Aktivitäten sehen', 'System'),
	(161, 'markers_bulk_delete', 'Marker Massenlöschung', 'Berechtigung zum gleichzeitigen Löschen mehrerer Marker', 'Marker'),
	(162, 'markers_activate', 'Marker aktivieren/deaktivieren', 'Berechtigung zum Ändern des Aktivierungsstatus von Markern', 'Marker'),
	(163, 'markers_location_edit', 'GPS-Position bearbeiten', 'Berechtigung zum manuellen Ändern der GPS-Koordinaten', 'Marker'),
	(164, 'maintenance_approve', 'Wartungen genehmigen', 'Berechtigung zum Genehmigen von geplanten Wartungen', 'Wartung'),
	(165, 'maintenance_complete', 'Wartungen abschließen', 'Berechtigung zum Abschließen und Bestätigen von Wartungen', 'Wartung'),
	(166, 'maintenance_schedule', 'Wartungen planen', 'Berechtigung zum Planen zukünftiger Wartungen', 'Wartung'),
	(167, 'maintenance_export', 'Wartungen exportieren', 'Berechtigung zum Exportieren der Wartungshistorie', 'Wartung'),
	(168, 'maintenance_costs_view', 'Wartungskosten anzeigen', 'Berechtigung zum Einsehen der Wartungskosten', 'Wartung'),
	(169, 'qr_pool_manage', 'QR-Code Pool verwalten', 'Berechtigung zum Verwalten des QR-Code Pools (hinzufügen, löschen)', 'QR-Codes'),
	(170, 'nfc_pool_manage', 'NFC-Chip Pool verwalten', 'Berechtigung zum Verwalten des NFC-Chip Pools', 'NFC'),
	(171, 'nfc_assign', 'NFC-Chips zuweisen', 'Berechtigung zum Zuweisen von NFC-Chips zu Markern', 'NFC'),
	(172, 'users_password_reset', 'Passwörter zurücksetzen', 'Berechtigung zum Zurücksetzen von Benutzer-Passwörtern', 'Benutzer'),
	(173, 'users_permissions_edit', 'Berechtigungen bearbeiten', 'Berechtigung zum Ändern der Benutzer-Berechtigungen', 'Benutzer'),
	(174, 'users_activity_view', 'Benutzer-Aktivitäten anzeigen', 'Berechtigung zum Einsehen der Aktivitäten einzelner Benutzer', 'Benutzer'),
	(175, 'roles_permissions_edit', 'Rollen-Berechtigungen bearbeiten', 'Berechtigung zum Ändern der Berechtigungen einer Rolle', 'Rollen'),
	(176, 'geofence_create', 'Geo-Fences erstellen', 'Berechtigung zum Erstellen neuer Geo-Fences', 'Geo-Fence'),
	(177, 'geofence_edit', 'Geo-Fences bearbeiten', 'Berechtigung zum Bearbeiten bestehender Geo-Fences', 'Geo-Fence'),
	(178, 'geofence_delete', 'Geo-Fences löschen', 'Berechtigung zum Löschen von Geo-Fences', 'Geo-Fence'),
	(179, 'geofence_groups_manage', 'Geo-Fence Gruppen verwalten', 'Berechtigung zum Verwalten von Geo-Fence Gruppen', 'Geo-Fence'),
	(180, 'reports_advanced', 'Erweiterte Reports', 'Berechtigung zum Erstellen erweiterter und benutzerdefinierter Reports', 'Reports'),
	(181, 'analytics_view', 'Analytics anzeigen', 'Berechtigung zum Anzeigen von Analytics und Statistiken', 'Reports'),
	(182, 'analytics_export', 'Analytics exportieren', 'Berechtigung zum Exportieren von Analytics-Daten', 'Reports'),
	(183, 'settings_system', 'System-Einstellungen', 'Berechtigung zum Ändern kritischer System-Einstellungen', 'System'),
	(184, 'settings_security', 'Sicherheits-Einstellungen', 'Berechtigung zum Ändern von Sicherheits-Einstellungen', 'System'),
	(185, 'settings_backup', 'Backups verwalten', 'Berechtigung zum Erstellen und Wiederherstellen von Backups', 'System'),
	(186, 'logs_delete', 'System-Logs löschen', 'Berechtigung zum Löschen alter Log-Einträge', 'System'),
	(187, 'custom_fields_create', 'Custom Fields erstellen', 'Berechtigung zum Erstellen neuer benutzerdefinierter Felder', 'Custom Fields'),
	(188, 'custom_fields_delete', 'Custom Fields löschen', 'Berechtigung zum Löschen benutzerdefinierter Felder', 'Custom Fields'),
	(189, 'templates_create', 'Vorlagen erstellen', 'Berechtigung zum Erstellen von Marker-Vorlagen', 'Templates'),
	(190, 'templates_edit', 'Vorlagen bearbeiten', 'Berechtigung zum Bearbeiten von Marker-Vorlagen', 'Templates'),
	(191, 'templates_delete', 'Vorlagen löschen', 'Berechtigung zum Löschen von Marker-Vorlagen', 'Templates'),
	(192, 'categories_create', 'Kategorien erstellen', 'Berechtigung zum Erstellen neuer Kategorien', 'Kategorien'),
	(193, 'categories_edit', 'Kategorien bearbeiten', 'Berechtigung zum Bearbeiten bestehender Kategorien', 'Kategorien'),
	(194, 'categories_delete', 'Kategorien löschen', 'Berechtigung zum Löschen von Kategorien', 'Kategorien'),
	(195, 'inspections_create', 'Prüfungen erstellen', 'Berechtigung zum Erstellen von Prüfungen/Inspektionen', 'Prüfungen'),
	(196, 'inspections_edit', 'Prüfungen bearbeiten', 'Berechtigung zum Bearbeiten von Prüfungen', 'Prüfungen'),
	(197, 'inspections_delete', 'Prüfungen löschen', 'Berechtigung zum Löschen von Prüfungen', 'Prüfungen'),
	(198, 'inspections_complete', 'Prüfungen abschließen', 'Berechtigung zum Abschließen von Prüfungen', 'Prüfungen'),
	(199, 'bugs_create', 'Bug-Tickets erstellen', 'Berechtigung zum Erstellen von Bug-Reports', 'Bug-Tracking'),
	(200, 'bugs_edit', 'Bug-Tickets bearbeiten', 'Berechtigung zum Bearbeiten von Bug-Reports', 'Bug-Tracking'),
	(201, 'bugs_delete', 'Bug-Tickets löschen', 'Berechtigung zum Löschen von Bug-Reports', 'Bug-Tracking'),
	(202, 'bugs_assign', 'Bug-Tickets zuweisen', 'Berechtigung zum Zuweisen von Bug-Tickets an Benutzer', 'Bug-Tracking'),
	(203, 'bugs_close', 'Bug-Tickets schließen', 'Berechtigung zum Schließen von Bug-Tickets', 'Bug-Tracking'),
	(204, 'public_view_all', 'Alle öffentlichen Marker anzeigen', 'Berechtigung zum Anzeigen aller öffentlich zugänglichen Marker', 'Öffentlich'),
	(205, 'public_edit', 'Öffentliche Marker bearbeiten', 'Berechtigung zum Bearbeiten öffentlich zugänglicher Marker', 'Öffentlich'),
	(206, 'trash_view', 'Papierkorb anzeigen', 'Berechtigung zum Anzeigen gelöschter Marker', 'Papierkorb'),
	(207, 'trash_restore', 'Marker wiederherstellen', 'Berechtigung zum Wiederherstellen gelöschter Marker', 'Papierkorb'),
	(208, 'trash_delete_permanent', 'Marker endgültig löschen', 'Berechtigung zum endgültigen Löschen von Markern', 'Papierkorb'),
	(341, '', 'maps_measure_distance', 'Tags anzeigen', 'tags'),
	(371, 'tags_view', 'Tags ansehen', 'Kann Tags ansehen', 'Tags'),
	(372, 'tags_manage', 'Tags verwalten', 'Kann Tags erstellen, bearbeiten und löschen', 'Tags'),
	(373, 'tags_assign', 'Tags zuweisen', 'Kann Tags zu Markern zuweisen', 'Tags'),
	(374, 'tags_create', 'Tags erstellen', 'Kann neue Tags erstellen', 'Tags'),
	(375, 'tags_edit', 'Tags bearbeiten', 'Kann bestehende Tags bearbeiten', 'Tags'),
	(376, 'tags_delete', 'Tags löschen', 'Kann Tags löschen', 'Tags'),
	(377, 'models_3d_view', '3D-Modelle anzeigen', 'Kann 3D-Modelle ansehen', '3D-Modelle'),
	(378, 'models_3d_upload', '3D-Modelle hochladen', 'Kann 3D-Modelle hochladen', '3D-Modelle'),
	(379, 'models_3d_download', '3D-Modelle herunterladen', 'Kann 3D-Modelle herunterladen', '3D-Modelle'),
	(380, 'models_3d_delete', '3D-Modelle löschen', 'Kann 3D-Modelle löschen', '3D-Modelle'),
	(381, 'models_3d_manage', '3D-Modelle verwalten', 'Kann 3D-Modelle vollständig verwalten', '3D-Modelle'),
	(382, 'ar_navigation_use', 'AR-Navigation verwenden', 'Kann AR-Navigation verwenden', 'AR'),
	(383, 'ar_markers_view', 'AR-Marker anzeigen', 'Kann AR-Marker anzeigen', 'AR'),
	(384, 'ar_markers_manage', 'AR-Marker konfigurieren', 'Kann AR-Marker konfigurieren und verwalten', 'AR'),
	(385, 'calendar_view', 'Kalender anzeigen', 'Kann den Kalender ansehen', 'Kalender'),
	(386, 'calendar_create_events', 'Kalender-Events erstellen', 'Kann Kalender-Events erstellen', 'Kalender'),
	(387, 'calendar_edit_events', 'Kalender-Events bearbeiten', 'Kann Kalender-Events bearbeiten', 'Kalender'),
	(388, 'calendar_delete_events', 'Kalender-Events löschen', 'Kann Kalender-Events löschen', 'Kalender'),
	(389, 'calendar_sync_outlook', 'Outlook-Integration verwenden', 'Kann Outlook-Integration verwenden', 'Kalender'),
	(390, 'calendar_sync_google', 'Google Calendar-Integration verwenden', 'Kann Google Calendar-Integration verwenden', 'Kalender'),
	(391, 'calendar_settings', 'Kalender-Einstellungen verwalten', 'Kann Kalender-Einstellungen verwalten', 'Kalender'),
	(392, 'calendar_auto_maintenance', 'Automatische Wartungstermine aktivieren', 'Kann automatische Wartungstermine aktivieren', 'Kalender'),
	(393, 'camera_use_advanced', 'Erweiterte Kamera-Funktionen verwenden', 'Kann erweiterte Kamera-Funktionen verwenden', 'Kamera'),
	(394, 'photos_edit', 'Fotos bearbeiten', 'Kann Fotos bearbeiten (drehen, zuschneiden)', 'Kamera'),
	(395, 'photos_annotations', 'Fotos annotieren', 'Kann Fotos annotieren (Markierungen, Text)', 'Kamera'),
	(396, 'photos_bulk_upload', 'Mehrere Fotos gleichzeitig hochladen', 'Kann mehrere Fotos gleichzeitig hochladen', 'Kamera'),
	(397, 'maps_cluster_view', 'Marker-Clustering auf Karte anzeigen', 'Kann Marker-Clustering auf Karte anzeigen', 'Karten'),
	(398, 'maps_heatmap', 'Heatmap-Ansicht verwenden', 'Kann Heatmap-Ansicht verwenden', 'Karten'),
	(399, 'maps_export', 'Karte exportieren', 'Kann Karte exportieren', 'Karten'),
	(400, 'maps_measure_distance', 'Entfernungen auf Karte messen', 'Kann Entfernungen auf Karte messen', 'Karten'),
	(428, 'manage_checklists', 'Wartungs-Checklisten verwalten', NULL, 'maintenance'),
	(429, 'perform_maintenance', 'Wartungen durchführen', NULL, 'maintenance'),
	(430, 'upload_documents', 'Dokumente hochladen', NULL, 'documents'),
	(431, 'manage_documents', 'Dokumente verwalten', NULL, 'documents'),
	(432, 'view_documents', 'Dokumente ansehen', NULL, 'documents'),
	(433, 'calendar_integration', 'Kalender-Integration nutzen', NULL, 'calendar');

-- Exportiere Struktur von Tabelle d044f149.photo_annotations
CREATE TABLE IF NOT EXISTS `photo_annotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `annotation_data` text DEFAULT NULL COMMENT 'JSON mit Markierungen, Text, etc.',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `photo_annotations_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `photo_annotations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.photo_annotations: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.public_view_login_attempts
CREATE TABLE IF NOT EXISTS `public_view_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_marker_ip` (`marker_id`,`ip_address`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Login-Versuche für Public View (Rate Limiting)';

-- Exportiere Daten aus Tabelle d044f149.public_view_login_attempts: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.public_view_trusted_devices
CREATE TABLE IF NOT EXISTS `public_view_trusted_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL COMMENT 'Marker zu dem das Gerät Zugriff hat',
  `device_token` varchar(64) NOT NULL COMMENT 'Eindeutiger Token für dieses Gerät',
  `device_fingerprint` varchar(255) NOT NULL COMMENT 'Browser-Fingerprint (User-Agent + weitere Infos)',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP-Adresse bei Erstellung',
  `last_access` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Letzter Zugriff',
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Ablaufdatum (30 Tage)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_device_marker` (`device_token`,`marker_id`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_device_token` (`device_token`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_trusted_device_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vertrauenswürdige Geräte für Public View ohne wiederholtes Login';

-- Exportiere Daten aus Tabelle d044f149.public_view_trusted_devices: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.pwa_settings
CREATE TABLE IF NOT EXISTS `pwa_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `install_prompted` tinyint(1) DEFAULT 0,
  `installed` tinyint(1) DEFAULT 0,
  `last_sync` datetime DEFAULT NULL,
  `offline_enabled` tinyint(1) DEFAULT 1,
  `push_enabled` tinyint(1) DEFAULT 0,
  `push_subscription` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `pwa_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.pwa_settings: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.qr_branding
CREATE TABLE IF NOT EXISTS `qr_branding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_is_default` (`is_default`),
  KEY `fk_qr_branding_user` (`created_by`),
  CONSTRAINT `fk_qr_branding_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Logo-Dateien für Branded QR-Codes';

-- Exportiere Daten aus Tabelle d044f149.qr_branding: ~1 rows (ungefähr)
INSERT INTO `qr_branding` (`id`, `name`, `logo_path`, `is_default`, `created_at`, `created_by`) VALUES
	(1, 'Logo', 'uploads/qr-logos/logo_1760285449_68ebd3090f2bd.png', 1, '2025-10-12 16:10:49', 1);

-- Exportiere Struktur von Tabelle d044f149.qr_code_pool
CREATE TABLE IF NOT EXISTS `qr_code_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_code` varchar(100) NOT NULL COMMENT 'QR-Code Nummer (z.B. QR-0001)',
  `batch_id` varchar(100) DEFAULT NULL,
  `is_assigned` tinyint(1) DEFAULT 0 COMMENT 'Ist der QR-Code bereits einem Marker zugewiesen?',
  `is_activated` tinyint(1) DEFAULT 0 COMMENT 'Wurde der QR-Code vor Ort aktiviert?',
  `marker_id` int(11) DEFAULT NULL COMMENT 'Zugewiesener Marker (wenn assigned)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_at` timestamp NULL DEFAULT NULL COMMENT 'Wann wurde er zugewiesen?',
  `print_batch` varchar(50) DEFAULT NULL COMMENT 'Druck-Batch zur Identifikation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_code` (`qr_code`),
  KEY `marker_id` (`marker_id`),
  KEY `idx_is_assigned` (`is_assigned`),
  KEY `batch_id` (`batch_id`),
  KEY `idx_is_activated` (`is_activated`),
  KEY `idx_qr_pool_status` (`is_assigned`,`is_activated`),
  KEY `idx_marker_id` (`marker_id`),
  CONSTRAINT `qr_code_pool_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.qr_code_pool: ~10 rows (ungefähr)
INSERT INTO `qr_code_pool` (`id`, `qr_code`, `batch_id`, `is_assigned`, `is_activated`, `marker_id`, `created_at`, `assigned_at`, `print_batch`) VALUES
	(1, 'QR-0001', NULL, 1, 1, 31, '2025-10-07 19:58:20', '2025-11-10 20:22:43', 'BATCH_2025-10-07_215820'),
	(2, 'QR-0002', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(3, 'QR-0003', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(4, 'QR-0004', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(5, 'QR-0005', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(6, 'QR-0006', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(7, 'QR-0007', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(8, 'QR-0008', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(9, 'QR-0009', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820'),
	(10, 'QR-0010', NULL, 0, 0, NULL, '2025-10-07 19:58:20', NULL, 'BATCH_2025-10-07_215820');

-- Exportiere Struktur von Prozedur d044f149.queue_notification_emails
DELIMITER //
CREATE PROCEDURE `queue_notification_emails`()
BEGIN
    
    INSERT INTO email_notification_queue (notification_type, related_id, related_type, recipient_email, subject, body)
    SELECT 
        CASE 
            WHEN m.next_maintenance < CURDATE() THEN 'maintenance_overdue'
            ELSE 'maintenance_due'
        END,
        m.id,
        'marker',
        u.email,
        CONCAT('Wartung fällig: ', m.name),
        CONCAT('Das Gerät "', m.name, '" (QR: ', m.qr_code, ') benötigt eine Wartung.')
    FROM markers m
    CROSS JOIN users u
    WHERE m.deleted_at IS NULL
      AND m.maintenance_required = 1
      AND m.next_maintenance IS NOT NULL
      AND DATEDIFF(m.next_maintenance, CURDATE()) <= 7
      AND u.receive_maintenance_emails = 1
      AND NOT EXISTS (
          SELECT 1 FROM email_notification_queue 
          WHERE related_id = m.id 
            AND related_type = 'marker'
            AND notification_type IN ('maintenance_due', 'maintenance_overdue')
            AND DATE(created_at) = CURDATE()
      );
    
    
    INSERT INTO email_notification_queue (notification_type, related_id, related_type, recipient_email, subject, body)
    SELECT 
        CASE 
            WHEN i.next_inspection < CURDATE() THEN 'inspection_overdue'
            ELSE 'inspection_due'
        END,
        i.id,
        'inspection',
        u.email,
        CONCAT('Prüfung fällig: ', m.name),
        CONCAT('Das Gerät "', m.name, '" benötigt eine ', i.inspection_type, '-Prüfung.')
    FROM inspection_schedules i
    JOIN markers m ON i.marker_id = m.id
    CROSS JOIN users u
    WHERE m.deleted_at IS NULL
      AND i.next_inspection IS NOT NULL
      AND DATEDIFF(i.next_inspection, CURDATE()) <= i.notification_days_before
      AND u.receive_maintenance_emails = 1
      AND NOT EXISTS (
          SELECT 1 FROM email_notification_queue 
          WHERE related_id = i.id 
            AND related_type = 'inspection'
            AND notification_type IN ('inspection_due', 'inspection_overdue')
            AND DATE(created_at) = CURDATE()
      );
    
    
    INSERT INTO email_notification_queue (notification_type, related_id, related_type, recipient_email, subject, body)
    SELECT 
        'document_expiry',
        d.id,
        'document',
        u.email,
        CONCAT('Dokument läuft ab: ', d.document_name),
        CONCAT('Das Dokument "', d.document_name, '" für Gerät "', m.name, '" läuft am ', DATE_FORMAT(d.expiry_date, '%d.%m.%Y'), ' ab.')
    FROM marker_documents d
    JOIN markers m ON d.marker_id = m.id
    CROSS JOIN users u
    WHERE m.deleted_at IS NULL
      AND d.expiry_date IS NOT NULL
      AND DATEDIFF(d.expiry_date, CURDATE()) <= d.notification_days_before
      AND d.document_status IN ('läuft_ab', 'abgelaufen')
      AND u.receive_maintenance_emails = 1
      AND NOT EXISTS (
          SELECT 1 FROM email_notification_queue 
          WHERE related_id = d.id 
            AND related_type = 'document'
            AND notification_type = 'document_expiry'
            AND DATE(created_at) = CURDATE()
      );
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.remember_tokens
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_token` (`user_id`,`token`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.remember_tokens: ~1 rows (ungefähr)
INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`, `ip_address`, `user_agent`) VALUES
	(1, 1, '29a268432e1e77342613144b38581e41f827c9315268b835c9b7c2295243d006', '2025-11-06 06:21:54', '2025-10-07 06:21:54', '94.31.75.153', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1');

-- Exportiere Struktur von Tabelle d044f149.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.roles: ~6 rows (ungefähr)
INSERT INTO `roles` (`id`, `role_name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
	(1, 'admin', 'Administrator', 'Volle Systemrechte - kann alles', 1, '2025-10-02 19:07:41', '2025-10-02 19:07:41'),
	(2, 'user', 'Benutzer', 'Standard-Benutzer - kann Marker verwalten', 1, '2025-10-02 19:07:41', '2025-10-02 19:07:41'),
	(3, 'viewer', 'Betrachter', 'Nur Lesezugriff', 1, '2025-10-02 19:07:41', '2025-10-02 19:07:41'),
	(7, 'maintenance_technician', 'Wartungstechniker', 'Rolle für Wartungspersonal mit erweiterten Wartungsrechten', 0, '2025-10-17 04:15:46', '2025-10-17 04:15:46'),
	(8, 'warehouse_manager', 'Lagerverwalter', 'Rolle für Lagerverwaltung mit Fokus auf Lagergeräte', 0, '2025-10-17 04:15:46', '2025-10-17 04:15:46'),
	(9, 'qr_manager', 'QR-Code Manager', 'Rolle für die Verwaltung von QR-Codes und NFC-Chips', 0, '2025-10-17 04:15:46', '2025-10-17 04:15:46');

-- Exportiere Struktur von Tabelle d044f149.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.role_permissions: ~254 rows (ungefähr)
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(1, 6),
	(1, 7),
	(1, 8),
	(1, 9),
	(1, 10),
	(1, 11),
	(1, 23),
	(1, 24),
	(1, 25),
	(1, 26),
	(1, 27),
	(1, 28),
	(1, 29),
	(1, 30),
	(1, 31),
	(1, 32),
	(1, 33),
	(1, 34),
	(1, 35),
	(1, 36),
	(1, 37),
	(1, 38),
	(1, 39),
	(1, 40),
	(1, 41),
	(1, 42),
	(1, 43),
	(1, 44),
	(1, 45),
	(1, 46),
	(1, 47),
	(1, 48),
	(1, 49),
	(1, 50),
	(1, 51),
	(1, 52),
	(1, 53),
	(1, 54),
	(1, 55),
	(1, 56),
	(1, 57),
	(1, 67),
	(1, 68),
	(1, 73),
	(1, 74),
	(1, 77),
	(1, 78),
	(1, 82),
	(1, 91),
	(1, 92),
	(1, 93),
	(1, 94),
	(1, 100),
	(1, 101),
	(1, 102),
	(1, 103),
	(1, 104),
	(1, 105),
	(1, 110),
	(1, 111),
	(1, 112),
	(1, 113),
	(1, 114),
	(1, 120),
	(1, 121),
	(1, 130),
	(1, 131),
	(1, 140),
	(1, 141),
	(1, 142),
	(1, 143),
	(1, 144),
	(1, 146),
	(1, 147),
	(1, 151),
	(1, 152),
	(1, 153),
	(1, 154),
	(1, 155),
	(1, 156),
	(1, 157),
	(1, 158),
	(1, 159),
	(1, 160),
	(1, 161),
	(1, 162),
	(1, 163),
	(1, 164),
	(1, 165),
	(1, 166),
	(1, 167),
	(1, 168),
	(1, 169),
	(1, 170),
	(1, 171),
	(1, 172),
	(1, 173),
	(1, 174),
	(1, 175),
	(1, 176),
	(1, 177),
	(1, 178),
	(1, 179),
	(1, 180),
	(1, 181),
	(1, 182),
	(1, 183),
	(1, 184),
	(1, 185),
	(1, 186),
	(1, 187),
	(1, 188),
	(1, 189),
	(1, 190),
	(1, 191),
	(1, 192),
	(1, 193),
	(1, 194),
	(1, 195),
	(1, 196),
	(1, 197),
	(1, 198),
	(1, 199),
	(1, 200),
	(1, 201),
	(1, 202),
	(1, 203),
	(1, 204),
	(1, 205),
	(1, 206),
	(1, 207),
	(1, 208),
	(1, 341),
	(1, 371),
	(1, 372),
	(1, 373),
	(1, 374),
	(1, 375),
	(1, 376),
	(1, 377),
	(1, 378),
	(1, 379),
	(1, 380),
	(1, 381),
	(1, 382),
	(1, 383),
	(1, 384),
	(1, 385),
	(1, 386),
	(1, 387),
	(1, 388),
	(1, 389),
	(1, 390),
	(1, 391),
	(1, 392),
	(1, 393),
	(1, 394),
	(1, 395),
	(1, 396),
	(1, 397),
	(1, 398),
	(1, 399),
	(1, 400),
	(2, 1),
	(2, 2),
	(2, 3),
	(2, 5),
	(2, 6),
	(2, 7),
	(2, 8),
	(2, 23),
	(2, 27),
	(2, 33),
	(2, 34),
	(2, 35),
	(2, 36),
	(2, 40),
	(2, 41),
	(2, 42),
	(2, 43),
	(2, 48),
	(2, 49),
	(2, 68),
	(2, 92),
	(2, 93),
	(2, 151),
	(2, 152),
	(2, 154),
	(2, 155),
	(2, 156),
	(2, 157),
	(2, 158),
	(2, 162),
	(2, 195),
	(2, 198),
	(2, 199),
	(3, 1),
	(3, 8),
	(3, 23),
	(3, 27),
	(3, 33),
	(3, 34),
	(3, 43),
	(3, 48),
	(3, 53),
	(3, 68),
	(3, 77),
	(3, 152),
	(3, 155),
	(3, 204),
	(3, 206),
	(7, 1),
	(7, 3),
	(7, 7),
	(7, 29),
	(7, 68),
	(7, 152),
	(7, 155),
	(7, 164),
	(7, 165),
	(7, 166),
	(7, 167),
	(7, 195),
	(7, 198),
	(8, 1),
	(8, 2),
	(8, 3),
	(8, 4),
	(8, 25),
	(8, 26),
	(8, 39),
	(8, 45),
	(8, 68),
	(8, 77),
	(8, 92),
	(8, 93),
	(8, 142),
	(8, 151),
	(8, 152),
	(9, 1),
	(9, 92),
	(9, 93),
	(9, 94),
	(9, 141),
	(9, 142),
	(9, 144),
	(9, 169),
	(9, 170),
	(9, 171);

-- Exportiere Struktur von Tabelle d044f149.saved_filters
CREATE TABLE IF NOT EXISTS `saved_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `filter_name` varchar(100) NOT NULL,
  `filter_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`filter_data`)),
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `saved_filters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.saved_filters: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.saved_searches
CREATE TABLE IF NOT EXISTS `saved_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `search_name` varchar(100) NOT NULL,
  `search_params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  `use_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `saved_searches_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.saved_searches: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.scan_history
CREATE TABLE IF NOT EXISTS `scan_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_code` varchar(100) NOT NULL,
  `marker_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `scan_type` enum('activation','view','update') DEFAULT 'view',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_qr_code` (`qr_code`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_scanned_at` (`scanned_at`),
  KEY `idx_scan_qr_date` (`qr_code`,`scanned_at`),
  CONSTRAINT `fk_scan_history_marker` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_scan_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historie aller QR-Code Scans mit Zeitstempel und Position';

-- Exportiere Daten aus Tabelle d044f149.scan_history: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.security_notifications
CREATE TABLE IF NOT EXISTS `security_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_type` (`user_id`,`notification_type`),
  CONSTRAINT `security_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.security_notifications: ~1 rows (ungefähr)
INSERT INTO `security_notifications` (`id`, `user_id`, `notification_type`, `details`, `sent_at`) VALUES
	(1, 10, 'password_changed', '[]', '2025-11-04 12:08:11');

-- Exportiere Struktur von Prozedur d044f149.send_inspection_notifications
DELIMITER //
CREATE PROCEDURE `send_inspection_notifications`()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_inspection_id INT;
    DECLARE v_marker_id INT;
    DECLARE v_days_until INT;
    DECLARE v_notification_type VARCHAR(20);
    
    DECLARE cur CURSOR FOR 
        SELECT 
            id,
            marker_id,
            days_until_due,
            CASE 
                WHEN days_until_due < 0 THEN 'überfällig'
                WHEN days_until_due <= 7 THEN 'fällig'
                ELSE 'warnung'
            END as notification_type
        FROM v_due_inspections
        WHERE priority_level > 0
          AND (last_notification_sent IS NULL 
               OR last_notification_sent < CURDATE() - INTERVAL 7 DAY);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_inspection_id, v_marker_id, v_days_until, v_notification_type;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        
        
        INSERT INTO inspection_notifications (
            inspection_id,
            marker_id,
            notification_type,
            sent_to,
            days_until_due
        ) VALUES (
            v_inspection_id,
            v_marker_id,
            v_notification_type,
            'system@example.com',
            v_days_until
        );
        
        
        UPDATE inspection_schedules 
        SET last_notification_sent = CURDATE()
        WHERE id = v_inspection_id;
        
    END LOOP;
    
    CLOSE cur;
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1015 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.system_settings: ~84 rows (ungefähr)
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`, `description`) VALUES
	(1, 'map_default_lat', '49.994502', '2025-10-05 08:45:46', NULL),
	(2, 'map_default_lng', '9.0721707', '2025-10-05 08:45:46', NULL),
	(3, 'map_default_zoom', '19', '2025-10-04 05:16:24', NULL),
	(4, 'marker_size', 'small', '2025-10-04 05:16:24', NULL),
	(5, 'marker_pulse', '0', '2025-10-03 18:33:32', NULL),
	(6, 'marker_hover_scale', '1', '2025-10-04 05:16:24', NULL),
	(19, 'email_from', '', '2025-10-03 07:05:58', NULL),
	(20, 'email_from_name', 'RFID System', '2025-10-03 07:05:58', NULL),
	(21, 'email_enabled', '0', '2025-10-03 07:05:58', NULL),
	(22, 'maintenance_check_days_before', '7', '2025-10-02 19:42:44', NULL),
	(30, 'storage_marker_color', '#28a745', '2025-10-03 06:56:38', NULL),
	(31, 'show_legend', '1', '2025-10-03 06:56:38', NULL),
	(32, 'show_notifications', '1', '2025-10-03 06:56:38', NULL),
	(33, 'auto_save_interval', '5', '2025-10-03 06:56:38', NULL),
	(70, 'show_map_legend', '1', '2025-10-04 05:01:51', NULL),
	(71, 'show_system_messages', '0', '2025-10-04 04:46:56', NULL),
	(76, 'system_name', 'BGG Geräte Verwaltung', '2025-10-06 18:52:16', NULL),
	(597, 'bug_report_email', 'doofwiescheisse@outlook.de', '2025-10-05 18:02:50', NULL),
	(598, 'bug_report_enabled', '1', '2025-10-05 12:24:07', NULL),
	(599, 'footer_copyright', '© 2025 RFID Marker System', '2025-10-05 12:24:07', NULL),
	(600, 'footer_company', 'Ihr Firmenname', '2025-10-05 12:24:07', NULL),
	(601, 'impressum_url', '/impressum.php', '2025-10-05 12:24:07', NULL),
	(602, 'datenschutz_url', '/datenschutz.php', '2025-10-05 12:24:07', NULL),
	(622, 'qr_scan_history_enabled', '1', '2025-10-09 19:11:45', NULL),
	(623, 'qr_scan_history_retention_days', '90', '2025-10-09 19:11:45', NULL),
	(624, 'email_notification_enabled', '1', '2025-10-09 19:11:45', NULL),
	(625, 'email_notification_batch_size', '50', '2025-10-09 19:11:45', NULL),
	(626, 'dashboard_refresh_interval', '300', '2025-10-09 19:11:45', NULL),
	(627, 'analytics_retention_months', '12', '2025-10-09 19:11:45', NULL),
	(628, 'document_expiry_default_days', '14', '2025-10-09 19:11:45', NULL),
	(629, 'qr_logo_enabled', '1', '2025-10-10 17:56:55', NULL),
	(630, 'qr_logo_default', '', '2025-10-10 17:56:55', NULL),
	(631, 'inspection_check_days_before', '30', '2025-10-11 17:53:20', NULL),
	(632, 'marker_color_available', '#3388ff', '2025-10-11 17:53:20', NULL),
	(633, 'marker_color_rented', '#ffc107', '2025-10-11 17:53:20', NULL),
	(634, 'marker_color_maintenance', '#dc3545', '2025-10-11 17:53:20', NULL),
	(635, 'marker_color_storage', '#28a745', '2025-10-11 17:53:20', NULL),
	(636, 'marker_color_multidevice', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '2025-10-11 17:53:20', NULL),
	(637, 'marker_color_customer', '#17a2b8', '2025-10-11 17:53:20', NULL),
	(638, 'marker_color_repair', '#fd7e14', '2025-10-11 17:53:20', NULL),
	(639, 'system_logo', '', '2025-10-12 19:22:12', NULL),
	(663, 'nfc_enabled', '1', '2025-10-14 04:16:02', NULL),
	(664, 'device_remember_enabled', '1', '2025-10-14 04:16:02', NULL),
	(665, 'device_remember_duration_days', '90', '2025-10-14 04:16:02', NULL),
	(666, 'require_public_auth', '1', '2025-10-14 04:16:02', NULL),
	(667, 'marker_icon_finished', 'grey', '2025-10-16 05:40:12', NULL),
	(668, 'marker_color_finished', '#6c757d', '2025-10-16 05:40:12', NULL),
	(669, 'show_geofences_on_map', '0', '2025-11-12 13:50:59', NULL),
	(670, 'enable_routing', '1', '2025-11-14 06:47:41', NULL),
	(671, 'enforce_geofence_rules', '0', '2025-11-12 13:50:59', NULL),
	(672, 'enable_tags', '1', '2025-10-18 04:20:00', 'Tag-System aktivieren'),
	(673, 'enable_3d_models', '1', '2025-10-18 04:20:00', '3D-Modell-Upload aktivieren'),
	(674, 'enable_ar_navigation', '1', '2025-10-18 04:20:00', 'AR-Navigation aktivieren'),
	(675, 'enable_calendar_integration', '1', '2025-10-18 04:20:00', 'Kalender-Integration aktivieren'),
	(676, 'enable_map_clustering', '1', '2025-10-18 04:20:00', 'Marker-Clustering auf Karte aktivieren'),
	(677, 'map_cluster_max_zoom', '15', '2025-10-18 04:20:00', 'Maximaler Zoom für Clustering'),
	(678, '3d_model_max_size', '52428800', '2025-10-18 04:20:00', 'Maximale 3D-Modell-Größe in Bytes (50MB)'),
	(679, 'allowed_3d_formats', 'glb,gltf,obj,stl,fbx', '2025-10-18 04:20:00', 'Erlaubte 3D-Formate'),
	(680, 'camera_max_photos', '20', '2025-10-18 04:20:00', 'Maximale Anzahl Fotos pro Upload'),
	(681, 'outlook_client_id', '', '2025-10-18 04:20:00', 'Outlook/Azure App Client ID'),
	(682, 'outlook_client_secret', '', '2025-10-18 04:20:00', 'Outlook/Azure App Client Secret'),
	(683, 'outlook_redirect_uri', '', '2025-10-18 04:20:00', 'Outlook OAuth Redirect URI'),
	(684, 'google_calendar_api_key', '', '2025-10-18 04:20:00', 'Google Calendar API Key'),
	(685, 'google_calendar_client_id', '', '2025-10-18 04:20:00', 'Google OAuth Client ID'),
	(686, 'calendar_auto_maintenance', '0', '2025-10-18 04:32:28', 'Automatische Wartungstermine erstellen'),
	(687, 'calendar_maintenance_days_before', '7', '2025-10-18 04:32:28', 'Tage vor Wartungstermin für Auto-Eintrag'),
	(688, 'enable_outlook_sync', '0', '2025-10-18 04:32:28', 'Outlook-Synchronisation aktivieren'),
	(689, 'enable_google_calendar', '0', '2025-10-18 04:32:28', 'Google Calendar aktivieren'),
	(690, 'enable_photo_editing', '1', '2025-10-18 04:32:28', 'Foto-Bearbeitung aktivieren'),
	(691, 'enable_photo_annotations', '1', '2025-10-18 04:32:28', 'Foto-Annotationen aktivieren'),
	(692, 'enable_map_heatmap', '0', '2025-10-18 04:32:28', 'Heatmap-Ansicht aktivieren'),
	(834, 'live_updates_enabled', '1', '2025-10-19 09:44:44', 'Live-Updates aktivieren'),
	(835, 'live_update_interval', '5000', '2025-10-19 09:44:44', 'Live-Update Interval in Millisekunden'),
	(836, 'max_active_users_display', '50', '2025-10-19 09:44:44', 'Maximale Anzahl aktiver Benutzer in Anzeige'),
	(837, 'marker_clustering_enabled', '1', '2025-10-19 09:44:44', 'Marker Clustering auf Karte aktivieren'),
	(838, 'marker_cluster_radius', '80', '2025-10-19 09:44:44', 'Cluster-Radius in Pixel'),
	(839, 'ar_enabled', '1', '2025-10-19 09:44:44', 'AR-Features aktivieren'),
	(840, 'ar_distance_unit', 'meters', '2025-10-19 09:44:44', 'AR Distanz-Einheit (meters/feet)'),
	(841, 'signature_required_maintenance', '0', '2025-10-19 09:44:44', 'Digitale Signatur für Wartungsberichte erforderlich'),
	(842, 'signature_required_inspection', '0', '2025-10-19 09:44:44', 'Digitale Signatur für Prüfberichte erforderlich'),
	(843, 'max_3d_model_size', '52428800', '2025-10-19 09:44:44', 'Maximale 3D-Modell Größe in Bytes (50MB)'),
	(844, 'calendar_sync_enabled', '1', '2025-10-19 09:44:44', 'Kalender-Synchronisation aktivieren'),
	(845, 'pwa_enabled', '1', '2025-10-19 09:44:44', 'Progressive Web App aktivieren'),
	(955, 'marker_color_messe', '#9c27b0', '2025-11-14 06:47:41', NULL);

-- Exportiere Struktur von Tabelle d044f149.tags
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#007bff',
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `idx_tags_name` (`name`),
  CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportiere Daten aus Tabelle d044f149.tags: ~13 rows (ungefähr)
INSERT INTO `tags` (`id`, `name`, `color`, `icon`, `description`, `created_at`, `created_by`) VALUES
	(1, '#dringend', '#dc3545', 'fas fa-exclamation-circle', 'Dringende Bearbeitung erforderlich', '2025-10-17 18:48:53', NULL),
	(2, '#kunde', '#28a745', 'fas fa-user', 'Kundengerät', '2025-10-17 18:48:53', NULL),
	(3, '#lager', '#ffc107', 'fas fa-warehouse', 'Lagerartikel', '2025-10-17 18:48:53', NULL),
	(4, '#reparatur', '#fd7e14', 'fas fa-tools', 'In Reparatur', '2025-10-17 18:48:53', NULL),
	(5, '#defekt', '#dc3545', 'fas fa-exclamation-triangle', 'Defekt', '2025-10-17 18:48:53', NULL),
	(6, '#test', '#6c757d', 'fas fa-vial', 'Test-Gerät', '2025-10-17 18:48:53', NULL),
	(7, '#garantie', '#17a2b8', 'fas fa-certificate', 'Unter Garantie', '2025-10-17 18:48:53', NULL),
	(8, '#reserviert', '#e83e8c', 'fas fa-bookmark', 'Reserviert', '2025-10-17 18:48:53', NULL),
	(9, '#verkauf', '#20c997', 'fas fa-shopping-cart', 'Zum Verkauf', '2025-10-17 18:48:53', NULL),
	(10, '#archiv', '#6c757d', 'fas fa-archive', 'Archiviert', '2025-10-17 18:48:53', NULL),
	(31, '#fertig', '#2ecc71', NULL, 'Fertig / Abholbereit', '2025-10-18 04:32:27', NULL),
	(32, '#neu', '#1abc9c', NULL, 'Neues Gerät', '2025-10-18 04:32:27', NULL),
	(33, '#wartung', '#9b59b6', NULL, 'Wartung fällig', '2025-10-18 04:32:27', NULL);

-- Exportiere Struktur von Tabelle d044f149.trusted_devices
CREATE TABLE IF NOT EXISTS `trusted_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL COMMENT 'Eindeutiger Token für dieses Gerät',
  `device_fingerprint` varchar(255) NOT NULL COMMENT 'Browser/Geräte Fingerprint',
  `device_name` varchar(255) DEFAULT NULL COMMENT 'Optional: Name des Geräts',
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_access` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL COMMENT 'Token Ablaufdatum (NULL = nie)',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_device_marker` (`marker_id`,`device_fingerprint`),
  KEY `idx_device_token` (`device_token`),
  KEY `idx_marker_id` (`marker_id`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `trusted_devices_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vertrauenswürdige Geräte für Public View ohne erneuten Login';

-- Exportiere Daten aus Tabelle d044f149.trusted_devices: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.unified_scan_history
CREATE TABLE IF NOT EXISTS `unified_scan_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) NOT NULL,
  `scan_type` enum('QR','NFC') NOT NULL COMMENT 'Welche Methode wurde verwendet?',
  `scan_identifier` varchar(100) NOT NULL COMMENT 'QR-Code oder NFC-Chip-ID',
  `gps_latitude` decimal(10,8) DEFAULT NULL,
  `gps_longitude` decimal(11,8) DEFAULT NULL,
  `gps_accuracy` decimal(10,2) DEFAULT NULL COMMENT 'GPS Genauigkeit in Metern',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Wer hat gescannt?',
  `scan_timestamp` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_marker` (`marker_id`),
  KEY `idx_scan_type` (`scan_type`),
  KEY `idx_timestamp` (`scan_timestamp`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `unified_scan_history_ibfk_1` FOREIGN KEY (`marker_id`) REFERENCES `markers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unified_scan_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vereinheitlichte Scan-Historie für QR und NFC (Hybrid System)';

-- Exportiere Daten aus Tabelle d044f149.unified_scan_history: ~0 rows (ungefähr)

-- Exportiere Struktur von Prozedur d044f149.update_overdue_maintenance_status
DELIMITER //
CREATE PROCEDURE `update_overdue_maintenance_status`()
BEGIN
    
    UPDATE markers 
    SET rental_status = 'wartung',
        maintenance_required = 1
    WHERE next_maintenance < CURDATE()
      AND next_maintenance IS NOT NULL
      AND rental_status != 'wartung'
      AND deleted_at IS NULL
      AND is_storage = 0;
    
    
    INSERT INTO activity_log (user_id, username, action, details, marker_id, ip_address)
    SELECT 
        NULL,
        'SYSTEM',
        'auto_maintenance_status',
        CONCAT('Wartungsstatus automatisch auf "Wartung" gesetzt (', 
               DATEDIFF(CURDATE(), next_maintenance), ' Tage überfällig)'),
        id,
        NULL
    FROM markers
    WHERE next_maintenance < CURDATE()
      AND next_maintenance IS NOT NULL
      AND rental_status = 'wartung'
      AND deleted_at IS NULL
      AND is_storage = 0;
END//
DELIMITER ;

-- Exportiere Struktur von Tabelle d044f149.uploaded_files
CREATE TABLE IF NOT EXISTS `uploaded_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marker_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `filepath` varchar(512) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `upload_category` varchar(100) DEFAULT 'marker_images' COMMENT 'marker_images, 3d_models, documents, icons, etc.',
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marker_id` (`marker_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `upload_category` (`upload_category`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.uploaded_files: ~0 rows (ungefähr)
INSERT INTO `uploaded_files` (`id`, `marker_id`, `filename`, `original_filename`, `filepath`, `file_type`, `file_size`, `upload_category`, `uploaded_by`, `uploaded_at`, `is_deleted`, `deleted_at`, `deleted_by`) VALUES
	(1, 25, 'img_690c2e9d3e6316.64386032_25.png', 'default.png', 'uploads/img_690c2e9d3e6316.64386032_25.png', 'image/png', 20221, 'marker_images', 1, '2025-11-06 06:14:05', 0, NULL, NULL);

-- Exportiere Struktur von Tabelle d044f149.usage_statistics
CREATE TABLE IF NOT EXISTS `usage_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `page` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_date` (`user_id`,`created_at`),
  KEY `idx_action` (`action_type`),
  CONSTRAINT `usage_statistics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.usage_statistics: ~131 rows (ungefähr)
INSERT INTO `usage_statistics` (`id`, `user_id`, `action_type`, `page`, `created_at`) VALUES
	(1, 1, 'checklist_complete', 'complete_checklist.php', '2025-10-04 20:56:40'),
	(2, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-04 20:57:49'),
	(3, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-04 21:00:38'),
	(4, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-04 21:23:29'),
	(5, 1, 'reports_view', 'reports.php', '2025-10-04 21:33:56'),
	(6, 1, 'profile_view', 'profile.php', '2025-10-04 23:02:30'),
	(7, 1, 'profile_view', 'profile.php', '2025-10-04 23:04:31'),
	(8, 1, 'profile_view', 'profile.php', '2025-10-04 23:04:32'),
	(9, 1, 'profile_view', 'profile.php', '2025-10-04 23:04:56'),
	(10, 1, 'profile_view', 'profile.php', '2025-10-04 23:05:19'),
	(11, 1, 'profile_view', 'profile.php', '2025-10-04 23:06:16'),
	(12, 1, 'edit_user', 'edit_user.php', '2025-10-04 23:06:21'),
	(13, 1, 'edit_user', 'edit_user.php', '2025-10-04 23:11:40'),
	(14, 1, 'advanced_search', 'advanced_search.php', '2025-10-05 10:20:53'),
	(15, 1, 'advanced_search', 'advanced_search.php', '2025-10-05 10:23:31'),
	(16, 1, 'advanced_search', 'advanced_search.php', '2025-10-05 10:23:33'),
	(17, 1, 'advanced_search', 'advanced_search.php', '2025-10-05 10:43:48'),
	(18, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-05 11:05:05'),
	(19, 1, 'advanced_search', 'advanced_search.php', '2025-10-05 16:22:15'),
	(20, 1, 'advanced_search', 'advanced_search.php', '2025-10-06 12:58:59'),
	(21, 1, 'advanced_search', 'advanced_search.php', '2025-10-07 09:28:29'),
	(22, 1, 'edit_user', 'edit_user.php', '2025-10-07 11:48:05'),
	(23, 1, 'edit_user', 'edit_user.php', '2025-10-07 11:48:26'),
	(24, 1, 'reports_view', 'reports.php', '2025-10-07 11:51:28'),
	(25, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 11:51:40'),
	(26, 1, 'profile_view', 'profile.php', '2025-10-07 11:53:24'),
	(27, 1, 'advanced_search', 'advanced_search.php', '2025-10-07 11:56:49'),
	(28, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 16:28:05'),
	(29, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 16:28:39'),
	(30, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 16:29:07'),
	(31, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 16:29:11'),
	(32, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 16:40:18'),
	(33, 1, 'reports_view', 'reports.php', '2025-10-07 16:40:23'),
	(34, 1, 'reports_view', 'reports.php', '2025-10-07 16:42:49'),
	(35, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:02:21'),
	(36, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:02:47'),
	(37, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:05:06'),
	(38, 1, 'profile_view', 'profile.php', '2025-10-07 21:30:26'),
	(39, 1, 'profile_view', 'profile.php', '2025-10-07 21:33:17'),
	(40, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:43:05'),
	(41, 1, 'profile_view', 'profile.php', '2025-10-07 21:44:04'),
	(42, 1, 'profile_view', 'profile.php', '2025-10-07 21:52:05'),
	(43, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-07 21:55:08'),
	(44, 1, 'reports_view', 'reports.php', '2025-10-07 21:55:17'),
	(45, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:55:19'),
	(46, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:56:15'),
	(47, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-07 21:57:09'),
	(48, 1, 'advanced_search', 'advanced_search.php', '2025-10-07 21:58:11'),
	(49, 1, 'advanced_search', 'advanced_search.php', '2025-10-07 23:48:24'),
	(50, 1, 'advanced_search', 'advanced_search.php', '2025-10-08 08:30:41'),
	(51, 1, 'advanced_search', 'advanced_search.php', '2025-10-08 08:46:24'),
	(52, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-08 09:22:41'),
	(53, 1, 'reports_view', 'reports.php', '2025-10-08 20:25:22'),
	(54, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-08 20:30:50'),
	(55, 1, 'reports_view', 'reports.php', '2025-10-08 20:30:51'),
	(56, 1, 'advanced_search', 'advanced_search.php', '2025-10-08 21:03:23'),
	(57, 1, 'advanced_search', 'advanced_search.php', '2025-10-09 20:32:31'),
	(58, 1, 'advanced_search', 'advanced_search.php', '2025-10-10 20:09:25'),
	(59, 1, 'advanced_search', 'advanced_search.php', '2025-10-11 07:04:32'),
	(60, 1, 'profile_view', 'profile.php', '2025-10-11 07:14:27'),
	(61, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-11 07:14:46'),
	(62, 1, 'advanced_search', 'advanced_search.php', '2025-10-11 20:49:00'),
	(63, 1, 'profile_view', 'profile.php', '2025-10-11 20:49:28'),
	(64, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-11 20:49:39'),
	(65, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-12 18:03:55'),
	(66, 1, 'bulk_operations', 'bulk_operations.php', '2025-10-12 20:17:19'),
	(67, 1, 'advanced_search', 'advanced_search.php', '2025-10-12 20:18:22'),
	(68, 1, 'checklist_complete', 'complete_checklist.php', '2025-10-12 20:19:46'),
	(69, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-12 20:23:08'),
	(70, 1, 'checklists_admin_view', 'checklists_admin.php', '2025-10-12 20:23:19'),
	(71, 1, 'checklist_complete', 'complete_checklist.php', '2025-10-12 20:24:38'),
	(72, 1, 'checklist_complete', 'complete_checklist.php', '2025-10-12 20:24:44'),
	(73, 1, 'advanced_search', 'advanced_search.php', '2025-10-14 12:10:57'),
	(74, 1, 'profile_view', 'profile.php', '2025-10-14 12:11:53'),
	(75, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-14 12:12:34'),
	(76, 1, 'reports_view', 'reports.php', '2025-10-14 12:14:51'),
	(77, 1, 'reports_view', 'reports.php', '2025-10-14 12:15:18'),
	(78, 1, 'export_maintenance', 'export_maintenance.php', '2025-10-14 12:15:22'),
	(79, 1, 'advanced_search', 'advanced_search.php', '2025-10-14 12:16:49'),
	(80, 1, 'advanced_search', 'advanced_search.php', '2025-10-17 05:48:49'),
	(81, 1, 'advanced_search', 'advanced_search.php', '2025-10-17 06:38:51'),
	(82, 1, 'profile_view', 'profile.php', '2025-10-17 14:40:46'),
	(83, 1, 'profile_view', 'profile.php', '2025-10-17 14:46:30'),
	(84, 1, 'advanced_search', 'advanced_search.php', '2025-10-17 19:54:09'),
	(85, 1, 'reports_view', 'reports.php', '2025-10-17 20:17:05'),
	(86, 1, 'advanced_search', 'advanced_search.php', '2025-10-17 20:22:20'),
	(87, 1, 'advanced_search', 'advanced_search.php', '2025-10-17 20:22:39'),
	(88, 1, 'reports_view', 'reports.php', '2025-10-17 20:25:31'),
	(89, 1, 'profile_view', 'profile.php', '2025-10-17 20:25:58'),
	(90, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-17 20:26:02'),
	(91, 1, 'reports_view', 'reports.php', '2025-10-20 06:28:28'),
	(92, 1, 'reports_view', 'reports.php', '2025-10-20 14:05:05'),
	(93, 1, 'reports_view', 'reports.php', '2025-10-21 11:46:00'),
	(94, 1, 'edit_user', 'edit_user.php', '2025-10-22 12:04:18'),
	(95, 1, 'reports_view', 'reports.php', '2025-10-25 13:44:50'),
	(96, 1, 'advanced_search', 'advanced_search.php', '2025-10-26 08:50:30'),
	(97, 1, 'reports_view', 'reports.php', '2025-10-26 08:54:45'),
	(98, 1, 'profile_view', 'profile.php', '2025-10-26 08:55:09'),
	(99, 1, 'setup_2fa', 'setup_2fa.php', '2025-10-26 08:55:15'),
	(100, 1, 'profile_view', 'profile.php', '2025-10-26 20:03:38'),
	(101, 1, 'profile_view', 'profile.php', '2025-10-26 20:06:59'),
	(102, 1, 'edit_user', 'edit_user.php', '2025-10-26 20:07:09'),
	(103, 1, 'edit_user', 'edit_user.php', '2025-10-27 07:03:13'),
	(104, 1, 'profile_view', 'profile.php', '2025-10-27 07:15:08'),
	(105, 1, 'advanced_search', 'advanced_search.php', '2025-10-27 12:03:09'),
	(106, 1, 'reports_view', 'reports.php', '2025-10-27 12:04:41'),
	(107, 1, 'edit_user', 'edit_user.php', '2025-10-27 12:35:36'),
	(108, 1, 'edit_user', 'edit_user.php', '2025-10-27 12:35:53'),
	(109, 1, 'reports_view', 'reports.php', '2025-10-30 06:24:23'),
	(110, 10, 'profile_view', 'profile.php', '2025-11-03 10:58:50'),
	(111, 1, 'edit_user', 'edit_user.php', '2025-11-03 11:58:27'),
	(112, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:08:24'),
	(113, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:08:35'),
	(114, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:11:40'),
	(115, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:11:46'),
	(116, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:11:55'),
	(117, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:12:00'),
	(118, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:12:04'),
	(119, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:12:09'),
	(120, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:12:12'),
	(121, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:12:16'),
	(122, 1, 'edit_user', 'edit_user.php', '2025-11-03 12:12:19'),
	(123, 1, 'edit_user', 'edit_user.php', '2025-11-04 05:58:27'),
	(124, 1, 'edit_user', 'edit_user.php', '2025-11-04 08:05:17'),
	(125, 1, 'edit_user', 'edit_user.php', '2025-11-04 08:05:47'),
	(126, 1, 'edit_user', 'edit_user.php', '2025-11-04 08:06:35'),
	(127, 1, 'edit_user', 'edit_user.php', '2025-11-04 12:03:53'),
	(128, 1, 'edit_user', 'edit_user.php', '2025-11-04 12:04:16'),
	(129, 1, 'edit_user', 'edit_user.php', '2025-11-04 12:33:51'),
	(130, 1, 'profile_view', 'profile.php', '2025-11-12 14:49:03'),
	(131, 1, 'profile_view', 'profile.php', '2025-11-13 12:54:28');

-- Exportiere Struktur von Tabelle d044f149.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `maintenance_notification` tinyint(1) DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) DEFAULT 0 COMMENT 'Benutzer muss Passwort bei nächstem Login ändern',
  `last_password_change` datetime DEFAULT NULL COMMENT 'Zeitpunkt der letzten Passwortänderung',
  `role` enum('admin','user','viewer') DEFAULT 'user',
  `role_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `receive_maintenance_emails` tinyint(1) DEFAULT 0 COMMENT 'Erhält E-Mails bei fälliger Wartung',
  `require_2fa` tinyint(1) DEFAULT 0,
  `has_2fa_enabled` tinyint(1) DEFAULT 0,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `account_locked` tinyint(1) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `failed_login_count` int(11) DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `phone_verified` tinyint(1) DEFAULT 0,
  `preferred_2fa_method` varchar(20) DEFAULT 'app',
  `sms_2fa_enabled` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_must_change_password` (`must_change_password`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_email` (`email`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.users: ~7 rows (ungefähr)
INSERT INTO `users` (`id`, `username`, `email`, `maintenance_notification`, `password`, `must_change_password`, `last_password_change`, `role`, `role_id`, `created_at`, `last_login`, `receive_maintenance_emails`, `require_2fa`, `has_2fa_enabled`, `first_name`, `last_name`, `phone`, `profile_image`, `account_locked`, `locked_until`, `failed_login_count`, `last_failed_login`, `phone_verified`, `preferred_2fa_method`, `sms_2fa_enabled`) VALUES
	(1, 'admin', 'admin@example.com', 0, '$2y$12$kbsJR1eK2YkyjDdyo55D1OT6MTnu7HW0MaswzobSbQGhivr2z7zju', 0, '2025-10-02 12:45:19', 'admin', 1, '2025-10-02 10:45:19', '2025-11-16 05:27:16', 1, 0, 0, 'Frank', 'Schwind', '123456789', NULL, 0, NULL, 0, NULL, 0, 'app', 0),
	(5, 'mrapp', 'test@email.de', 0, '$2y$12$.tqskOgf6Xz2d2/e1Keq0.W1EIQHucDipGQu9bIkDlFPxMfbdAcRK', 0, '2025-10-27 12:50:28', 'admin', 1, '2025-10-27 11:50:28', NULL, 0, 0, 0, 'Michael', 'Rapp', '', NULL, 0, NULL, 0, NULL, 0, 'app', 0),
	(6, 'mkleesattel', 'test2@email.de', 0, '$2y$12$f2C0HBS385XxVz8W9zoA7u0KSnx0F4tcQglZQHmJJe9NSxwOqH3da', 0, '2025-10-27 13:12:21', 'admin', 1, '2025-10-27 12:12:21', NULL, 0, 0, 0, 'Michael', 'Kleesattel', '', NULL, 0, NULL, 0, NULL, 0, 'app', 0),
	(7, 'marapp', 'test3@email.de', 0, '$2y$12$Wl3ISmWd7/wGyioJ9wjaVuzNi0w7D1aAfpA.b1jnHEQciU.0FFP5K', 0, '2025-10-27 14:05:54', 'admin', 1, '2025-10-27 13:05:54', NULL, 0, 0, 0, 'Marian', 'Rapp', '', NULL, 0, NULL, 0, NULL, 0, 'app', 0),
	(8, 'sofcarek', 'test4@email.de', 0, '$2y$12$lCLWs.U4.hmb1W4FC0OPne0eKlleLTk8zthF8nWZEypEu6g1aA91S', 0, '2025-10-27 14:10:09', 'admin', 1, '2025-10-27 13:10:09', NULL, 0, 0, 0, 'Stefan', 'Ofcarek', '', NULL, 0, NULL, 0, NULL, 0, 'app', 0),
	(9, 'rweber', 'test5@email.de', 0, '$2y$12$8UcN2hecNTFD6P/hPccS7OCUu/o5MNSRTXOYKvg3/S5euacBVj9sm', 0, '2025-10-27 14:11:06', 'admin', 1, '2025-10-27 13:11:06', NULL, 0, 0, 0, 'Robin', 'Weber', '', NULL, 0, NULL, 0, NULL, 0, 'app', 0),
	(10, 'testname', 'test45@email.de', 0, '$2y$12$iU2wIdQGTfnegMG2xcQ2xeC.75dspq5qPaySGUzEU9.0nfrqY3Wce', 0, NULL, 'admin', 1, '2025-11-03 09:58:31', '2025-11-04 11:04:30', 0, 0, 0, 'Test', 'Testnamen', '', NULL, 0, NULL, 0, NULL, 0, 'app', 0);

-- Exportiere Struktur von Tabelle d044f149.user_2fa
CREATE TABLE IF NOT EXISTS `user_2fa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `secret` varchar(32) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 0,
  `backup_codes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_2fa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_2fa: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_2fa_phone
CREATE TABLE IF NOT EXISTS `user_2fa_phone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `method` enum('sms','whatsapp') DEFAULT 'sms',
  `verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `user_2fa_phone_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_2fa_phone: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_backup_codes
CREATE TABLE IF NOT EXISTS `user_backup_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` varchar(16) NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_code` (`code`),
  CONSTRAINT `user_backup_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_backup_codes: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_bulk_imports
CREATE TABLE IF NOT EXISTS `user_bulk_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imported_by` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `total_rows` int(11) DEFAULT 0,
  `successful_rows` int(11) DEFAULT 0,
  `failed_rows` int(11) DEFAULT 0,
  `error_log` text DEFAULT NULL,
  `import_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_imported_by` (`imported_by`),
  CONSTRAINT `user_bulk_imports_ibfk_1` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_bulk_imports: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_calendar_settings
CREATE TABLE IF NOT EXISTS `user_calendar_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `google_calendar_enabled` tinyint(1) DEFAULT 0,
  `outlook_enabled` tinyint(1) DEFAULT 0,
  `ical_enabled` tinyint(1) DEFAULT 1,
  `auto_create_events` tinyint(1) DEFAULT 0,
  `calendar_token` varchar(255) DEFAULT NULL,
  `calendar_url` varchar(500) DEFAULT NULL,
  `notification_days_before` int(11) DEFAULT 3,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_calendar_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_calendar_settings: ~1 rows (ungefähr)
INSERT INTO `user_calendar_settings` (`id`, `user_id`, `google_calendar_enabled`, `outlook_enabled`, `ical_enabled`, `auto_create_events`, `calendar_token`, `calendar_url`, `notification_days_before`, `updated_at`) VALUES
	(1, 1, 0, 0, 1, 0, '4b888f757c32fb2791f3e51dba29ae1fdd79e55ee6ee64068c9b9f20bb385a0d', NULL, 3, '2025-10-27 06:29:13');

-- Exportiere Struktur von Tabelle d044f149.user_checklist
CREATE TABLE IF NOT EXISTS `user_checklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `checklist_item` varchar(100) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_item` (`user_id`,`checklist_item`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `user_checklist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_checklist: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_dashboard_widgets
CREATE TABLE IF NOT EXISTS `user_dashboard_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `widget_type` varchar(50) NOT NULL COMMENT 'Typ des Widgets (maintenance_calendar, critical_warnings, etc.)',
  `position` int(11) NOT NULL DEFAULT 0 COMMENT 'Position im Dashboard (für Sortierung)',
  `width` enum('full','half','third','quarter') NOT NULL DEFAULT 'half' COMMENT 'Breite des Widgets',
  `is_expanded` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=erweitert, 0=kompakt',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=sichtbar, 0=ausgeblendet',
  `settings` text DEFAULT NULL COMMENT 'JSON mit individuellen Widget-Einstellungen',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `widget_type` (`widget_type`),
  KEY `idx_user_visible` (`user_id`,`is_visible`),
  KEY `idx_user_position` (`user_id`,`position`),
  CONSTRAINT `fk_dashboard_widgets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Dashboard-Widget-Konfigurationen pro Benutzer';

-- Exportiere Daten aus Tabelle d044f149.user_dashboard_widgets: ~7 rows (ungefähr)
INSERT INTO `user_dashboard_widgets` (`id`, `user_id`, `widget_type`, `position`, `width`, `is_expanded`, `is_visible`, `settings`, `created_at`, `updated_at`) VALUES
	(1, 1, 'quick_stats', 0, 'full', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14'),
	(2, 1, 'maintenance_calendar', 1, 'half', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14'),
	(3, 1, 'critical_warnings', 2, 'half', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14'),
	(4, 1, 'kpi_utilization', 3, 'third', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14'),
	(5, 1, 'geo_heatmap', 4, 'half', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14'),
	(6, 1, 'avg_rental_duration', 5, 'third', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14'),
	(7, 1, 'top_devices', 6, 'third', 1, 1, NULL, '2025-11-02 11:45:14', '2025-11-02 11:45:14');

-- Exportiere Struktur von Tabelle d044f149.user_impersonation_log
CREATE TABLE IF NOT EXISTS `user_impersonation_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL,
  `impersonated_user_id` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_user` (`admin_user_id`),
  KEY `idx_impersonated_user` (`impersonated_user_id`),
  CONSTRAINT `user_impersonation_log_ibfk_1` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `user_impersonation_log_ibfk_2` FOREIGN KEY (`impersonated_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_impersonation_log: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_onboarding
CREATE TABLE IF NOT EXISTS `user_onboarding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `setup_wizard_completed` tinyint(1) DEFAULT 0,
  `tour_completed` tinyint(1) DEFAULT 0,
  `welcome_email_sent` tinyint(1) DEFAULT 0,
  `first_login_at` timestamp NULL DEFAULT NULL,
  `setup_completed_at` timestamp NULL DEFAULT NULL,
  `tour_completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `user_onboarding_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_onboarding: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_preferences
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `user_id` int(11) NOT NULL,
  `dark_mode` tinyint(1) DEFAULT 0,
  `language` varchar(10) DEFAULT 'de',
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_preferences: ~1 rows (ungefähr)
INSERT INTO `user_preferences` (`user_id`, `dark_mode`, `language`, `notifications_enabled`, `preferences`) VALUES
	(1, 0, 'de', 1, NULL);

-- Exportiere Struktur von Tabelle d044f149.user_sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `last_activity` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_active` (`user_id`,`is_active`),
  KEY `idx_session` (`session_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_sessions: ~54 rows (ungefähr)
INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `ip_address`, `user_agent`, `device_info`, `last_activity`, `created_at`, `expires_at`, `is_active`) VALUES
	(1, 1, '91a430da6278ac85efafa6746928c22c', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-04 08:05:11', '2025-11-04 08:05:11', '2025-11-05 08:05:11', 0),
	(2, 1, '6cb3dbade3af234d47532fa5cfc8f206', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Windows PC', '2025-11-04 12:03:25', '2025-11-04 12:03:25', '2025-11-05 12:03:25', 0),
	(3, 10, '60176d4a03d914335f872950fa4ff0b6', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Windows PC', '2025-11-04 12:04:30', '2025-11-04 12:04:30', '2025-11-05 12:04:30', 1),
	(4, 1, '1e92f2e685e085e59b60fd8ca3798f98', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Windows PC', '2025-11-04 12:08:21', '2025-11-04 12:08:21', '2025-11-05 12:08:21', 0),
	(5, 1, '2d761669359067fb1532c8c06a824767', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', 'Windows PC', '2025-11-04 12:33:12', '2025-11-04 12:33:12', '2025-11-05 12:33:12', 0),
	(6, 1, 'fd60916c944b0aa84b388fac540d0e84', '80.187.105.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.4', 'iOS Mobile', '2025-11-04 16:40:18', '2025-11-04 16:40:18', '2025-11-05 16:40:18', 0),
	(7, 1, '6fa18fe4c3b8156a1550051402c7443a', '85.13.129.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-04 22:19:30', '2025-11-04 22:19:30', '2025-11-05 22:19:30', 0),
	(8, 1, 'ccadfa29c052470cec6f6a0ab097a208', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-05 12:46:26', '2025-11-05 12:46:26', '2025-11-06 12:46:26', 0),
	(9, 1, '38f05e82221e4a4149224a243f86b212', '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-05 21:14:38', '2025-11-05 21:14:38', '2025-11-06 21:14:38', 0),
	(10, 1, '6c3f4000a9f48ae217e63c4bbb540fb7', '109.43.115.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-05 21:53:40', '2025-11-05 21:53:40', '2025-11-06 21:53:40', 0),
	(11, 1, 'eef398eb821368d85c8a2740c191e43a', '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-06 06:00:58', '2025-11-06 06:00:58', '2025-11-07 06:00:58', 0),
	(12, 1, '25cc2648dd6f3a94bbabb7c48267c918', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-06 07:44:13', '2025-11-06 07:44:13', '2025-11-07 07:44:13', 0),
	(13, 1, '2386d69fa94eac6249886b6e3e571acf', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-06 11:20:36', '2025-11-06 11:20:36', '2025-11-07 11:20:36', 0),
	(14, 1, 'c4dfd35e09fcf6cdada28d7e63973776', '80.187.105.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-06 12:12:01', '2025-11-06 12:12:01', '2025-11-07 12:12:01', 0),
	(15, 1, 'bfcc5e47409ed04bda58ecf64bf47bac', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-06 15:45:19', '2025-11-06 15:45:19', '2025-11-07 15:45:19', 0),
	(16, 1, 'bf076d16bc4e67374f6c11c52afcf024', '109.43.114.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-07 05:56:18', '2025-11-07 05:56:18', '2025-11-08 05:56:18', 0),
	(17, 1, '41754ef25b799e097ef08b10536b95cd', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-07 07:47:04', '2025-11-07 07:47:04', '2025-11-08 07:47:04', 0),
	(18, 1, '517e4497fc014e14cc39f1bc6bff04a6', '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 12:07:50', '2025-11-10 12:07:50', '2025-11-11 12:07:50', 0),
	(19, 1, '0164964b0a7b41f5ba70d085bf61e75b', '80.187.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 15:20:09', '2025-11-10 15:20:09', '2025-11-11 15:20:09', 0),
	(20, 1, 'd14ff503dec2612c8ff4197ab86195c2', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 15:42:05', '2025-11-10 15:42:05', '2025-11-11 15:42:05', 0),
	(21, 1, '47acb2816e2430611d10a37cfb0422d9', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-10 15:43:44', '2025-11-10 15:43:44', '2025-11-11 15:43:44', 0),
	(22, 1, 'b645999a4699c10d529646ae99fc11ab', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 16:18:35', '2025-11-10 16:18:35', '2025-11-11 16:18:35', 0),
	(23, 1, 'cf0153538303a7a82faced9f4f39b680', '80.187.101.154', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-10 16:44:01', '2025-11-10 16:44:01', '2025-11-11 16:44:01', 0),
	(24, 1, '750a6d7117a494fe19856a91286f56ce', '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 19:53:33', '2025-11-10 19:53:33', '2025-11-11 19:53:33', 0),
	(25, 1, '5f695c84c68c1c47a1b4cec105fd627b', '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 20:47:30', '2025-11-10 20:47:30', '2025-11-11 20:47:30', 0),
	(26, 1, '0778bac8b696188eacc45f7b9e152885', '109.43.114.35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-10 21:51:26', '2025-11-10 21:51:26', '2025-11-11 21:51:26', 0),
	(27, 1, '8dd6e46885f949c89aae3fd92b526337', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-11 06:09:28', '2025-11-11 06:09:28', '2025-11-12 06:09:28', 0),
	(28, 1, 'f8aac2e39349d0f4f09547fe3bead26c', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-11 09:50:41', '2025-11-11 09:50:41', '2025-11-12 09:50:41', 0),
	(29, 1, '69b304c6cdea8b82d7b8c913b1375c58', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-11 09:51:10', '2025-11-11 09:51:10', '2025-11-12 09:51:10', 0),
	(30, 1, 'cfb22a5da8e89aaf7dc1a7e57a46e65c', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-11 09:52:19', '2025-11-11 09:52:19', '2025-11-12 09:52:19', 0),
	(31, 1, 'ce6ee1820a00201226d1c4fbc96c3fbb', '80.187.101.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-11 12:43:09', '2025-11-11 12:43:09', '2025-11-12 12:43:09', 0),
	(32, 1, '95f3256f12e93546c4eaf268b292811e', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-11 16:08:44', '2025-11-11 16:08:44', '2025-11-12 16:08:44', 0),
	(33, 1, '5dcdba370d148c94678f26546f492b46', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-11 19:51:16', '2025-11-11 19:51:16', '2025-11-12 19:51:16', 0),
	(34, 1, '528b820594b569966c2b6d3e003da181', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-12 06:15:34', '2025-11-12 06:15:34', '2025-11-13 06:15:34', 0),
	(35, 1, 'dc4dab4480d96ab348c69f452f4fbf8e', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-12 07:39:16', '2025-11-12 07:39:16', '2025-11-13 07:39:16', 0),
	(36, 1, '44c6df1827c36f34c5aa7cd7af1a9e49', '80.187.101.214', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-12 12:14:25', '2025-11-12 12:14:25', '2025-11-13 12:14:25', 0),
	(37, 1, 'ee590e26161bc3dc35f4bc3eb7dcb817', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-12 14:48:58', '2025-11-12 14:48:58', '2025-11-13 14:48:58', 0),
	(38, 1, '057d645432e724dc2ce6d59bfc2cdf89', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-12 14:50:04', '2025-11-12 14:50:04', '2025-11-13 14:50:04', 0),
	(39, 1, 'a94efe6a2d5afb87135002cd946d4a93', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-12 14:54:29', '2025-11-12 14:54:29', '2025-11-13 14:54:29', 0),
	(40, 1, 'ba543248932f3452d753fa35df327efd', '109.43.114.174', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-12 22:01:59', '2025-11-12 22:01:59', '2025-11-13 22:01:59', 0),
	(41, 1, 'd3e31c921276838011566e5b8103d5d2', '109.43.114.174', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-12 22:03:16', '2025-11-12 22:03:16', '2025-11-13 22:03:16', 0),
	(42, 1, '49112c974f502b981977708df4d8c107', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-13 07:53:29', '2025-11-13 07:53:29', '2025-11-14 07:53:29', 0),
	(43, 1, 'e66c9c717a3ca18054c9a0bd0e5b2fa5', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-13 11:46:05', '2025-11-13 11:46:05', '2025-11-14 11:46:05', 0),
	(44, 1, '5e08a01bbe6e5e3d229af42831eb55f9', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-13 13:52:57', '2025-11-13 13:52:57', '2025-11-14 13:52:57', 0),
	(45, 1, '9bd947ffcbccebc6dc7c54539b209640', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-13 13:59:43', '2025-11-13 13:59:43', '2025-11-14 13:59:43', 0),
	(46, 1, '831882e7670aea6e024e5c486b7d5cf7', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-13 14:00:25', '2025-11-13 14:00:25', '2025-11-14 14:00:25', 0),
	(47, 1, '7a4223536b26ca6b4518a356a7988069', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-14 07:45:59', '2025-11-14 07:45:59', '2025-11-15 07:45:59', 0),
	(48, 1, '746b666174c25e4226aabf1016f4097f', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-14 08:27:24', '2025-11-14 08:27:24', '2025-11-15 08:27:24', 0),
	(49, 1, 'be575eee5425b68dbcade46b81cbeb53', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-14 09:13:48', '2025-11-14 09:13:48', '2025-11-15 09:13:48', 0),
	(50, 1, '1c3450d23488f155d85db7863f9ff7ef', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-14 10:13:00', '2025-11-14 10:13:00', '2025-11-15 10:13:00', 0),
	(51, 1, '520dc285f67fa2754d132e7afb0cea98', '80.151.166.21', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-14 13:43:20', '2025-11-14 13:43:20', '2025-11-15 13:43:20', 0),
	(52, 1, '47e3c35aeb012f4cc372a9c9d5167901', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-14 13:44:51', '2025-11-14 13:44:51', '2025-11-15 13:44:51', 0),
	(53, 1, '8fce8c94bdd23df2ff831c23689f359c', '80.187.101.214', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0 Mobile/15E148 Safari/604.1 OPT/6.1.7', 'iOS Mobile', '2025-11-15 16:18:45', '2025-11-15 16:18:45', '2025-11-16 16:18:45', 1),
	(54, 1, '8d147b78f22cf85d12bd1cf42c361208', '109.43.114.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'Windows PC', '2025-11-16 06:27:16', '2025-11-16 06:27:16', '2025-11-17 06:27:16', 1);

-- Exportiere Struktur von Tabelle d044f149.user_settings
CREATE TABLE IF NOT EXISTS `user_settings` (
  `user_id` int(11) NOT NULL,
  `dark_mode` tinyint(1) DEFAULT 0,
  `items_per_page` int(11) DEFAULT 25,
  `default_map_view` varchar(20) DEFAULT 'standard',
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_settings: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_signatures
CREATE TABLE IF NOT EXISTS `user_signatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `signature_data` longtext NOT NULL COMMENT 'Base64 encoded signature image',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_signature` (`user_id`),
  CONSTRAINT `fk_user_signature_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Digitale Signaturen der Benutzer';

-- Exportiere Daten aus Tabelle d044f149.user_signatures: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_trusted_devices
CREATE TABLE IF NOT EXISTS `user_trusted_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_fingerprint` varchar(255) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `trusted_until` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_fingerprint` (`device_fingerprint`),
  KEY `idx_trusted_until` (`trusted_until`),
  CONSTRAINT `user_trusted_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_trusted_devices: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle d044f149.user_widget_preferences
CREATE TABLE IF NOT EXISTS `user_widget_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `widget_id` varchar(50) NOT NULL,
  `widget_position` int(11) DEFAULT 0,
  `widget_size` varchar(20) DEFAULT 'medium',
  `is_visible` tinyint(1) DEFAULT 1,
  `widget_settings` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_widget` (`user_id`,`widget_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_widget_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle d044f149.user_widget_preferences: ~0 rows (ungefähr)

-- Exportiere Struktur von View d044f149.v_draft_maintenances
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_draft_maintenances` (
	`id` INT(11) NOT NULL,
	`marker_id` INT(11) NOT NULL,
	`marker_name` VARCHAR(255) NULL COLLATE 'utf8mb4_unicode_ci',
	`qr_code` VARCHAR(100) NULL COLLATE 'utf8mb4_unicode_ci',
	`checklist_id` INT(11) NULL COMMENT 'Verwendete Checkliste',
	`checklist_name` VARCHAR(255) NULL COLLATE 'utf8mb4_unicode_ci',
	`performed_by` INT(11) NULL,
	`performed_by_name` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
	`created_at` TIMESTAMP NOT NULL,
	`updated_at` DATETIME NULL,
	`hours_since_update` BIGINT(21) NULL
) ENGINE=MyISAM;

-- Exportiere Struktur von View d044f149.v_due_inspections
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_due_inspections` (
	`id` INT(11) NOT NULL,
	`marker_id` INT(11) NOT NULL,
	`marker_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`qr_code` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`inspection_type` ENUM('TÜV','UVV','DGUV','Sicherheitsprüfung','Sonstiges') NOT NULL COLLATE 'utf8mb4_general_ci',
	`next_inspection` DATE NULL,
	`last_inspection` DATE NULL,
	`responsible_person` VARCHAR(100) NULL COMMENT 'Verantwortliche Person' COLLATE 'utf8mb4_general_ci',
	`status` ENUM('aktuell','fällig','überfällig') NULL COLLATE 'utf8mb4_general_ci',
	`days_until_due` INT(8) NULL,
	`inspection_authority` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`certificate_number` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`priority_level` INT(1) NULL
) ENGINE=MyISAM;

-- Exportiere Struktur von View d044f149.v_expiring_documents
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_expiring_documents` (
	`id` INT(11) NOT NULL,
	`marker_id` INT(11) NOT NULL,
	`marker_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`qr_code` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`document_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`expiry_date` DATE NULL COMMENT 'Ablaufdatum des Dokuments',
	`document_status` ENUM('aktuell','läuft_ab','abgelaufen') NULL COLLATE 'utf8mb4_general_ci',
	`days_until_expiry` INT(8) NULL,
	`notification_days_before` INT(11) NULL COMMENT 'Tage vorher benachrichtigen',
	`last_notification_sent` DATE NULL COMMENT 'Letzte Benachrichtigung',
	`priority_level` INT(1) NULL
) ENGINE=MyISAM;

-- Exportiere Struktur von View d044f149.v_marker_scan_stats
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_marker_scan_stats` (
	`marker_id` INT(11) NOT NULL,
	`marker_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`qr_code` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nfc_chip_id` VARCHAR(100) NULL COMMENT 'NFC-Chip-ID (falls NFC aktiviert)' COLLATE 'utf8mb4_unicode_ci',
	`gps_latitude` DECIMAL(10,8) NULL COMMENT 'GPS Breitengrad',
	`gps_longitude` DECIMAL(11,8) NULL COMMENT 'GPS Längengrad',
	`gps_captured_at` DATETIME NULL COMMENT 'Wann wurde GPS erfasst?',
	`gps_captured_by` VARCHAR(20) NULL COMMENT 'QR oder NFC - welche Methode?' COLLATE 'utf8mb4_unicode_ci',
	`total_scans` BIGINT(21) NOT NULL,
	`qr_scans` BIGINT(21) NOT NULL,
	`nfc_scans` BIGINT(21) NOT NULL,
	`last_scan` TIMESTAMP NULL,
	`first_scan` TIMESTAMP NULL
) ENGINE=MyISAM;

-- Exportiere Struktur von View d044f149.v_messe_statistics
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_messe_statistics` (
	`messe_id` INT(11) NOT NULL,
	`messe_name` VARCHAR(255) NOT NULL COMMENT 'Messe-Name' COLLATE 'utf8mb4_general_ci',
	`devices_scanned` BIGINT(21) NOT NULL,
	`total_scans` DECIMAL(32,0) NULL,
	`total_visitors` DECIMAL(32,0) NULL,
	`total_leads` BIGINT(21) NOT NULL,
	`most_scanned_marker` INT(11) NULL
) ENGINE=MyISAM;

-- Exportiere Struktur von View d044f149.v_nfc_scan_stats
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_nfc_scan_stats` (
	`marker_id` INT(11) NOT NULL,
	`marker_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`nfc_chip_id` VARCHAR(100) NULL COMMENT 'NFC-Chip-ID (falls NFC aktiviert)' COLLATE 'utf8mb4_unicode_ci',
	`total_nfc_scans` BIGINT(21) NOT NULL,
	`last_nfc_scan` TIMESTAMP NULL,
	`first_nfc_scan` TIMESTAMP NULL
) ENGINE=MyISAM;

-- Exportiere Struktur von View d044f149.v_qr_codes_with_markers
-- Erstelle temporäre Tabelle, um View-Abhängigkeiten zuvorzukommen
CREATE TABLE `v_qr_codes_with_markers` (
	`id` INT(11) NOT NULL,
	`qr_code` VARCHAR(100) NOT NULL COMMENT 'QR-Code Nummer (z.B. QR-0001)' COLLATE 'utf8mb4_general_ci',
	`batch_id` VARCHAR(100) NULL COLLATE 'utf8mb4_general_ci',
	`is_assigned` TINYINT(1) NULL COMMENT 'Ist der QR-Code bereits einem Marker zugewiesen?',
	`is_activated` TINYINT(1) NULL COMMENT 'Wurde der QR-Code vor Ort aktiviert?',
	`marker_id` INT(11) NULL COMMENT 'Zugewiesener Marker (wenn assigned)',
	`created_at` TIMESTAMP NOT NULL,
	`assigned_at` TIMESTAMP NULL COMMENT 'Wann wurde er zugewiesen?',
	`print_batch` VARCHAR(50) NULL COMMENT 'Druck-Batch zur Identifikation' COLLATE 'utf8mb4_general_ci',
	`marker_name` VARCHAR(255) NULL COLLATE 'utf8mb4_unicode_ci',
	`marker_category` VARCHAR(100) NULL COLLATE 'utf8mb4_unicode_ci',
	`marker_deleted_at` TIMESTAMP NULL,
	`status_text` VARCHAR(28) NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Exportiere Struktur von Trigger d044f149.marker_deleted_permanently
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER marker_deleted_permanently
BEFORE DELETE ON markers
FOR EACH ROW
BEGIN
    
    UPDATE qr_code_pool 
    SET is_assigned = 0,
        marker_id = NULL,
        assigned_at = NULL
    WHERE qr_code = OLD.qr_code;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.marker_deleted_permanently_nfc
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `marker_deleted_permanently_nfc` 
BEFORE DELETE ON `markers` 
FOR EACH ROW 
BEGIN
    -- NFC-Chip freigeben
    IF OLD.nfc_chip_id IS NOT NULL THEN
        UPDATE nfc_chip_pool 
        SET is_assigned = 0,
            assigned_to_marker_id = NULL,
            assigned_at = NULL
        WHERE nfc_chip_id = OLD.nfc_chip_id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.marker_deleted_release_nfc
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `marker_deleted_release_nfc` 
AFTER UPDATE ON `markers` 
FOR EACH ROW 
BEGIN
    -- Wenn Marker gelöscht wird (soft delete)
    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
        -- NFC-Chip freigeben (falls vorhanden)
        IF NEW.nfc_chip_id IS NOT NULL THEN
            UPDATE nfc_chip_pool 
            SET is_assigned = 0,
                assigned_to_marker_id = NULL,
                assigned_at = NULL
            WHERE nfc_chip_id = OLD.nfc_chip_id;
        END IF;
    END IF;
    
    -- Wenn Marker wiederhergestellt wird
    IF NEW.deleted_at IS NULL AND OLD.deleted_at IS NOT NULL THEN
        -- NFC-Chip wieder zuweisen (falls vorhanden)
        IF NEW.nfc_chip_id IS NOT NULL THEN
            UPDATE nfc_chip_pool 
            SET is_assigned = 1,
                assigned_to_marker_id = NEW.id,
                assigned_at = NOW()
            WHERE nfc_chip_id = NEW.nfc_chip_id;
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.marker_deleted_release_qr
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER marker_deleted_release_qr
AFTER UPDATE ON markers
FOR EACH ROW
BEGIN
    
    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL THEN
        
        UPDATE qr_code_pool 
        SET is_assigned = 0,
            marker_id = NULL,
            assigned_at = NULL
        WHERE qr_code = OLD.qr_code;
        
        
        INSERT INTO activity_log (
            user_id, 
            username, 
            action, 
            details,
            marker_id,
            ip_address,
            user_agent,
            created_at
        ) VALUES (
            NEW.deleted_by,
            (SELECT username FROM users WHERE id = NEW.deleted_by),
            'qr_code_released',
            CONCAT('QR-Code für Marker ID ', OLD.id, ' freigegeben'),
            OLD.id,
            NULL,
            NULL,
            NOW()
        );
    END IF;
    
    
    IF NEW.deleted_at IS NULL AND OLD.deleted_at IS NOT NULL THEN
        
        UPDATE qr_code_pool 
        SET is_assigned = 1,
            marker_id = NEW.id,
            assigned_at = NOW()
        WHERE qr_code = NEW.qr_code;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.qr_deleted_cascade_marker
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER qr_deleted_cascade_marker
BEFORE DELETE ON qr_code_pool
FOR EACH ROW
BEGIN
    
    IF OLD.is_assigned = 1 AND OLD.marker_id IS NOT NULL THEN
        
        UPDATE markers 
        SET deleted_at = NOW(),
            deleted_by = (SELECT id FROM users WHERE username = 'system' LIMIT 1)
        WHERE id = OLD.marker_id 
          AND deleted_at IS NULL;
        
        
        INSERT INTO activity_log (
            user_id,
            username,
            action,
            details,
            marker_id
        ) VALUES (
            NULL,
            'system',
            'marker_deleted_by_qr_deletion',
            OLD.marker_id
        );
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.trg_marker_update_history
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_marker_update_history` 
AFTER UPDATE ON `markers`
FOR EACH ROW
BEGIN
    DECLARE change_json TEXT;
    DECLARE current_user_id INT;
    DECLARE current_username VARCHAR(100);
    
    
    SET current_user_id = @current_user_id;
    SET current_username = @current_username;
    
    
    SET change_json = JSON_OBJECT(
        'name', JSON_OBJECT('old', OLD.name, 'new', NEW.name),
        'category', JSON_OBJECT('old', OLD.category, 'new', NEW.category),
        'serial_number', JSON_OBJECT('old', OLD.serial_number, 'new', NEW.serial_number),
        'rental_status', JSON_OBJECT('old', OLD.rental_status, 'new', NEW.rental_status),
        'operating_hours', JSON_OBJECT('old', OLD.operating_hours, 'new', NEW.operating_hours),
        'fuel_level', JSON_OBJECT('old', OLD.fuel_level, 'new', NEW.fuel_level),
        'latitude', JSON_OBJECT('old', OLD.latitude, 'new', NEW.latitude),
        'longitude', JSON_OBJECT('old', OLD.longitude, 'new', NEW.longitude),
        'is_storage', JSON_OBJECT('old', OLD.is_storage, 'new', NEW.is_storage),
        'is_customer_device', JSON_OBJECT('old', OLD.is_customer_device, 'new', NEW.is_customer_device),
        'customer_name', JSON_OBJECT('old', OLD.customer_name, 'new', NEW.customer_name),
        'order_number', JSON_OBJECT('old', OLD.order_number, 'new', NEW.order_number),
        'is_finished', JSON_OBJECT('old', OLD.is_finished, 'new', NEW.is_finished)
    );
    
    
    IF OLD.name != NEW.name OR OLD.category != NEW.category OR OLD.serial_number != NEW.serial_number OR
       OLD.rental_status != NEW.rental_status OR OLD.operating_hours != NEW.operating_hours OR
       OLD.fuel_level != NEW.fuel_level OR OLD.latitude != NEW.latitude OR OLD.longitude != NEW.longitude OR
       OLD.is_storage != NEW.is_storage OR OLD.is_customer_device != NEW.is_customer_device OR
       OLD.customer_name != NEW.customer_name OR OLD.order_number != NEW.order_number OR
       OLD.is_finished != NEW.is_finished THEN
        
        INSERT INTO marker_history (
            marker_id, 
            user_id, 
            username, 
            action, 
            change_details,
            ip_address,
            user_agent
        ) VALUES (
            NEW.id,
            current_user_id,
            current_username,
            'updated',
            change_json,
            @current_ip,
            @current_user_agent
        );
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.update_document_status_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `update_document_status_before_insert`
BEFORE INSERT ON `marker_documents`
FOR EACH ROW
BEGIN
    IF NEW.expiry_date IS NOT NULL THEN
        IF NEW.expiry_date < CURDATE() THEN
            SET NEW.document_status = 'abgelaufen';
        ELSEIF DATEDIFF(NEW.expiry_date, CURDATE()) <= NEW.notification_days_before THEN
            SET NEW.document_status = 'läuft_ab';
        ELSE
            SET NEW.document_status = 'aktuell';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.update_document_status_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `update_document_status_before_update`
BEFORE UPDATE ON `marker_documents`
FOR EACH ROW
BEGIN
    IF NEW.expiry_date IS NOT NULL THEN
        IF NEW.expiry_date < CURDATE() THEN
            SET NEW.document_status = 'abgelaufen';
        ELSEIF DATEDIFF(NEW.expiry_date, CURDATE()) <= NEW.notification_days_before THEN
            SET NEW.document_status = 'läuft_ab';
        ELSE
            SET NEW.document_status = 'aktuell';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.update_inspection_status_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER update_inspection_status_before_insert
BEFORE INSERT ON inspection_schedules
FOR EACH ROW
BEGIN
    IF NEW.next_inspection IS NOT NULL THEN
        IF NEW.next_inspection < CURDATE() THEN
            SET NEW.status = 'überfällig';
        ELSEIF DATEDIFF(NEW.next_inspection, CURDATE()) <= NEW.notification_days_before THEN
            SET NEW.status = 'fällig';
        ELSE
            SET NEW.status = 'aktuell';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger d044f149.update_inspection_status_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER update_inspection_status_before_update
BEFORE UPDATE ON inspection_schedules
FOR EACH ROW
BEGIN
    IF NEW.next_inspection IS NOT NULL THEN
        IF NEW.next_inspection < CURDATE() THEN
            SET NEW.status = 'überfällig';
        ELSEIF DATEDIFF(NEW.next_inspection, CURDATE()) <= NEW.notification_days_before THEN
            SET NEW.status = 'fällig';
        ELSE
            SET NEW.status = 'aktuell';
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von View d044f149.v_draft_maintenances
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_draft_maintenances`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_draft_maintenances` AS select `mh`.`id` AS `id`,`mh`.`marker_id` AS `marker_id`,`m`.`name` AS `marker_name`,`m`.`qr_code` AS `qr_code`,`mh`.`checklist_id` AS `checklist_id`,`mc`.`name` AS `checklist_name`,`mh`.`performed_by` AS `performed_by`,`u`.`username` AS `performed_by_name`,`mh`.`created_at` AS `created_at`,`mh`.`updated_at` AS `updated_at`,timestampdiff(HOUR,`mh`.`updated_at`,current_timestamp()) AS `hours_since_update` from (((`maintenance_history` `mh` left join `markers` `m` on(`mh`.`marker_id` = `m`.`id`)) left join `maintenance_checklists` `mc` on(`mh`.`checklist_id` = `mc`.`id`)) left join `users` `u` on(`mh`.`performed_by` = `u`.`id`)) where `mh`.`status` = 'draft' and `m`.`deleted_at` is null order by `mh`.`updated_at` desc;

-- Exportiere Struktur von View d044f149.v_due_inspections
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_due_inspections`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_due_inspections` AS select `i`.`id` AS `id`,`i`.`marker_id` AS `marker_id`,`m`.`name` AS `marker_name`,`m`.`qr_code` AS `qr_code`,`i`.`inspection_type` AS `inspection_type`,`i`.`next_inspection` AS `next_inspection`,`i`.`last_inspection` AS `last_inspection`,`i`.`responsible_person` AS `responsible_person`,`i`.`status` AS `status`,to_days(`i`.`next_inspection`) - to_days(curdate()) AS `days_until_due`,`i`.`inspection_authority` AS `inspection_authority`,`i`.`certificate_number` AS `certificate_number`,case when `i`.`next_inspection` < curdate() then 3 when to_days(`i`.`next_inspection`) - to_days(curdate()) <= 7 then 2 when to_days(`i`.`next_inspection`) - to_days(curdate()) <= `i`.`notification_days_before` then 1 else 0 end AS `priority_level` from (`inspection_schedules` `i` join `markers` `m` on(`i`.`marker_id` = `m`.`id`)) where `m`.`deleted_at` is null and `i`.`next_inspection` is not null order by case when `i`.`next_inspection` < curdate() then 3 when to_days(`i`.`next_inspection`) - to_days(curdate()) <= 7 then 2 when to_days(`i`.`next_inspection`) - to_days(curdate()) <= `i`.`notification_days_before` then 1 else 0 end desc,`i`.`next_inspection`;

-- Exportiere Struktur von View d044f149.v_expiring_documents
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_expiring_documents`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_expiring_documents` AS select `d`.`id` AS `id`,`d`.`marker_id` AS `marker_id`,`m`.`name` AS `marker_name`,`m`.`qr_code` AS `qr_code`,`d`.`document_name` AS `document_name`,`d`.`expiry_date` AS `expiry_date`,`d`.`document_status` AS `document_status`,to_days(`d`.`expiry_date`) - to_days(curdate()) AS `days_until_expiry`,`d`.`notification_days_before` AS `notification_days_before`,`d`.`last_notification_sent` AS `last_notification_sent`,case when `d`.`expiry_date` < curdate() then 3 when to_days(`d`.`expiry_date`) - to_days(curdate()) <= 7 then 2 when to_days(`d`.`expiry_date`) - to_days(curdate()) <= `d`.`notification_days_before` then 1 else 0 end AS `priority_level` from (`marker_documents` `d` join `markers` `m` on(`d`.`marker_id` = `m`.`id`)) where `m`.`deleted_at` is null and `d`.`expiry_date` is not null order by case when `d`.`expiry_date` < curdate() then 3 when to_days(`d`.`expiry_date`) - to_days(curdate()) <= 7 then 2 when to_days(`d`.`expiry_date`) - to_days(curdate()) <= `d`.`notification_days_before` then 1 else 0 end desc,`d`.`expiry_date`;

-- Exportiere Struktur von View d044f149.v_marker_scan_stats
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_marker_scan_stats`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_marker_scan_stats` AS select `m`.`id` AS `marker_id`,`m`.`name` AS `marker_name`,`m`.`qr_code` AS `qr_code`,`m`.`nfc_chip_id` AS `nfc_chip_id`,`m`.`gps_latitude` AS `gps_latitude`,`m`.`gps_longitude` AS `gps_longitude`,`m`.`gps_captured_at` AS `gps_captured_at`,`m`.`gps_captured_by` AS `gps_captured_by`,count(distinct `sh`.`id`) AS `total_scans`,count(distinct case when `sh`.`scan_type` = 'QR' then `sh`.`id` end) AS `qr_scans`,count(distinct case when `sh`.`scan_type` = 'NFC' then `sh`.`id` end) AS `nfc_scans`,max(`sh`.`scan_timestamp`) AS `last_scan`,min(`sh`.`scan_timestamp`) AS `first_scan` from (`markers` `m` left join `unified_scan_history` `sh` on(`m`.`id` = `sh`.`marker_id`)) where `m`.`deleted_at` is null group by `m`.`id`;

-- Exportiere Struktur von View d044f149.v_messe_statistics
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_messe_statistics`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_messe_statistics` AS select `m`.`id` AS `messe_id`,`m`.`name` AS `messe_name`,count(distinct `s`.`marker_id`) AS `devices_scanned`,sum(`s`.`scan_count`) AS `total_scans`,sum(`s`.`unique_visitors`) AS `total_visitors`,count(distinct `l`.`id`) AS `total_leads`,(select `messe_scan_stats`.`marker_id` from `messe_scan_stats` where `messe_scan_stats`.`messe_id` = `m`.`id` order by `messe_scan_stats`.`scan_count` desc limit 1) AS `most_scanned_marker` from ((`messe_config` `m` left join `messe_scan_stats` `s` on(`m`.`id` = `s`.`messe_id`)) left join `messe_leads` `l` on(`m`.`id` = `l`.`messe_id`)) group by `m`.`id`;

-- Exportiere Struktur von View d044f149.v_nfc_scan_stats
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_nfc_scan_stats`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_nfc_scan_stats` AS select `m`.`id` AS `marker_id`,`m`.`name` AS `marker_name`,`m`.`nfc_chip_id` AS `nfc_chip_id`,count(distinct `nsh`.`id`) AS `total_nfc_scans`,max(`nsh`.`scan_timestamp`) AS `last_nfc_scan`,min(`nsh`.`scan_timestamp`) AS `first_nfc_scan` from (`markers` `m` left join `nfc_scan_history` `nsh` on(`m`.`id` = `nsh`.`marker_id`)) where `m`.`deleted_at` is null and `m`.`nfc_enabled` = 1 group by `m`.`id`;

-- Exportiere Struktur von View d044f149.v_qr_codes_with_markers
-- Entferne temporäre Tabelle und erstelle die eigentliche View
DROP TABLE IF EXISTS `v_qr_codes_with_markers`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_qr_codes_with_markers` AS select `q`.`id` AS `id`,`q`.`qr_code` AS `qr_code`,`q`.`batch_id` AS `batch_id`,`q`.`is_assigned` AS `is_assigned`,`q`.`is_activated` AS `is_activated`,`q`.`marker_id` AS `marker_id`,`q`.`created_at` AS `created_at`,`q`.`assigned_at` AS `assigned_at`,`q`.`print_batch` AS `print_batch`,`m`.`name` AS `marker_name`,`m`.`category` AS `marker_category`,`m`.`deleted_at` AS `marker_deleted_at`,case when `q`.`is_assigned` = 0 then 'Verfügbar' when `q`.`is_assigned` = 1 and `m`.`deleted_at` is not null then 'Marker gelöscht' when `q`.`is_assigned` = 1 and `q`.`is_activated` = 0 then 'Zugewiesen (nicht aktiviert)' when `q`.`is_assigned` = 1 and `q`.`is_activated` = 1 then 'Aktiv' else 'Unbekannt' end AS `status_text` from (`qr_code_pool` `q` left join `markers` `m` on(`q`.`marker_id` = `m`.`id`));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
