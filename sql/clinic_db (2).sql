-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 03:35 PM
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
-- Database: `clinic_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `appointmentCode` varchar(20) NOT NULL,
  `patientId` int(11) DEFAULT NULL,
  `doctorId` int(11) DEFAULT NULL,
  `appointmentDate` date NOT NULL,
  `appointmentTime` time NOT NULL,
  `channel` enum('Walk-in','Online','Phone','Referral','Follow-up') NOT NULL DEFAULT 'Walk-in',
  `status` enum('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `bookedByUserId` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `appointmentCode`, `patientId`, `doctorId`, `appointmentDate`, `appointmentTime`, `channel`, `status`, `remarks`, `bookedByUserId`, `createdAt`, `updatedAt`, `address`) VALUES
(2, 'APP-2026-0002', 2, 22, '2026-05-01', '11:30:00', 'Phone', 'Completed', '', NULL, '2026-04-30 14:50:18', '2026-05-04 06:33:05', NULL),
(3, 'APP-2026-0003', 3, 23, '2026-05-06', '08:30:00', 'Walk-in', 'Cancelled', '', NULL, '2026-04-30 14:51:38', '2026-05-04 06:26:43', NULL),
(4, 'APP-2026-0004', 4, 21, '2026-05-04', '14:30:00', 'Online', 'Completed', '', NULL, '2026-04-30 14:52:54', '2026-05-06 06:53:36', NULL),
(5, 'APP-2026-0005', 5, 25, '2026-05-03', '11:30:00', 'Walk-in', 'Cancelled', '', NULL, '2026-04-30 14:53:57', '2026-05-01 08:37:36', NULL),
(6, 'APP-2026-0006', 1, 24, '2026-05-01', '16:30:00', 'Walk-in', 'Pending', '', NULL, '2026-05-01 09:36:54', '2026-05-16 04:54:48', NULL),
(7, 'APP-2026-0007', 6, 21, '2026-05-04', '10:00:00', 'Online', 'Completed', 'test 1', 2, '2026-05-01 11:13:45', '2026-05-02 10:22:56', NULL),
(8, 'APP-2026-0008', NULL, 22, '2026-05-04', '09:30:00', 'Walk-in', 'Completed', '', NULL, '2026-05-04 05:36:11', '2026-05-04 06:19:26', NULL),
(10, 'APP-2026-0009', NULL, 23, '2026-05-13', '09:30:00', 'Online', 'Completed', '', 2, '2026-05-08 10:15:06', '2026-05-14 06:49:19', NULL),
(11, 'APP-2026-0010', NULL, 25, '2026-05-17', '09:30:00', 'Online', 'Completed', '', 2, '2026-05-10 12:52:24', '2026-05-15 14:07:40', NULL),
(12, 'APP-2026-0011', NULL, 24, '2026-05-16', '15:00:00', 'Online', 'Completed', '', 2, '2026-05-16 10:33:17', '2026-05-16 10:35:41', '123 Rizal St., Barangay 1, Cagayan de Oro City');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `doctorCode` varchar(20) DEFAULT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `contactNumber` varchar(20) DEFAULT NULL,
  `emailAddress` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `employmentStatus` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `status` enum('On Duty','Break','Off Duty') NOT NULL DEFAULT 'Off Duty',
  `patientCapacity` int(11) DEFAULT 20,
  `photoUrl` varchar(255) DEFAULT NULL,
  `prcLicenseNo` varchar(50) DEFAULT NULL,
  `yearsOfExperience` int(11) DEFAULT 0,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `doctorCode`, `firstName`, `middleName`, `lastName`, `gender`, `dateOfBirth`, `specialization`, `department`, `contactNumber`, `emailAddress`, `address`, `employmentStatus`, `status`, `patientCapacity`, `photoUrl`, `prcLicenseNo`, `yearsOfExperience`, `createdAt`, `updatedAt`, `notes`) VALUES
(21, 'DOC-2026-001', 'Rafael', 'Torres', 'Dela Cruz', 'Female', '1982-03-15', 'General Medicine', 'General', '0917123453', 'rafael.delacruz@gmail.com', '22 Rizal St., Poblacion, Davao City', 'Active', 'Off Duty', 20, '/Clinic_Appointment_System/app/uploads/doctors/DOC-21_773cc677da.jpg', '5842301', 14, '2026-04-30 10:59:04', '2026-05-21 14:32:01', ''),
(22, 'DOC-2026-002', 'Camille', 'Reyes', 'Fontaine', 'Female', '1988-07-20', 'Pediatrics', 'Child Health', '09182345678', 'camille.fontaine@gmail.com', '45 Mabini St., Agdao, Davao City', 'Active', 'Off Duty', 15, NULL, '6184792', 10, '2026-04-30 11:02:46', '2026-05-21 13:15:17', ''),
(23, 'DOC-2026-003', 'Elise', 'Vargas', 'Montero', 'Female', '1985-11-05', 'Dermatology', 'Skin & Hair', '09193456789', 'elise.montero@gmail.com', '78 Bonifacio St., Buhangin, Davao City', 'Active', 'Off Duty', 18, NULL, '7439284', 12, '2026-04-30 11:04:15', '2026-05-21 13:15:17', ''),
(24, 'DOC-2026-004', 'Adrian', 'Castillo', 'Severino', 'Male', '1979-09-30', 'OB-GYN', 'Maternal Care', '09204567890', 'adrian.severino@gmail.com', '33 Quezon Blvd., Talomo, Davao City', 'Active', 'Off Duty', 12, NULL, '3928156', 18, '2026-04-30 11:05:47', '2026-05-15 13:55:09', ''),
(25, 'DOC-2026-005', 'Vivienne', 'Lara', 'Estrada', 'Female', '1975-05-12', 'Cardiology', 'Internal Medicine', '09215678905', 'vivienne.estrada@gmail.com', '10 Roxas Ave., Matina, Davao City', 'Active', 'Off Duty', 23, NULL, '2267489', 22, '2026-04-30 11:06:58', '2026-05-02 13:08:54', '');

-- --------------------------------------------------------

--
-- Table structure for table `doctorschedules`
--

CREATE TABLE `doctorschedules` (
  `id` int(11) NOT NULL,
  `doctorId` int(11) NOT NULL,
  `dayOfWeek` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `shiftStart` time NOT NULL,
  `shiftEnd` time NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctorschedules`
