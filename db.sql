-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Sep 2025 pada 02.02
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `church_scheduling`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('pemuda','pria','wanita','sekolah_minggu','rayon') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` text DEFAULT NULL,
  `is_alternative` tinyint(1) DEFAULT 0,
  `scheduled_start_time` time DEFAULT NULL,
  `scheduled_end_time` time DEFAULT NULL,
  `is_urgent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `activity_type`, `title`, `description`, `date`, `start_time`, `end_time`, `status`, `created_at`, `rejection_reason`, `is_alternative`, `scheduled_start_time`, `scheduled_end_time`, `is_urgent`) VALUES
(1, 9, 'pemuda', 'sad', 'asd', '2025-09-20', '20:00:00', '21:00:00', 'approved', '2025-09-04 22:53:17', NULL, 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 9, 'Ada permohonan booking baru dari admin (URGENT)', 0, '2025-09-04 22:53:17'),
(2, 9, 'Permohonan booking Anda telah disetujui', 0, '2025-09-04 22:55:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `activity_type` enum('pemuda','pria','wanita','sekolah_minggu','rayon','tk_paud','doa','minggu') NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `organization` varchar(100) DEFAULT NULL,
  `is_fixed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`id`, `booking_id`, `activity_type`, `title`, `date`, `start_time`, `end_time`, `organization`, `is_fixed`, `created_at`) VALUES
(1, 1, 'pemuda', 'sad', '2025-09-20', '20:00:00', '21:00:00', '', 0, '2025-09-04 22:55:05'),
(2, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-05', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(3, NULL, 'doa', 'Ibadah Doa', '2025-09-06', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(4, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-09-07', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(5, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-08', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(6, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-09', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(7, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-10', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(8, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-11', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(9, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-12', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(10, NULL, 'doa', 'Ibadah Doa', '2025-09-13', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(11, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-09-14', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(12, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-15', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(13, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-16', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(14, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-17', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(15, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-18', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(16, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-19', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(17, NULL, 'doa', 'Ibadah Doa', '2025-09-20', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(18, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-09-21', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(19, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-22', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(20, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-23', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(21, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-24', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(22, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-25', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(23, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-26', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(24, NULL, 'doa', 'Ibadah Doa', '2025-09-27', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(25, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-09-28', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(26, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-29', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(27, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-09-30', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(28, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-01', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(29, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-02', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(30, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-03', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(31, NULL, 'doa', 'Ibadah Doa', '2025-10-04', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(32, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-10-05', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(33, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-06', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(34, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-07', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(35, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-08', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(36, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-09', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(37, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-10', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(38, NULL, 'doa', 'Ibadah Doa', '2025-10-11', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(39, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-10-12', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(40, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-13', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(41, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-14', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(42, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-15', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(43, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-16', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(44, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-17', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(45, NULL, 'doa', 'Ibadah Doa', '2025-10-18', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(46, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-10-19', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(47, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-20', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(48, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-21', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(49, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-22', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(50, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-23', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(51, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-24', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(52, NULL, 'doa', 'Ibadah Doa', '2025-10-25', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(53, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-10-26', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(54, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-27', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(55, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-28', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(56, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-29', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(57, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-30', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(58, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-10-31', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(59, NULL, 'doa', 'Ibadah Doa', '2025-11-01', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(60, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-11-02', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(61, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-03', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(62, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-04', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(63, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-05', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(64, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-06', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(65, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-07', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(66, NULL, 'doa', 'Ibadah Doa', '2025-11-08', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(67, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-11-09', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:15'),
(68, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-10', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(69, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-11', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(70, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-12', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(71, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-13', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:15'),
(72, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-14', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(73, NULL, 'doa', 'Ibadah Doa', '2025-11-15', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:16'),
(74, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-11-16', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:16'),
(75, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-17', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(76, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-18', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(77, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-19', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(78, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-20', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(79, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-21', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(80, NULL, 'doa', 'Ibadah Doa', '2025-11-22', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:16'),
(81, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-11-23', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:16'),
(82, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-24', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(83, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-25', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(84, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-26', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(85, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-27', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(86, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-11-28', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(87, NULL, 'doa', 'Ibadah Doa', '2025-11-29', '18:00:00', '20:00:00', 'Gereja', 1, '2025-09-04 22:55:16'),
(88, NULL, 'minggu', 'Ibadah Minggu Pagi', '2025-11-30', '09:30:00', '11:30:00', 'Gereja', 1, '2025-09-04 22:55:16'),
(89, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-12-01', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(90, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-12-02', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(91, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-12-03', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(92, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-12-04', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16'),
(93, NULL, 'tk_paud', 'Kegiatan Belajar Mengajar TK &amp; PAUD', '2025-12-05', '08:00:00', '12:00:00', 'TK &amp; PAUD', 1, '2025-09-04 22:55:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(6, 'Test User 6', 'testuser6@example.com', '$2y$10$yOqWJnXj8oZvXa/YE5y2eOvQJzNQfOwhDmMknXr.BD9KfQH6JsBXi', 'user', '2025-09-02 04:00:55'),
(7, 'Test User 7', 'testuser7@example.com', '$2y$10$v73Fe04v8V9h9cOtObTht.MREFlAHn64sw0RmyIA7KeoVapmR03r.', 'user', '2025-09-02 04:00:55'),
(8, 'Test User 8', 'testuser8@example.com', '$2y$10$ZjLAxiU0ItLCJ0ZW0aPUHenHSY/xmi053FJXolaBBSiQ3D0e5DU7u', 'user', '2025-09-02 04:00:55'),
(9, 'admin', 'admin@admin.com', '$2y$10$1xZI7gdA.NJjaM.6gtRXVegQOnTWPTNOg1RXb7nWFwiaVkK5bLtl6', 'admin', '2025-09-02 03:31:13'),
(10, 'user', 'user@user.com', '$2y$10$52nxFYiYz5wHcAndCloNIeGRUzt1yx1813TIE8A7oEsg0S8A1m/R.', 'user', '2025-09-03 23:56:41');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
