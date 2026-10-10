-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 10, 2026 at 05:53 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legal_advisor`
--

-- --------------------------------------------------------

--
-- Table structure for table `advocate`
--

DROP TABLE IF EXISTS `advocate`;
CREATE TABLE IF NOT EXISTS `advocate` (
  `advocate_id` int NOT NULL AUTO_INCREMENT,
  `login_id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualifications` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialization` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` tinyint UNSIGNED DEFAULT '0',
  `bar_council_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `house_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `place` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`advocate_id`),
  UNIQUE KEY `login_id` (`login_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `bar_council_no` (`bar_council_no`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advocate`
--

INSERT INTO `advocate` (`advocate_id`, `login_id`, `first_name`, `last_name`, `qualifications`, `specialization`, `experience_years`, `bar_council_no`, `gender`, `phone`, `email`, `house_name`, `place`, `pincode`, `approval_status`, `created_at`) VALUES
(1, 3, 'Jose', 'Mon', 'LLB', 'Criminal', 5, '6677888', 'Male', '9876543210', 'jose123@gmail.com', 'Jose House', 'Ernakulam', '123456', 'approved', '2026-10-10 21:27:37');

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

DROP TABLE IF EXISTS `cases`;
CREATE TABLE IF NOT EXISTS `cases` (
  `case_id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `type_id` int NOT NULL,
  `court_id` int DEFAULT NULL,
  `assigned_advocate_id` int DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `case_date` date DEFAULT NULL,
  `police_station` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','proposed','assigned','ongoing','closed','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`case_id`),
  KEY `fk_cases_client` (`client_id`),
  KEY `fk_cases_type` (`type_id`),
  KEY `fk_cases_court` (`court_id`),
  KEY `fk_cases_advocate` (`assigned_advocate_id`),
  KEY `idx_cases_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`case_id`, `client_id`, `type_id`, `court_id`, `assigned_advocate_id`, `title`, `description`, `case_date`, `police_station`, `pincode`, `phone`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 1, 'hhfjdkkdkkdk', 'dfghjklcvbnmdfghjklvbnm', '2026-10-09', 'Tim Nolan', '789006', '9988765543', 'closed', '2026-10-10 21:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `case_file`
--

DROP TABLE IF EXISTS `case_file`;
CREATE TABLE IF NOT EXISTS `case_file` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `case_id` int NOT NULL,
  `uploaded_by_login_id` int NOT NULL,
  `file_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`),
  KEY `fk_file_case` (`case_id`),
  KEY `fk_file_uploader` (`uploaded_by_login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_file`
--

INSERT INTO `case_file` (`file_id`, `case_id`, `uploaded_by_login_id`, `file_title`, `file_path`, `uploaded_at`) VALUES
(1, 1, 3, 'hgfds', 'uploads/case_1/7e68585f0653ed58.docx', '2026-10-10 22:47:06'),
(2, 1, 2, 'vcxz', 'uploads/case_1/36a20233958af74f.docx', '2026-10-10 22:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `case_note`
--

DROP TABLE IF EXISTS `case_note`;
CREATE TABLE IF NOT EXISTS `case_note` (
  `note_id` int NOT NULL AUTO_INCREMENT,
  `case_id` int NOT NULL,
  `author_login_id` int NOT NULL,
  `note_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`note_id`),
  KEY `fk_note_case` (`case_id`),
  KEY `fk_note_author` (`author_login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_note`
--

INSERT INTO `case_note` (`note_id`, `case_id`, `author_login_id`, `note_datetime`, `description`) VALUES
(1, 1, 3, '2026-10-10 22:46:22', 'fdghsjkafhdjs'),
(2, 1, 3, '2026-10-10 22:46:42', 'fdhsjkafdjsk'),
(3, 1, 3, '2026-10-10 22:51:34', 'bgfds');

-- --------------------------------------------------------

--
-- Table structure for table `case_type`
--

DROP TABLE IF EXISTS `case_type`;
CREATE TABLE IF NOT EXISTS `case_type` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_type`
--

INSERT INTO `case_type` (`type_id`, `type_name`, `description`) VALUES
(1, 'Criminal', 'Offences against the state or individuals'),
(2, 'Civil', 'Disputes between individuals or organisations'),
(3, 'Family', 'Marriage, divorce, custody and inheritance'),
(4, 'Property', 'Land, tenancy and ownership disputes'),
(5, 'Cyber', 'Online fraud, hacking and data offences'),
(6, 'jkj', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `chat_id` int NOT NULL AUTO_INCREMENT,
  `sender_login_id` int NOT NULL,
  `receiver_login_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`chat_id`),
  KEY `fk_chat_receiver` (`receiver_login_id`),
  KEY `idx_chat_pair` (`sender_login_id`,`receiver_login_id`,`sent_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`chat_id`, `sender_login_id`, `receiver_login_id`, `message`, `sent_at`, `is_read`) VALUES
(1, 2, 3, 'hello', '2026-10-10 22:39:08', 1),
(2, 3, 2, 'hello how can i help you', '2026-10-10 22:44:13', 1),
(3, 2, 3, 'i would like to consult', '2026-10-10 22:44:26', 1),
(4, 3, 2, 'sure', '2026-10-10 22:44:31', 1),
(5, 3, 2, 'can you explain', '2026-10-10 22:44:41', 1),
(6, 2, 3, 'Ok', '2026-10-10 22:44:48', 1),
(7, 3, 2, 'i have uploaded the details', '2026-10-10 22:47:32', 1),
(8, 2, 3, 'ok sir thank you', '2026-10-10 22:47:41', 1),
(9, 2, 3, '^V^', '2026-10-10 22:47:44', 1),
(10, 3, 2, 'you are welcome ^-^', '2026-10-10 22:47:56', 1),
(11, 3, 2, 'Payment received Thanks', '2026-10-10 22:50:13', 1),
(12, 3, 2, '^V^', '2026-10-10 22:50:17', 1),
(13, 2, 3, 'no probem ^v^', '2026-10-10 22:50:29', 1),
(14, 3, 2, 'The case is closed', '2026-10-10 22:51:57', 1),
(15, 2, 3, 'ok thanks for your help ^v^', '2026-10-10 22:52:09', 1),
(16, 3, 2, 'your are welcome ^V^', '2026-10-10 22:52:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `client_id` int NOT NULL AUTO_INCREMENT,
  `login_id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `house_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `place` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_id`),
  UNIQUE KEY `login_id` (`login_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`client_id`, `login_id`, `first_name`, `last_name`, `gender`, `dob`, `phone`, `email`, `house_name`, `place`, `pincode`, `created_at`) VALUES
(1, 2, 'Mina', 'Dracul', 'Female', '1999-12-19', '9988765543', 'mina123@gmail.com', 'Translaynia House', 'Europe', '789006', '2026-10-10 20:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `complaint_id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply` text COLLATE utf8mb4_unicode_ci,
  `complaint_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reply_date` datetime DEFAULT NULL,
  `status` enum('open','replied','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  PRIMARY KEY (`complaint_id`),
  KEY `fk_complaint_client` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `client_id`, `description`, `reply`, `complaint_date`, `reply_date`, `status`) VALUES
(1, 1, 'No complaints', 'nbvcx', '2026-10-10 22:53:12', '2026-10-10 17:27:27', 'replied');

-- --------------------------------------------------------

--
-- Table structure for table `contact_message`
--

DROP TABLE IF EXISTS `contact_message`;
CREATE TABLE IF NOT EXISTS `contact_message` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_message`
--

INSERT INTO `contact_message` (`message_id`, `name`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'fchdj', 'rose123@gmail.com', 'gfdjsk', 'fyduskgfhdjskfbdnsm,', 0, '2026-10-10 23:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `court`
--

DROP TABLE IF EXISTS `court`;
CREATE TABLE IF NOT EXISTS `court` (
  `court_id` int NOT NULL AUTO_INCREMENT,
  `login_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `place` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`court_id`),
  UNIQUE KEY `login_id` (`login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `court`
--

INSERT INTO `court` (`court_id`, `login_id`, `name`, `place`, `pincode`, `phone`, `email`) VALUES
(1, NULL, 'District Court', 'Ernakulam', '682011', '0484000000', 'court@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `hearing`
--

DROP TABLE IF EXISTS `hearing`;
CREATE TABLE IF NOT EXISTS `hearing` (
  `hearing_id` int NOT NULL AUTO_INCREMENT,
  `case_id` int NOT NULL,
  `court_id` int DEFAULT NULL,
  `hearing_date` datetime NOT NULL,
  `purpose` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','completed','adjourned','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `outcome` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`hearing_id`),
  KEY `fk_hearing_case` (`case_id`),
  KEY `fk_hearing_court` (`court_id`),
  KEY `idx_hearing_date` (`hearing_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hearing`
--

INSERT INTO `hearing` (`hearing_id`, `case_id`, `court_id`, `hearing_date`, `purpose`, `status`, `outcome`, `created_at`) VALUES
(1, 1, 1, '2026-10-17 17:45:00', 'hjgfcdgshj', 'completed', 'tttt', '2026-10-10 22:45:14'),
(2, 1, 1, '2026-10-25 22:51:00', 'fdsa', 'completed', NULL, '2026-10-10 22:51:21');

-- --------------------------------------------------------

--
-- Table structure for table `law_details`
--

DROP TABLE IF EXISTS `law_details`;
CREATE TABLE IF NOT EXISTS `law_details` (
  `law_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ipc_section` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bns_section` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `penalty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`law_id`),
  KEY `idx_law_title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `law_details`
--

INSERT INTO `law_details` (`law_id`, `title`, `ipc_section`, `bns_section`, `description`, `penalty`) VALUES
(1, 'Murder', '302', '103', 'Causing the death of a person with intention', 'Death or life imprisonment, and fine'),
(2, 'Theft', '379', '303', 'Dishonestly taking movable property', 'Up to 3 years, or fine, or both'),
(3, 'Cheating', '420', '318', 'Cheating and dishonestly inducing delivery of property', 'Up to 7 years, and fine'),
(4, 'fdjkfd', '677', 'ge6', 'fdhsjka', 'gfhdjks');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `login_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` enum('admin','advocate','client','court') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`login_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`login_id`, `username`, `password_hash`, `user_type`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$2hesB2R6qszF2d/XTAVqiOLv7hn6qYqHEAtd9Ntw1keamNKI.wOOW', 'admin', 1, '2026-10-10 20:38:59'),
(2, 'Mina123', '$2y$10$KJvLqRox0DmSSHZeE/97mOQSclya30bp1b332WH0eDtbapmAxIJ1C', 'client', 1, '2026-10-10 20:59:44'),
(3, 'Jose123', '$2y$10$jP99j5UNC8SP86W.CT/Rt.eumk9c.QN76fu/x7WJPawacmM3gj22u', 'advocate', 1, '2026-10-10 21:27:37');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `published_date` date NOT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`news_id`),
  KEY `fk_news_admin` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`news_id`, `title`, `content`, `published_date`, `created_by`) VALUES
(1, 'UUUUU', 'fgdhsjakfhdsjak', '2026-10-10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriber`
--

DROP TABLE IF EXISTS `newsletter_subscriber`;
CREATE TABLE IF NOT EXISTS `newsletter_subscriber` (
  `subscriber_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`subscriber_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `login_id` int NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_notification_user` (`login_id`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `login_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 'Account approved', 'Your advocate account has been approved. You can now find cases and send proposals.', 1, '2026-10-10 22:37:17'),
(2, 2, 'New proposal', 'Jose sent a fee proposal for \"hhfjdkkdkkdk\".', 1, '2026-10-10 22:38:32'),
(3, 3, 'Proposal accepted', 'Your proposal for \"hhfjdkkdkkdk\" was accepted.', 1, '2026-10-10 22:39:02'),
(4, 2, 'Hearing scheduled', 'A hearing for \"hhfjdkkdkkdk\" is set for 17 Oct 2026, 05:45 PM.', 1, '2026-10-10 22:45:14'),
(5, 3, 'Payment received', 'Payment received for \"hhfjdkkdkkdk\". The case is now ongoing.', 1, '2026-10-10 22:49:53'),
(6, 2, 'Hearing scheduled', 'A hearing for \"hhfjdkkdkkdk\" is set for 25 Oct 2026, 10:51 PM.', 1, '2026-10-10 22:51:21'),
(7, 2, 'Case closed', 'Your case \"hhfjdkkdkkdk\" is closed. You can now rate your advocate.', 1, '2026-10-10 22:51:39'),
(8, 2, 'Complaint answered', 'The admin has replied to your complaint.', 0, '2026-10-10 22:57:27');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

DROP TABLE IF EXISTS `password_reset`;
CREATE TABLE IF NOT EXISTS `password_reset` (
  `reset_id` int NOT NULL AUTO_INCREMENT,
  `login_id` int NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reset_id`),
  KEY `fk_reset_login` (`login_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `proposal_id` int NOT NULL,
  `client_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('upi','card','netbanking','cash') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_ref` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `fk_payment_proposal` (`proposal_id`),
  KEY `fk_payment_client` (`client_id`)
) ;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `proposal_id`, `client_id`, `amount`, `method`, `transaction_ref`, `status`, `paid_at`, `created_at`) VALUES
(1, 1, 1, 4000.00, 'card', 'TXN261010171953278', 'paid', '2026-10-10 22:49:53', '2026-10-10 22:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `proposal`
--

DROP TABLE IF EXISTS `proposal`;
CREATE TABLE IF NOT EXISTS `proposal` (
  `proposal_id` int NOT NULL AUTO_INCREMENT,
  `case_id` int NOT NULL,
  `advocate_id` int NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `proposed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL,
  `status` enum('pending','accepted','rejected','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`proposal_id`),
  UNIQUE KEY `uq_proposal_case_advocate` (`case_id`,`advocate_id`),
  KEY `fk_proposal_advocate` (`advocate_id`)
) ;

--
-- Dumping data for table `proposal`
--

INSERT INTO `proposal` (`proposal_id`, `case_id`, `advocate_id`, `fee`, `proposed_at`, `responded_at`, `status`) VALUES
(1, 1, 1, 4000.00, '2026-10-10 22:38:32', '2026-10-10 22:39:02', 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `advocate_id` int NOT NULL,
  `rate` tinyint NOT NULL,
  `review` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `uq_rating_client_advocate` (`client_id`,`advocate_id`),
  KEY `fk_rating_advocate` (`advocate_id`)
) ;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `client_id`, `advocate_id`, `rate`, `review`, `created_at`) VALUES
(1, 1, 1, 5, 'A very good lawyer always very nice', '2026-10-10 22:52:57');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advocate`
--
ALTER TABLE `advocate`
  ADD CONSTRAINT `fk_advocate_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE;

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `fk_cases_advocate` FOREIGN KEY (`assigned_advocate_id`) REFERENCES `advocate` (`advocate_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cases_court` FOREIGN KEY (`court_id`) REFERENCES `court` (`court_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cases_type` FOREIGN KEY (`type_id`) REFERENCES `case_type` (`type_id`) ON DELETE RESTRICT;

--
-- Constraints for table `case_file`
--
ALTER TABLE `case_file`
  ADD CONSTRAINT `fk_file_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_file_uploader` FOREIGN KEY (`uploaded_by_login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE;

--
-- Constraints for table `case_note`
--
ALTER TABLE `case_note`
  ADD CONSTRAINT `fk_note_author` FOREIGN KEY (`author_login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_note_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE;

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`receiver_login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_sender` FOREIGN KEY (`sender_login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE;

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `fk_client_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_complaint_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `court`
--
ALTER TABLE `court`
  ADD CONSTRAINT `fk_court_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing`
--
ALTER TABLE `hearing`
  ADD CONSTRAINT `fk_hearing_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_court` FOREIGN KEY (`court_id`) REFERENCES `court` (`court_id`) ON DELETE SET NULL;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `fk_news_admin` FOREIGN KEY (`created_by`) REFERENCES `login` (`login_id`) ON DELETE SET NULL;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `fk_reset_login` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal` (`proposal_id`) ON DELETE CASCADE;

--
-- Constraints for table `proposal`
--
ALTER TABLE `proposal`
  ADD CONSTRAINT `fk_proposal_advocate` FOREIGN KEY (`advocate_id`) REFERENCES `advocate` (`advocate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_proposal_case` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_rating_advocate` FOREIGN KEY (`advocate_id`) REFERENCES `advocate` (`advocate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rating_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