--

INSERT INTO `doctorschedules` (`id`, `doctorId`, `dayOfWeek`, `shiftStart`, `shiftEnd`, `createdAt`) VALUES
(3, 22, 'Monday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(4, 22, 'Tuesday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(5, 22, 'Wednesday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(6, 22, 'Thursday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(7, 22, 'Friday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(8, 22, 'Saturday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(9, 22, 'Sunday', '08:00:00', '17:00:00', '2026-04-30 11:02:46'),
(12, 24, 'Friday', '08:00:00', '17:00:00', '2026-04-30 11:05:47'),
(13, 24, 'Saturday', '08:00:00', '17:00:00', '2026-04-30 11:05:47'),
(21, 23, 'Wednesday', '08:00:00', '17:00:00', '2026-04-30 11:13:27'),
(22, 23, 'Thursday', '08:00:00', '17:00:00', '2026-04-30 11:13:27'),
(32, 25, 'Sunday', '08:00:00', '17:00:00', '2026-05-02 13:08:54'),
(40, 21, 'Monday', '08:00:00', '17:00:00', '2026-05-21 14:32:01'),
(41, 21, 'Tuesday', '08:00:00', '17:00:00', '2026-05-21 14:32:01');

-- --------------------------------------------------------

--
-- Table structure for table `followups`
--

CREATE TABLE `followups` (
  `id` int(11) NOT NULL,
  `followUpCode` varchar(20) NOT NULL,
  `patientId` int(11) DEFAULT NULL,
  `appointmentId` int(11) DEFAULT NULL,
  `doctorId` int(11) DEFAULT NULL,
  `followUpDate` date NOT NULL,
  `followUpTime` time NOT NULL DEFAULT '08:00:00',
  `reason` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `followups`
--

INSERT INTO `followups` (`id`, `followUpCode`, `patientId`, `appointmentId`, `doctorId`, `followUpDate`, `followUpTime`, `reason`, `status`, `notes`, `createdAt`, `updatedAt`) VALUES
(1, 'FOL-2026-0001', 4, 4, 21, '2026-05-11', '08:30:00', 'Follow-up from record REC-2026-0004', 'Pending', NULL, '2026-04-30 14:59:46', '2026-05-16 10:03:33'),
(10, 'FOL-2026-0003', 4, 4, 3, '2026-05-18', '08:00:00', 'Follow-up from record REC-2026-0004', 'Pending', NULL, '2026-05-15 08:16:46', '2026-05-17 12:28:36'),
(27, 'FOL-2026-0004', NULL, NULL, 24, '2026-05-16', '16:30:00', 'Follow-up from record REC-2026-0009', 'Completed', NULL, '2026-05-16 14:54:36', '2026-05-17 12:32:31'),
(28, 'FOL-2026-0005', NULL, NULL, 24, '2026-05-22', '08:00:00', 'Follow-up from record REC-2026-0009', 'Pending', NULL, '2026-05-16 14:56:09', '2026-05-17 12:32:31');

-- --------------------------------------------------------

--
-- Table structure for table `medicalrecordaudit`
--

CREATE TABLE `medicalrecordaudit` (
  `id` int(11) NOT NULL,
  `recordId` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `changedBy` varchar(100) DEFAULT 'Admin',
  `changedAt` datetime DEFAULT current_timestamp(),
  `fieldName` varchar(100) DEFAULT NULL,
  `oldValue` text DEFAULT NULL,
  `newValue` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicalrecordaudit`
--

INSERT INTO `medicalrecordaudit` (`id`, `recordId`, `action`, `changedBy`, `changedAt`, `fieldName`, `oldValue`, `newValue`) VALUES
(1, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:25', NULL, NULL, NULL),
(2, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:26', NULL, NULL, NULL),
(3, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:27', NULL, NULL, NULL),
(4, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:27', NULL, NULL, NULL),
(5, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:27', NULL, NULL, NULL),
(6, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:28', NULL, NULL, NULL),
(7, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:28', NULL, NULL, NULL),
(8, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:28', NULL, NULL, NULL),
(9, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:43', NULL, NULL, NULL),
(10, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:43', NULL, NULL, NULL),
(11, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:43', NULL, NULL, NULL),
(12, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:44', NULL, NULL, NULL),
(13, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:14:44', NULL, NULL, NULL),
(14, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:16:05', NULL, NULL, NULL),
(15, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:16:06', NULL, NULL, NULL),
(16, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:16:09', NULL, NULL, NULL),
(17, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:25:21', NULL, NULL, NULL),
(18, 6, 'Edited', 'Admin Uoiea', '2026-05-02 20:26:03', NULL, NULL, NULL),
(19, 6, 'Edited', 'Admin Uoiea', '2026-05-02 21:06:13', NULL, NULL, NULL),
(20, 5, 'Edited', 'Admin Uoiea', '2026-05-02 21:09:19', NULL, NULL, NULL),
(21, 6, 'Edited', 'Admin Uoiea', '2026-05-02 21:12:05', NULL, NULL, NULL),
(22, 6, 'Edited', 'Admin Zoe', '2026-05-04 13:22:12', NULL, NULL, NULL),
(23, 5, 'Edited', 'Admin Uoiea', '2026-05-09 21:58:53', NULL, NULL, NULL),
(24, 7, 'Created', 'Admin Uoiea', '2026-05-14 14:51:36', NULL, NULL, NULL),
(25, 8, 'Created', 'Admin Uoiea', '2026-05-14 14:51:43', NULL, NULL, NULL),
(26, 9, 'Created', 'Admin Uoiea', '2026-05-14 14:51:46', NULL, NULL, NULL),
(27, 10, 'Created', 'Admin Uoiea', '2026-05-14 14:51:47', NULL, NULL, NULL),
(28, 11, 'Created', 'Admin Uoiea', '2026-05-14 14:51:48', NULL, NULL, NULL),
(29, 12, 'Created', 'Admin Uoiea', '2026-05-14 14:51:48', NULL, NULL, NULL),
(30, 13, 'Created', 'Admin Uoiea', '2026-05-14 14:51:48', NULL, NULL, NULL),
(31, 14, 'Created', 'Admin Uoiea', '2026-05-14 14:52:00', NULL, NULL, NULL),
(32, 15, 'Created', 'Admin Uoiea', '2026-05-14 14:53:43', NULL, NULL, NULL),
(33, 6, 'Status Changed', 'Admin Uoiea', '2026-05-14 19:52:00', NULL, 'Draft', 'Finalized'),
(34, 101, 'Created', 'Admin Uoiea', '2026-05-15 16:16:46', NULL, NULL, NULL),
(35, 102, 'Created', 'Admin Uoiea', '2026-05-15 16:16:48', NULL, NULL, NULL),
(36, 103, 'Created', 'Admin Uoiea', '2026-05-15 16:16:48', NULL, NULL, NULL),
(37, 104, 'Created', 'Admin Uoiea', '2026-05-15 16:16:48', NULL, NULL, NULL),
(38, 105, 'Created', 'Admin Uoiea', '2026-05-15 16:16:49', NULL, NULL, NULL),
(39, 106, 'Created', 'Admin Uoiea', '2026-05-15 16:16:49', NULL, NULL, NULL),
(40, 107, 'Created', 'Admin Uoiea', '2026-05-15 16:16:49', NULL, NULL, NULL),
(41, 108, 'Created', 'Admin Uoiea', '2026-05-15 16:16:49', NULL, NULL, NULL),
(42, 109, 'Created', 'Admin Uoiea', '2026-05-15 16:16:50', NULL, NULL, NULL),
(43, 110, 'Created', 'Admin Uoiea', '2026-05-15 16:16:50', NULL, NULL, NULL),
(44, 111, 'Created', 'Admin Uoiea', '2026-05-15 16:16:50', NULL, NULL, NULL),
(45, 112, 'Created', 'Admin Uoiea', '2026-05-15 16:16:50', NULL, NULL, NULL),
(46, 113, 'Created', 'Admin Uoiea', '2026-05-15 16:16:50', NULL, NULL, NULL),
(47, 114, 'Created', 'Admin Uoiea', '2026-05-15 16:16:51', NULL, NULL, NULL),
(48, 115, 'Created', 'Admin Uoiea', '2026-05-15 16:17:10', NULL, NULL, NULL),
(49, 116, 'Created', 'Admin Uoiea', '2026-05-15 16:18:02', NULL, NULL, NULL),
(50, 117, 'Created', 'Admin Uoiea', '2026-05-15 16:29:04', NULL, NULL, NULL),
(51, 118, 'Created', 'Admin Uoiea', '2026-05-16 22:54:36', NULL, NULL, NULL),
(52, 118, 'Status Changed', 'Admin Uoiea', '2026-05-16 22:55:00', NULL, 'Draft', 'Finalized'),
(53, 119, 'Created', 'Admin Uoiea', '2026-05-16 22:56:09', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medicalrecords`
--

CREATE TABLE `medicalrecords` (
  `id` int(11) NOT NULL,
  `recordCode` varchar(20) NOT NULL,
  `patientId` int(11) DEFAULT NULL,
  `doctorId` int(11) DEFAULT NULL,
  `appointmentId` int(11) DEFAULT NULL,
  `recordType` enum('Consultation','Lab Result','Prescription','Imaging','Other') NOT NULL DEFAULT 'Consultation',
  `diagnosis` text DEFAULT NULL,
  `icdCode` varchar(20) DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Draft','Finalized') NOT NULL DEFAULT 'Draft',
  `followUpDate` date DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `followUpId` int(11) DEFAULT NULL,
  `parentRecordId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicalrecords`
--

INSERT INTO `medicalrecords` (`id`, `recordCode`, `patientId`, `doctorId`, `appointmentId`, `recordType`, `diagnosis`, `icdCode`, `prescription`, `notes`, `status`, `followUpDate`, `createdAt`, `updatedAt`, `followUpId`, `parentRecordId`) VALUES
(1, 'REC-2026-0001', 1, 24, NULL, 'Consultation', 'Allergic Rhinitis', 'J30.9', 'Cetirizine 10mg PO daily; Fluticasone furoate 27.5mcg nasal spray, 2 sprays per nostril daily.', 'Avoid known allergens and dust.', 'Finalized', NULL, '2026-04-30 14:57:05', '2026-04-30 14:57:05', NULL, NULL),
(2, 'REC-2026-0002', 2, 22, NULL, 'Consultation', 'Acute upper respiratory infection', 'J06.9', 'Paracetamol 500mg PO every 4-6 hours PRN for fever; Salbutamol 2mg/5mL syrup 10mL PO TID.', 'Advised rest and increased oral fluid intake.', 'Finalized', NULL, '2026-04-30 14:57:48', '2026-04-30 14:57:48', NULL, NULL),
(3, 'REC-2026-0003', 3, 23, 3, 'Consultation', 'Encounter for routine child health examination', 'Z00.129', 'Ascorbic Acid (Vitamin C) 100mg PO daily.', '', 'Finalized', NULL, '2026-04-30 14:58:32', '2026-04-30 14:58:32', NULL, NULL),
(4, 'REC-2026-0004', 4, 21, 4, 'Lab Result', 'Pure hypercholesterolemia', 'E78.00', 'Atorvastatin 20mg PO at bedtime.', 'Follow-up in 3 months for repeat lipid profile.', 'Finalized', '2026-08-04', '2026-04-30 14:59:46', '2026-05-16 13:05:49', NULL, NULL),
(5, 'REC-2026-0005', 5, 25, 5, 'Prescription', 'Essential (primary) hypertension', 'I10', 'Losartan 50mg PO daily; Amlodipine 5mg PO daily.', 'Continued maintenance for BP control.', 'Finalized', NULL, '2026-04-30 15:00:57', '2026-05-09 13:58:53', NULL, NULL),
(6, 'REC-2026-0006', 6, 21, 7, 'Consultation', 'Type 2 Diabetes Mellitus', 'E11.9', 'test four', '', 'Finalized', NULL, '2026-05-01 11:20:26', '2026-05-14 11:52:00', NULL, NULL),
(15, 'REC-2026-0007', NULL, 23, 10, 'Consultation', 'a', 'E11.9', 'a', 'a', 'Finalized', '2026-05-15', '2026-05-14 06:53:42', '2026-05-14 06:53:42', NULL, NULL),
(101, 'REC-2026-0008', 4, NULL, NULL, 'Imaging', 'test', '', 'TEST', '', 'Draft', '2026-05-18', '2026-05-15 08:16:46', '2026-05-15 08:16:46', NULL, 4),
(118, 'REC-2026-0009', NULL, 24, 12, 'Consultation', 'test', 'E11.9', 'test', '', 'Finalized', '2026-05-22', '2026-05-16 14:54:36', '2026-05-16 14:55:00', NULL, NULL),
(119, 'REC-2026-0010', NULL, 24, NULL, 'Consultation', 'Coronary Artery Disease', 'I48.91', 'test two', '', 'Finalized', '2026-05-22', '2026-05-16 14:56:09', '2026-05-16 14:56:09', NULL, 118);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patientCode` varchar(20) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `contactNumber` varchar(20) DEFAULT NULL,
  `emailAddress` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `photoUrl` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patientCode`, `firstName`, `middleName`, `lastName`, `gender`, `dateOfBirth`, `contactNumber`, `emailAddress`, `address`, `status`, `photoUrl`, `notes`, `createdAt`, `updatedAt`) VALUES
(1, 'PAT-2026-001', 'Maria', 'Santos', 'Garcia', 'Female', '1999-09-18', '09187654322', 'm.garcia@gmail.com', '202 Masterson Ave., Upper Balulang, Cagayan de Oro City', 'Active', NULL, '', '2026-04-30 14:48:22', '2026-05-10 14:35:27'),
(2, 'PAT-2026-002', 'Juan', 'Dela Cruz', 'Luna', 'Male', '1975-11-05', '09271112233', 'jluna.art@yahoo.com', '789 Velez St., Camaman-an, Cagayan de Oro City', 'Active', '/Clinic_Appointment_System/app/uploads/patients/PAT-2026-002_1779375232.jpg', '', '2026-04-30 14:50:18', '2026-05-21 15:00:03'),
(3, 'PAT-2026-003', 'Elena', 'Beatriz', 'Reyes', 'Female', '1995-03-12', '09159998877', 'reyes.family@gmail.com', '123 Rizal St., Barangay 1, Cagayan de Oro City', 'Active', NULL, '', '2026-04-30 14:51:38', '2026-05-01 07:49:40'),
(4, 'PAT-2026-004', 'Ricardo', 'Protacio', 'Dalisay', 'Male', '1975-01-30', '09453334455', 'rick.dalisay@outlook.com', '101 Corrales Extension, Puntod, Cagayan de Oro City', 'Active', '/Clinic_Appointment_System/app/uploads/patients/PAT-2026-004_1779017402.jpg', '', '2026-04-30 14:52:54', '2026-05-21 13:52:48'),
(5, 'PAT-2026-005', 'Rafael', 'Benjamin', 'Tan', 'Male', '1988-07-22', '09172223344', 'rbtan85@gmail.com', '456 Magsaysay Ave., Lapasan, Cagayan de Oro City', 'Active', NULL, '', '2026-04-30 14:53:57', '2026-05-01 07:50:42'),
(6, 'PAT-2026-006', 'Zoe', 'Hilario', 'Lampadio', 'Female', '2006-04-12', '09067222246', 'lampadiozoe@gmail.com', '', 'Active', NULL, '', '2026-05-01 11:13:45', '2026-05-04 06:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `recentactivity`
--

CREATE TABLE `recentactivity` (
  `id` int(11) NOT NULL,
  `activityType` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `referenceId` int(11) DEFAULT NULL,
  `referenceType` varchar(50) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recentactivity`
--

INSERT INTO `recentactivity` (`id`, `activityType`, `description`, `referenceId`, `referenceType`, `createdAt`) VALUES
(1, 'Doctor Added', 'New doctor added: Dr. Rafael Dela Cruz (DOC-2026-001)', 21, 'Doctor', '2026-04-30 10:59:04'),
(2, 'Doctor Added', 'New doctor added: Dr. Camille Fontaine (DOC-2026-002)', 22, 'Doctor', '2026-04-30 11:02:46'),
(3, 'Doctor Added', 'New doctor added: Dr. Elise Montero (DOC-2026-003)', 23, 'Doctor', '2026-04-30 11:04:15'),
(4, 'Doctor Added', 'New doctor added: Dr. Adrian Severino (DOC-2026-004)', 24, 'Doctor', '2026-04-30 11:05:47'),
(5, 'Doctor Added', 'New doctor added: Dr. Vivienne Estrada (DOC-2026-005)', 25, 'Doctor', '2026-04-30 11:06:58'),
(6, 'doctor_status', 'Dr. Rafael Dela Cruz status changed to Off Duty', 21, 'Doctor', '2026-04-30 11:08:19'),
(7, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-04-30 11:08:28'),
(8, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-04-30 11:08:35'),
(9, 'Doctor Updated', 'Doctor updated: Dr. Elise Montero (ID: 23)', 23, 'Doctor', '2026-04-30 11:11:04'),
(10, 'Doctor Updated', 'Doctor updated: Dr. Elise Montero (ID: 23)', 23, 'Doctor', '2026-04-30 11:13:27'),
(11, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-04-30 11:18:24'),
(12, 'appointment', 'New appointment APP-2026-0001 booked for Maria Santos Garcia', 1, 'Appointment', '2026-04-30 14:48:22'),
(13, 'appointment', 'New appointment APP-2026-0002 booked for Juan Dela Cruz Luna', 2, 'Appointment', '2026-04-30 14:50:18'),
(14, 'appointment', 'New appointment APP-2026-0003 booked for Elena Beatriz Reyes', 3, 'Appointment', '2026-04-30 14:51:38'),
(15, 'appointment', 'New appointment APP-2026-0004 booked for Ricardo Protacio Dalisay', 4, 'Appointment', '2026-04-30 14:52:54'),
(16, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-04-30 14:52:58'),
(17, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-04-30 14:53:03'),
(18, 'appointment', 'New appointment APP-2026-0005 booked for Rafael Benjamin Tan', 5, 'Appointment', '2026-04-30 14:53:57'),
(19, 'appointment_update', 'Appointment APP-2026-0005 status changed to Completed', 5, 'Appointment', '2026-04-30 14:54:02'),
(20, 'New Record', 'Medical record REC-2026-0001 created', 1, 'Record', '2026-04-30 14:57:05'),
(21, 'New Record', 'Medical record REC-2026-0002 created', 2, 'Record', '2026-04-30 14:57:48'),
(22, 'New Record', 'Medical record REC-2026-0003 created', 3, 'Record', '2026-04-30 14:58:32'),
(23, 'New Record', 'Medical record REC-2026-0004 created', 4, 'Record', '2026-04-30 14:59:46'),
(24, 'New Follow-up', 'Follow-up FOL-2026-0001 created from REC-2026-0004', 1, 'Followup', '2026-04-30 14:59:46'),
(25, 'New Record', 'Medical record REC-2026-0005 created', 5, 'Record', '2026-04-30 15:00:57'),
(26, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-01 07:35:45'),
(27, 'patient', 'Patient record updated: Elena Beatriz Reyes', 3, 'Patient', '2026-05-01 07:46:19'),
(28, 'patient', 'Patient record updated: Rafael Benjamin Tan', 5, 'Patient', '2026-05-01 07:46:51'),
(29, 'patient', 'Patient record updated: Juan Dela Cruz Luna', 2, 'Patient', '2026-05-01 07:47:27'),
(30, 'patient', 'Patient record updated: Ricardo Protacio Dalisay', 4, 'Patient', '2026-05-01 07:47:58'),
(31, 'patient', 'Patient record updated: Maria Santos Garcia', 1, 'Patient', '2026-05-01 07:48:29'),
(32, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-01 07:48:45'),
(33, 'patient', 'Patient record updated: Maria Garcia', 1, 'Patient', '2026-05-01 07:49:28'),
(34, 'patient', 'Patient record updated: Elena Reyes', 3, 'Patient', '2026-05-01 07:49:40'),
(35, 'patient', 'Patient record updated: Ricardo Dalisay', 4, 'Patient', '2026-05-01 07:50:03'),
(36, 'patient', 'Patient record updated: Juan Luna', 2, 'Patient', '2026-05-01 07:50:27'),
(37, 'patient', 'Patient record updated: Rafael Tan', 5, 'Patient', '2026-05-01 07:50:42'),
(38, 'appointment_update', 'Appointment APP-2026-0002 status changed to Pending', 2, 'Appointment', '2026-05-01 08:31:01'),
(39, 'appointment_update', 'Appointment APP-2026-0002 status changed to Completed', 2, 'Appointment', '2026-05-01 08:31:25'),
(40, 'appointment_update', 'Appointment APP-2026-0003 status changed to Pending', 3, 'Appointment', '2026-05-01 08:33:43'),
(41, 'appointment_update', 'Appointment APP-2026-0003 status changed to In Progress', 3, 'Appointment', '2026-05-01 08:33:55'),
(42, 'appointment_update', 'Appointment APP-2026-0005 status changed to Cancelled', 5, 'Appointment', '2026-05-01 08:37:36'),
(43, 'appointment_update', 'Appointment APP-2026-0003 updated (status: In Progress)', 3, 'Appointment', '2026-05-01 08:46:59'),
(44, 'appointment_update', 'Appointment APP-2026-0004 updated (status: Completed)', 4, 'Appointment', '2026-05-01 08:47:33'),
(45, 'appointment_update', 'Appointment APP-2026-0003 status changed to Pending', 3, 'Appointment', '2026-05-01 09:07:59'),
(46, 'appointment_update', 'Appointment APP-2026-0003 updated (status: Pending)', 3, 'Appointment', '2026-05-01 09:08:08'),
(47, 'appointment_update', 'Appointment APP-2026-0003 updated (status: Pending)', 3, 'Appointment', '2026-05-01 09:08:13'),
(48, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-01 09:08:49'),
(49, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-05-01 09:15:58'),
(50, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-01 09:26:08'),
(51, 'appointment_update', 'Appointment APP-2026-0003 updated (status: Pending)', 3, 'Appointment', '2026-05-01 09:28:23'),
(52, 'appointment_update', 'Appointment APP-2026-0001 updated (status: Pending)', 1, 'Appointment', '2026-05-01 09:28:39'),
(53, 'appointment_update', 'Appointment APP-2026-0001 updated (status: Pending)', 1, 'Appointment', '2026-05-01 09:29:01'),
(54, 'appointment_update', 'Appointment APP-2026-0001 updated (status: Pending)', 1, 'Appointment', '2026-05-01 09:34:56'),
(55, 'appointment', 'New appointment APP-2026-0006 booked for Maria Garcia', 6, 'Appointment', '2026-05-01 09:36:54'),
(56, 'appointment_update', 'Appointment APP-2026-0006 status changed to Completed', 6, 'Appointment', '2026-05-01 09:36:57'),
(57, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-05-01 09:43:19'),
(58, 'appointment_update', 'Appointment APP-2026-0002 status changed to In Progress', 2, 'Appointment', '2026-05-01 09:43:21'),
(59, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-05-01 09:43:44'),
(60, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-05-01 09:43:56'),
(61, 'appointment_update', 'Appointment  updated (status: Pending)', 1, 'Appointment', '2026-05-01 09:44:19'),
(62, 'appointment_update', 'Appointment  updated (status: Pending)', 1, 'Appointment', '2026-05-01 11:03:32'),
(63, 'followup_update', 'Follow-up updated (status: Pending)', 1, 'Followup', '2026-05-01 11:08:41'),
(64, 'patient', 'New patient registered: Zoe Hilario Lampadio (PAT-2026-006)', 6, 'Patient', '2026-05-01 11:13:45'),
(65, 'appointment', 'Appointment APP-2026-0007 booked for Zoe Hilario Lampadio via booking form.', 7, 'Appointment', '2026-05-01 11:13:45'),
(66, 'cancel', 'Appointment APP-2026-0007 cancelled by patient.', 7, 'Appointment', '2026-05-01 11:13:53'),
(67, 'appointment_update', 'Appointment APP-2026-0007 status changed to In Progress', 7, 'Appointment', '2026-05-01 11:14:23'),
(68, 'appointment_update', 'Appointment APP-2026-0007 status changed to Completed', 7, 'Appointment', '2026-05-01 11:19:41'),
(69, 'New Record', 'Medical record REC-2026-0006 created', 6, 'Record', '2026-05-01 11:20:26'),
(70, 'appointment_update', 'Appointment APP-2026-0007 updated (status: Completed)', 7, 'Appointment', '2026-05-02 10:12:39'),
(71, 'appointment_update', 'Appointment APP-2026-0007 updated (status: Completed)', 7, 'Appointment', '2026-05-02 10:12:57'),
(72, 'appointment_update', 'Appointment APP-2026-0007 updated (status: Completed)', 7, 'Appointment', '2026-05-02 10:14:19'),
(73, 'appointment_update', 'Appointment APP-2026-0007 updated (status: Completed)', 7, 'Appointment', '2026-05-02 10:17:27'),
(74, 'appointment_update', 'Appointment APP-2026-0003 status changed to In Progress', 3, 'Appointment', '2026-05-02 10:20:29'),
(75, 'appointment_update', 'Appointment APP-2026-0003 status changed to Pending', 3, 'Appointment', '2026-05-02 10:20:32'),
(76, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-02 10:21:10'),
(77, 'appointment_update', 'Appointment APP-2026-0004 status changed to Pending', 4, 'Appointment', '2026-05-02 10:21:18'),
(78, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-02 10:22:21'),
(79, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-05-02 10:22:23'),
(80, 'appointment_update', 'Appointment APP-2026-0007 status changed to In Progress', 7, 'Appointment', '2026-05-02 10:22:54'),
(81, 'appointment_update', 'Appointment APP-2026-0007 status changed to Completed', 7, 'Appointment', '2026-05-02 10:22:56'),
(82, 'appointment_update', 'Appointment APP-2026-0003 updated (status: Pending)', 3, 'Appointment', '2026-05-02 13:04:46'),
(83, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-02 13:06:29'),
(84, 'Doctor Updated', 'Doctor updated: Dr. Vivienne Estrada (ID: 25)', 25, 'Doctor', '2026-05-02 13:06:39'),
(85, 'appointment_update', 'Appointment APP-2026-0004 updated (status: Completed)', 4, 'Appointment', '2026-05-02 13:07:10'),
(86, 'appointment_update', 'Appointment APP-2026-0003 updated (status: Pending)', 3, 'Appointment', '2026-05-02 13:07:20'),
(87, 'appointment_update', 'Appointment APP-2026-0003 updated (status: Pending)', 3, 'Appointment', '2026-05-02 13:07:32'),
(88, 'Doctor Updated', 'Doctor updated: Dr. Vivienne Estrada (ID: 25)', 25, 'Doctor', '2026-05-02 13:08:54'),
(89, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-02 13:09:07'),
(90, 'Doctor Added', 'New doctor added: Dr. Iza Dy (DOC-2026-006)', 26, 'Doctor', '2026-05-04 05:29:06'),
(91, 'patient', 'New patient registered: Rafael Dela Cruz (PAT-2026-007)', 7, 'Patient', '2026-05-04 05:34:33'),
(92, 'appointment', 'New appointment APP-2026-0008 booked for Maria Santos Luna', 8, 'Appointment', '2026-05-04 05:36:11'),
(93, 'appointment_update', 'Appointment APP-2026-0008 updated (status: Pending)', 8, 'Appointment', '2026-05-04 05:36:50'),
(94, 'appointment_update', 'Appointment APP-2026-0008 updated (status: Pending)', 8, 'Appointment', '2026-05-04 05:36:59'),
(95, 'appointment_update', 'Appointment APP-2026-0008 updated (status: Pending)', 8, 'Appointment', '2026-05-04 05:37:06'),
(96, 'appointment_update', 'Appointment APP-2026-0008 status changed to In Progress', 8, 'Appointment', '2026-05-04 06:18:01'),
(97, 'appointment_update', 'Appointment APP-2026-0008 status changed to Cancelled', 8, 'Appointment', '2026-05-04 06:19:23'),
(98, 'appointment_update', 'Appointment APP-2026-0008 status changed to Completed', 8, 'Appointment', '2026-05-04 06:19:26'),
(99, 'appointment_update', 'Appointment APP-2026-0003 status changed to In Progress', 3, 'Appointment', '2026-05-04 06:22:38'),
(100, 'appointment_update', 'Appointment APP-2026-0003 status changed to Cancelled', 3, 'Appointment', '2026-05-04 06:22:48'),
(101, 'appointment_update', 'Appointment APP-2026-0003 status changed to In Progress', 3, 'Appointment', '2026-05-04 06:22:50'),
(102, 'appointment_update', 'Appointment APP-2026-0003 status changed to In Progress', 3, 'Appointment', '2026-05-04 06:22:58'),
(103, 'appointment_update', 'Appointment APP-2026-0003 status changed to Cancelled', 3, 'Appointment', '2026-05-04 06:23:04'),
(104, 'appointment_update', 'Appointment APP-2026-0003 status changed to In Progress', 3, 'Appointment', '2026-05-04 06:23:06'),
(105, 'appointment_update', 'Appointment APP-2026-0004 status changed to Cancelled', 4, 'Appointment', '2026-05-04 06:24:50'),
(106, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-04 06:25:22'),
(107, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-04 06:25:25'),
(108, 'appointment_update', 'Appointment APP-2026-0003 status changed to Cancelled', 3, 'Appointment', '2026-05-04 06:26:43'),
(109, 'appointment_update', 'Appointment APP-2026-0002 status changed to Completed', 2, 'Appointment', '2026-05-04 06:33:05'),
(110, 'patient', 'Patient record updated: Zoe Lampadio', 6, 'Patient', '2026-05-04 06:42:28'),
(111, 'patient', 'New patient registered: Lex Basanes Rabosa (PAT-2026-008)', 9, 'Patient', '2026-05-04 06:43:37'),
(112, 'appointment', 'Appointment APP-2026-0009 booked for Lex Rabosa via booking form.', 9, 'Appointment', '2026-05-04 06:43:37'),
(113, 'cancel', 'Appointment APP-2026-0009 cancelled by patient.', 9, 'Appointment', '2026-05-04 10:20:39'),
(114, 'appointment_update', 'Appointment APP-2026-0004 status changed to In Progress', 4, 'Appointment', '2026-05-06 06:52:07'),
(115, 'followup_update', 'Follow-up updated (status: Completed)', 1, 'Followup', '2026-05-06 06:52:30'),
(116, 'appointment_update', 'Appointment APP-2026-0004 status changed to Completed', 4, 'Appointment', '2026-05-06 06:53:36'),
(117, 'patient', 'New patient registered: Trisha Abellar Martizano (PAT-2026-008)', 10, 'Patient', '2026-05-08 10:15:06'),
(118, 'appointment', 'Appointment APP-2026-0009 booked for Trisha Martizano via booking form.', 10, 'Appointment', '2026-05-08 10:15:06'),
(119, 'doctor_status', 'Dr. Rafael Dela Cruz status changed to Break', 21, 'Doctor', '2026-05-09 11:50:22'),
(120, 'doctor_status', 'Dr. Rafael Dela Cruz status changed to On Duty', 21, 'Doctor', '2026-05-09 11:50:23'),
(121, 'doctor_status', 'Dr. Rafael Dela Cruz status changed to Off Duty', 21, 'Doctor', '2026-05-09 11:50:25'),
(122, 'patient', 'New patient registered: test one two (PAT-2026-009)', 11, 'Patient', '2026-05-10 12:52:24'),
(123, 'appointment', 'Appointment APP-2026-0010 booked for test two via booking form.', 11, 'Appointment', '2026-05-10 12:52:24'),
(124, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-10 13:58:31'),
(125, 'patient', 'Patient record updated: Maria Garcia', 1, 'Patient', '2026-05-10 14:35:27'),
(126, 'doctor_status', 'Dr. Elise Montero status changed to On Duty', 23, 'Doctor', '2026-05-11 14:23:25'),
(127, 'doctor_status', 'Dr. Elise Montero status changed to Off Duty', 23, 'Doctor', '2026-05-11 14:23:27'),
(128, 'doctor_status', 'Dr. Rafael Dela Cruz status changed to Break', 21, 'Doctor', '2026-05-12 15:17:30'),
(129, 'doctor_status', 'Dr. Rafael Dela Cruz status changed to On Duty', 21, 'Doctor', '2026-05-12 15:17:33'),
(130, 'appointment_status', 'Appointment #10 status → In Progress.', NULL, NULL, '2026-05-14 06:45:36'),
(131, 'appointment_status', 'Appointment #10 status → In Progress.', NULL, NULL, '2026-05-14 06:45:39'),
(132, 'appointment_status', 'Appointment #10 status → Completed.', NULL, NULL, '2026-05-14 06:49:19'),
(133, 'followup_status', 'Follow-up #1 status → In Progress.', NULL, NULL, '2026-05-15 13:02:45'),
(134, 'followup_status', 'Follow-up #1 status → In Progress.', NULL, NULL, '2026-05-15 13:03:38'),
(135, 'followup_status', 'Follow-up #1 status → In Progress.', NULL, NULL, '2026-05-15 13:03:44'),
(136, 'followup_status', 'Follow-up #1 status → Cancelled.', NULL, NULL, '2026-05-15 13:03:46'),
(137, 'followup_status', 'Follow-up #1 status → In Progress.', NULL, NULL, '2026-05-15 13:03:48'),
(138, 'followup_status', 'Follow-up #1 status → Cancelled.', NULL, NULL, '2026-05-15 13:04:22'),
(139, 'followup_status', 'Follow-up #1 status → In Progress.', NULL, NULL, '2026-05-15 13:04:33'),
(140, 'followup_updated', 'Follow-up #1 updated.', NULL, NULL, '2026-05-16 10:03:33'),
(141, 'followup_updated', 'Follow-up #1 updated.', NULL, NULL, '2026-05-16 10:03:41'),
(142, 'patient', 'New patient registered: test  one', 12, 'Patient', '2026-05-16 10:28:32'),
(143, 'patient', 'New patient registered: test  one', 13, 'Patient', '2026-05-16 10:28:33'),
(144, 'patient', 'New patient registered: test  one', 14, 'Patient', '2026-05-16 10:28:42'),
(145, 'patient', 'New patient registered: test  one', 15, 'Patient', '2026-05-16 10:29:30'),
(146, 'patient', 'New patient registered: test  one', 16, 'Patient', '2026-05-16 10:29:37'),
(147, 'patient', 'New patient registered: test  one', 17, 'Patient', '2026-05-16 10:29:53'),
(148, 'patient', 'New patient registered: test  two', 18, 'Patient', '2026-05-16 10:33:17'),
(149, 'appointment', 'Appointment APP-2026-0011 booked for test two via booking form.', 12, 'Appointment', '2026-05-16 10:33:17'),
(150, 'appointment_status', 'Appointment #12 status → In Progress.', NULL, NULL, '2026-05-16 10:33:38'),
(151, 'appointment_status', 'Appointment #12 status → In Progress.', NULL, NULL, '2026-05-16 10:33:51'),
(152, 'appointment_status', 'Appointment #12 status → Completed.', NULL, NULL, '2026-05-16 10:35:41'),
(153, 'followup_updated', 'Follow-up #27 updated.', NULL, NULL, '2026-05-16 14:55:34'),
(154, 'followup_status', 'Follow-up #27 status → Completed.', NULL, NULL, '2026-05-16 14:55:43'),
(155, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-17 11:18:08'),
(156, 'patient', 'Patient record updated: Ricardo Dalisay', 4, 'Patient', '2026-05-17 11:30:02'),
(157, 'patient_status', 'Patient Ricardo Dalisay status changed to Discharged', 4, 'Patient', '2026-05-20 13:02:06'),
(158, 'patient_status', 'Patient Ricardo Dalisay status changed to Active', 4, 'Patient', '2026-05-20 13:02:09'),
(159, 'patient_status', 'Patient Ricardo Dalisay status changed to Discharged', 4, 'Patient', '2026-05-20 13:08:45'),
(160, 'patient_status', 'Patient Ricardo Dalisay status changed to Active', 4, 'Patient', '2026-05-20 13:08:53'),
(161, 'patient', 'Patient record updated: Juan Luna', 2, 'Patient', '2026-05-21 13:58:36'),
(162, 'Doctor Updated', 'Doctor updated: Dr. Rafael Dela Cruz (ID: 21)', 21, 'Doctor', '2026-05-21 14:32:01'),
(163, 'patient', 'Patient record updated: Juan Luna', 2, 'Patient', '2026-05-21 14:53:52'),
(164, 'patient', 'Patient record updated: Juan Luna', 2, 'Patient', '2026-05-21 15:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `emailAddress` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `firstName` varchar(100) DEFAULT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `street` varchar(150) DEFAULT NULL,
  `barangay` varchar(150) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `photoUrl` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profilePic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `username`, `emailAddress`, `password`, `role`, `firstName`, `middleName`, `lastName`, `street`, `barangay`, `city`, `photoUrl`, `createdAt`, `updatedAt`, `profilePic`) VALUES
(1, NULL, 'admin', 'relosakate@gmail.com', '$2y$10$qHhBWFnFREJy7mfEo0sFZeNzASfJQ2FgBsBvG53INhHbDf/sctCUy', 'admin', 'Uoiea Kate', 'Donghil', 'Relosa', 'Zone 1B', 'San Miguel', 'Manolo Fortich, Bukidnon', NULL, '2026-04-30 10:20:41', '2026-05-19 13:51:44', '/Clinic_Appointment_System/uploads/profiles/user_1_1779198704.jpg'),
(2, NULL, 'user', 'lampadiozoe@gmail.com', '$2y$10$Qc8ZKjE.bMUmClZRMCrKp.Ck7ovcYoGD14Qxv9xdwu7Hq4Ku6mF0K', 'user', 'Zoe', 'Hilario', 'Lampadio', 'Zone 5', 'Patag', 'Cagayan de Oro City', NULL, '2026-04-30 10:46:18', '2026-05-21 14:31:43', '/Clinic_Appointment_System/app/uploads/profiles/user_2_1779373903.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointmentCode` (`appointmentCode`),
  ADD KEY `patientId` (`patientId`),
  ADD KEY `doctorId` (`doctorId`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `doctorCode` (`doctorCode`);

--
-- Indexes for table `doctorschedules`
--
ALTER TABLE `doctorschedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctorId` (`doctorId`);

--
-- Indexes for table `followups`
--
ALTER TABLE `followups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `followUpCode` (`followUpCode`),
  ADD KEY `patientId` (`patientId`),
  ADD KEY `appointmentId` (`appointmentId`);

--
-- Indexes for table `medicalrecordaudit`
--
ALTER TABLE `medicalrecordaudit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recordId` (`recordId`);

--
-- Indexes for table `medicalrecords`
--
ALTER TABLE `medicalrecords`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `recordCode` (`recordCode`),
  ADD KEY `patientId` (`patientId`),
  ADD KEY `doctorId` (`doctorId`),
  ADD KEY `appointmentId` (`appointmentId`),
  ADD KEY `fk_mr_followup` (`followUpId`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patientCode` (`patientCode`);

--
-- Indexes for table `recentactivity`
--
ALTER TABLE `recentactivity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `doctorschedules`
--
ALTER TABLE `doctorschedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `followups`
--
ALTER TABLE `followups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `medicalrecordaudit`
--
ALTER TABLE `medicalrecordaudit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `medicalrecords`
--
ALTER TABLE `medicalrecords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `recentactivity`
--
ALTER TABLE `recentactivity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appt_doctor` FOREIGN KEY (`doctorId`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appt_patient` FOREIGN KEY (`patientId`) REFERENCES `patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `doctorschedules`
--
ALTER TABLE `doctorschedules`
  ADD CONSTRAINT `fk_ds_doctor` FOREIGN KEY (`doctorId`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `followups`
--
ALTER TABLE `followups`
  ADD CONSTRAINT `fk_fu_appt` FOREIGN KEY (`appointmentId`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fu_patient` FOREIGN KEY (`patientId`) REFERENCES `patients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `medicalrecords`
--
ALTER TABLE `medicalrecords`
  ADD CONSTRAINT `fk_mr_appt` FOREIGN KEY (`appointmentId`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mr_doctor` FOREIGN KEY (`doctorId`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mr_followup` FOREIGN KEY (`followUpId`) REFERENCES `followups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mr_patient` FOREIGN KEY (`patientId`) REFERENCES `patients` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
