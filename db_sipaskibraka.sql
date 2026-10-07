-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Jun 2026 pada 16.03
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
-- Database: `db_sipaskibraka`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','superadmin') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `nama`, `username`, `password`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$29gtKsD8qee/u03UN5hHZOPxg07yVC6Fvw7aqISKS/VCmlUOWsM4i', 'admin', 1, '2026-06-19 15:11:26', '2026-05-14 08:47:28', '2026-06-19 15:11:26'),
(2, 'Super Administrator', 'superadmin', '$2y$12$vjjHrBArDe7gRxb4LMyF3.S.1hsxZKALehKzwjpgCd4iaqxevi3F6', 'superadmin', 1, '2026-06-18 07:21:31', '2026-05-14 08:47:28', '2026-06-18 07:21:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_latih`
--

CREATE TABLE `data_latih` (
  `id` int(11) NOT NULL,
  `nama_referensi` varchar(150) NOT NULL,
  `label` enum('Lulus','Tidak Lulus') NOT NULL,
  `nilai_pip` decimal(5,2) NOT NULL DEFAULT 0.00,
  `nilai_tiu` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tinggi_badan` decimal(5,1) NOT NULL DEFAULT 0.0,
  `berat_badan` decimal(5,1) NOT NULL DEFAULT 0.0,
  `kesehatan` tinyint(1) NOT NULL DEFAULT 1,
  `push_up` smallint(6) NOT NULL DEFAULT 0,
  `sit_up` smallint(6) NOT NULL DEFAULT 0,
  `back_up` smallint(6) NOT NULL DEFAULT 0,
  `lari` decimal(4,1) NOT NULL DEFAULT 0.0,
  `pbb` decimal(5,2) NOT NULL DEFAULT 0.00,
  `kepribadian` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_latih`
--

INSERT INTO `data_latih` (`id`, `nama_referensi`, `label`, `nilai_pip`, `nilai_tiu`, `tinggi_badan`, `berat_badan`, `kesehatan`, `push_up`, `sit_up`, `back_up`, `lari`, `pbb`, `kepribadian`, `created_at`) VALUES
(14, 'ABDUL AJIS', 'Tidak Lulus', 74.00, 73.00, 176.0, 68.0, 0, 23, 23, 25, 10.0, 76.00, 75.00, '2026-05-14 08:54:38'),
(15, 'ADITYA INDRA PRAMANA', 'Lulus', 91.00, 92.00, 174.0, 59.0, 1, 26, 25, 30, 14.0, 86.00, 89.00, '2026-05-14 08:54:38'),
(16, 'ADITYA ZIQRI WARDANA', 'Lulus', 90.00, 94.00, 173.0, 60.0, 1, 30, 30, 26, 12.0, 91.00, 87.00, '2026-05-14 08:54:38'),
(17, 'AHMAD WIRANTO', 'Tidak Lulus', 76.00, 77.00, 175.0, 69.0, 1, 26, 23, 24, 11.0, 72.00, 73.00, '2026-05-14 08:54:38'),
(18, 'AIF ARDABILI', 'Tidak Lulus', 72.00, 71.00, 170.0, 56.0, 0, 26, 26, 26, 10.0, 75.00, 71.00, '2026-05-14 08:54:38'),
(19, 'AISYAH NUR AZIZAH', 'Tidak Lulus', 70.00, 76.00, 171.0, 58.0, 1, 26, 26, 23, 12.0, 73.00, 75.00, '2026-05-14 08:54:38'),
(20, 'AISYAH PUTRI', 'Tidak Lulus', 78.00, 75.00, 171.0, 65.0, 1, 24, 26, 26, 12.0, 77.00, 75.00, '2026-05-14 08:54:38'),
(21, 'AKBAR DELFIANDRI', 'Tidak Lulus', 78.00, 70.00, 172.0, 58.0, 1, 26, 23, 25, 10.0, 75.00, 78.00, '2026-05-14 08:54:38'),
(22, 'AKMAL MIZAZ', 'Tidak Lulus', 76.00, 73.00, 172.0, 67.0, 0, 23, 23, 24, 10.0, 77.00, 72.00, '2026-05-14 08:54:38'),
(23, 'ALBAN JUANDIKA PUTRA', 'Tidak Lulus', 75.00, 75.00, 170.0, 59.0, 0, 25, 26, 23, 12.0, 70.00, 76.00, '2026-05-14 08:54:38'),
(24, 'ALIEF AL FAJAR', 'Tidak Lulus', 75.00, 75.00, 172.0, 62.0, 1, 25, 25, 26, 10.0, 74.00, 76.00, '2026-05-14 08:54:38'),
(25, 'ALIF BAROKAH', 'Tidak Lulus', 71.00, 78.00, 172.0, 62.0, 1, 25, 23, 24, 12.0, 74.00, 76.00, '2026-05-14 08:54:38'),
(26, 'ALIFIAN NUGRAHA', 'Tidak Lulus', 76.00, 73.00, 173.0, 63.0, 0, 26, 24, 23, 11.0, 70.00, 77.00, '2026-05-14 08:54:38'),
(27, 'ALIKA AULIA NUR\'AIN', 'Lulus', 85.00, 93.00, 171.0, 66.0, 1, 25, 30, 28, 13.0, 81.00, 95.00, '2026-05-14 08:54:38'),
(28, 'ALMUGHNI DAFANO PUTRA SUSANTO', 'Tidak Lulus', 70.00, 74.00, 172.0, 61.0, 1, 26, 23, 26, 12.0, 78.00, 73.00, '2026-05-14 08:54:38'),
(29, 'ALVIN PRATAMA', 'Tidak Lulus', 70.00, 74.00, 175.0, 64.0, 1, 23, 24, 24, 10.0, 72.00, 76.00, '2026-05-14 08:54:38'),
(30, 'AMELIA PUTRI', 'Tidak Lulus', 72.00, 74.00, 166.0, 56.0, 1, 25, 24, 26, 11.0, 70.00, 78.00, '2026-05-14 08:54:38'),
(31, 'ANDIKA NAZWALI PRATAMA', 'Tidak Lulus', 74.00, 72.00, 173.0, 66.0, 1, 23, 26, 23, 10.0, 73.00, 76.00, '2026-05-14 08:54:38'),
(32, 'ANHAR', 'Tidak Lulus', 77.00, 71.00, 182.0, 73.0, 1, 23, 23, 26, 11.0, 77.00, 78.00, '2026-05-14 08:54:38'),
(33, 'ANISA RAHMAWATI', 'Tidak Lulus', 77.00, 71.00, 165.0, 55.0, 1, 23, 23, 25, 12.0, 76.00, 71.00, '2026-05-14 08:54:38'),
(34, 'ANNISA MIRA RAMADHANI', 'Lulus', 95.00, 89.00, 166.0, 58.0, 1, 26, 26, 29, 12.0, 88.00, 86.00, '2026-05-14 08:54:38'),
(35, 'ARIL APRIANSYAH', 'Lulus', 84.00, 93.00, 176.0, 68.0, 1, 27, 29, 30, 14.0, 92.00, 94.00, '2026-05-14 08:54:38'),
(36, 'ARYA FIRMAN', 'Tidak Lulus', 70.00, 71.00, 172.0, 64.0, 1, 25, 25, 23, 10.0, 73.00, 72.00, '2026-05-14 08:54:38'),
(37, 'BIMA CANDRA FARDILA', 'Tidak Lulus', 75.00, 76.00, 172.0, 64.0, 0, 24, 25, 24, 11.0, 71.00, 75.00, '2026-05-14 08:54:38'),
(38, 'BINTANG SYAHRU RAMADHAN', 'Tidak Lulus', 71.00, 70.00, 175.0, 63.0, 1, 23, 25, 23, 12.0, 76.00, 77.00, '2026-05-14 08:54:38'),
(39, 'CAHAYA AINI SUSANTO', 'Lulus', 83.00, 86.00, 168.0, 60.0, 1, 28, 25, 25, 14.0, 81.00, 90.00, '2026-05-14 08:54:38'),
(40, 'DEANDRA AL AZHAR RAMADHANI', 'Tidak Lulus', 71.00, 77.00, 172.0, 60.0, 0, 23, 26, 23, 12.0, 72.00, 76.00, '2026-05-14 08:54:38'),
(41, 'DEFKA TURIYADI', 'Tidak Lulus', 76.00, 74.00, 172.0, 67.0, 1, 25, 24, 25, 12.0, 72.00, 77.00, '2026-05-14 08:54:38'),
(42, 'DEFRI AJI ALAMSYAH', 'Tidak Lulus', 74.00, 75.00, 171.0, 63.0, 0, 26, 26, 24, 11.0, 77.00, 77.00, '2026-05-14 08:54:38'),
(43, 'DELLA ANGGRAENI', 'Tidak Lulus', 70.00, 78.00, 170.0, 58.0, 1, 24, 26, 26, 12.0, 72.00, 73.00, '2026-05-14 08:54:38'),
(44, 'DEWI LESTARI', 'Tidak Lulus', 77.00, 70.00, 166.0, 51.0, 1, 25, 24, 24, 12.0, 71.00, 73.00, '2026-05-14 08:54:38'),
(45, 'DIANA PUTRI', 'Tidak Lulus', 73.00, 73.00, 172.0, 67.0, 1, 26, 25, 24, 10.0, 75.00, 71.00, '2026-05-14 08:54:38'),
(46, 'DIKA VERNANDA', 'Tidak Lulus', 73.00, 78.00, 181.0, 71.0, 1, 25, 23, 26, 11.0, 78.00, 74.00, '2026-05-14 08:54:38'),
(47, 'DZIKRI AGUS RAMDANI', 'Lulus', 95.00, 88.00, 170.0, 56.0, 1, 28, 29, 29, 12.0, 89.00, 91.00, '2026-05-14 08:54:38'),
(48, 'FAHRI IRSYAD A.', 'Tidak Lulus', 74.00, 73.00, 174.0, 62.0, 0, 24, 23, 26, 12.0, 72.00, 71.00, '2026-05-14 08:54:38'),
(49, 'FAJAR WIRYANTO', 'Tidak Lulus', 76.00, 72.00, 179.0, 71.0, 1, 25, 25, 23, 10.0, 71.00, 77.00, '2026-05-14 08:54:38'),
(50, 'FAJI SAPTA RAMADHAN', 'Tidak Lulus', 77.00, 70.00, 173.0, 62.0, 1, 26, 23, 25, 11.0, 71.00, 73.00, '2026-05-14 08:54:38'),
(51, 'FATAHILAH', 'Tidak Lulus', 76.00, 73.00, 170.0, 59.0, 0, 26, 26, 25, 12.0, 77.00, 70.00, '2026-05-14 08:54:38'),
(52, 'FATIMAH AZZAHRA', 'Tidak Lulus', 74.00, 73.00, 171.0, 61.0, 1, 24, 26, 23, 11.0, 75.00, 76.00, '2026-05-14 08:54:38'),
(53, 'FERI YANSAH', 'Tidak Lulus', 76.00, 74.00, 175.0, 60.0, 0, 23, 23, 23, 12.0, 78.00, 76.00, '2026-05-14 08:54:38'),
(54, 'FIRLIZA AULIA ARIFIN PUTRI', 'Lulus', 82.00, 84.00, 169.0, 60.0, 1, 27, 30, 26, 13.0, 93.00, 93.00, '2026-05-14 08:54:38'),
(55, 'FITRIANI RAHMA', 'Tidak Lulus', 73.00, 70.00, 169.0, 56.0, 1, 24, 24, 24, 12.0, 73.00, 73.00, '2026-05-14 08:54:38'),
(56, 'GALANG PUTRA HERLIANO', 'Tidak Lulus', 73.00, 74.00, 170.0, 64.0, 0, 25, 26, 23, 12.0, 72.00, 78.00, '2026-05-14 08:54:38'),
(57, 'GHAZI HAIDAR HARA', 'Lulus', 86.00, 94.00, 176.0, 66.0, 1, 27, 30, 28, 13.0, 92.00, 95.00, '2026-05-14 08:54:38'),
(58, 'GHINI MALINI KUSUMAH', 'Lulus', 90.00, 89.00, 170.0, 62.0, 1, 28, 26, 29, 13.0, 92.00, 89.00, '2026-05-14 08:54:38'),
(59, 'GIGA BELFAST ELFAREL', 'Lulus', 92.00, 83.00, 174.0, 59.0, 1, 26, 28, 26, 12.0, 94.00, 95.00, '2026-05-14 08:54:38'),
(60, 'HANA DIYANDA ZEN', 'Lulus', 89.00, 87.00, 172.0, 64.0, 1, 30, 29, 28, 13.0, 81.00, 82.00, '2026-05-14 08:54:38'),
(61, 'HARIS DWI FIRMANSYAH', 'Lulus', 90.00, 90.00, 174.0, 59.0, 1, 26, 26, 29, 12.0, 95.00, 82.00, '2026-05-14 08:54:38'),
(62, 'HERDI', 'Lulus', 93.00, 86.00, 175.0, 63.0, 1, 25, 25, 30, 14.0, 93.00, 81.00, '2026-05-14 08:54:38'),
(63, 'HERDIANSYAH', 'Tidak Lulus', 74.00, 75.00, 172.0, 67.0, 0, 25, 26, 25, 11.0, 77.00, 72.00, '2026-05-14 08:54:38'),
(64, 'HOERUL AJAM', 'Tidak Lulus', 78.00, 73.00, 174.0, 62.0, 1, 23, 24, 25, 11.0, 71.00, 75.00, '2026-05-14 08:54:38'),
(65, 'IBRAHIM EMBISA', 'Tidak Lulus', 70.00, 71.00, 170.0, 57.0, 0, 25, 26, 25, 12.0, 71.00, 78.00, '2026-05-14 08:54:38'),
(66, 'ILHAM', 'Tidak Lulus', 74.00, 73.00, 173.0, 61.0, 1, 24, 25, 23, 11.0, 78.00, 77.00, '2026-05-14 08:54:38'),
(67, 'IMAN', 'Lulus', 90.00, 86.00, 170.0, 64.0, 1, 29, 28, 30, 13.0, 85.00, 84.00, '2026-05-14 08:54:38'),
(68, 'INA APRIL LIA', 'Lulus', 89.00, 94.00, 168.0, 56.0, 1, 28, 30, 29, 13.0, 90.00, 88.00, '2026-05-14 08:54:38'),
(69, 'INTAN LESTARI', 'Tidak Lulus', 72.00, 73.00, 167.0, 52.0, 1, 23, 24, 25, 10.0, 78.00, 71.00, '2026-05-14 08:54:38'),
(70, 'INTAN PERMATASARI', 'Tidak Lulus', 74.00, 71.00, 169.0, 62.0, 1, 24, 23, 23, 11.0, 73.00, 78.00, '2026-05-14 08:54:38'),
(71, 'JUNI SUPANDI', 'Tidak Lulus', 77.00, 70.00, 180.0, 75.0, 0, 23, 26, 26, 11.0, 73.00, 70.00, '2026-05-14 08:54:38'),
(72, 'KAYLA ZAIRA RAHMAN', 'Lulus', 90.00, 84.00, 168.0, 62.0, 1, 29, 28, 25, 13.0, 92.00, 84.00, '2026-05-14 08:54:38'),
(73, 'KAYSEIVO NAUFAL AZIZ', 'Lulus', 95.00, 84.00, 171.0, 62.0, 1, 29, 26, 29, 13.0, 85.00, 94.00, '2026-05-14 08:54:38'),
(74, 'KHAISAN AHHWAZ DHIYAULHAQ', 'Tidak Lulus', 70.00, 75.00, 172.0, 66.0, 1, 25, 25, 24, 12.0, 70.00, 78.00, '2026-05-14 08:54:38'),
(75, 'KHALFANI BUDIANA', 'Lulus', 83.00, 95.00, 173.0, 60.0, 1, 27, 29, 27, 12.0, 83.00, 95.00, '2026-05-14 08:54:38'),
(76, 'MARSHA BERINDA MUTIA', 'Lulus', 93.00, 91.00, 173.0, 63.0, 1, 30, 29, 26, 14.0, 91.00, 92.00, '2026-05-14 08:54:38'),
(77, 'MAS FARREL MUHAMAD FAHREZ', 'Lulus', 92.00, 85.00, 176.0, 67.0, 1, 28, 26, 27, 14.0, 83.00, 84.00, '2026-05-14 08:54:38'),
(78, 'MAULANA YUSUP', 'Lulus', 87.00, 85.00, 173.0, 63.0, 1, 30, 28, 28, 14.0, 92.00, 89.00, '2026-05-14 08:54:38'),
(79, 'MEIZHALIA PUTRI ZHASQIA', 'Lulus', 87.00, 83.00, 166.0, 57.0, 1, 25, 27, 29, 14.0, 90.00, 93.00, '2026-05-14 08:54:38'),
(80, 'MIFTAHUDIN', 'Lulus', 90.00, 88.00, 175.0, 63.0, 1, 28, 27, 26, 14.0, 82.00, 94.00, '2026-05-14 08:54:38'),
(81, 'MIMIN', 'Lulus', 88.00, 85.00, 170.0, 63.0, 1, 29, 30, 28, 13.0, 92.00, 81.00, '2026-05-14 08:54:38'),
(82, 'MUHAMAD ADIV AWALUDIN', 'Lulus', 91.00, 90.00, 170.0, 60.0, 1, 27, 29, 26, 14.0, 92.00, 82.00, '2026-05-14 08:54:38'),
(83, 'MUHAMAD AL HAFIDZI', 'Tidak Lulus', 75.00, 76.00, 174.0, 68.0, 0, 24, 23, 23, 11.0, 75.00, 71.00, '2026-05-14 08:54:38'),
(84, 'MUHAMAD DONA RIZKI', 'Tidak Lulus', 71.00, 72.00, 170.0, 59.0, 1, 24, 24, 23, 11.0, 74.00, 78.00, '2026-05-14 08:54:38'),
(85, 'MUHAMAD FAUZI', 'Tidak Lulus', 76.00, 72.00, 178.0, 69.0, 0, 26, 25, 25, 12.0, 70.00, 72.00, '2026-05-14 08:54:38'),
(86, 'MUHAMAD GILMAN', 'Tidak Lulus', 74.00, 75.00, 171.0, 57.0, 1, 24, 24, 25, 11.0, 73.00, 71.00, '2026-05-14 08:54:38'),
(87, 'MUHAMAD JENI', 'Tidak Lulus', 76.00, 77.00, 174.0, 68.0, 0, 24, 25, 23, 10.0, 76.00, 78.00, '2026-05-14 08:54:38'),
(88, 'MUHAMAD NAZWA FAITUROHMAN', 'Lulus', 95.00, 94.00, 172.0, 62.0, 1, 25, 28, 30, 12.0, 93.00, 85.00, '2026-05-14 08:54:38'),
(89, 'MUHAMAD RASYA YUSUP', 'Tidak Lulus', 72.00, 75.00, 175.0, 66.0, 1, 23, 23, 25, 12.0, 76.00, 76.00, '2026-05-14 08:54:38'),
(90, 'MUHAMAD RIZQI AL PIANSYAH', 'Tidak Lulus', 75.00, 71.00, 171.0, 66.0, 1, 26, 25, 25, 10.0, 75.00, 72.00, '2026-05-14 08:54:38'),
(91, 'MUHAMAD SURIYANA', 'Tidak Lulus', 74.00, 73.00, 173.0, 64.0, 1, 24, 23, 23, 11.0, 77.00, 74.00, '2026-05-14 08:54:38'),
(92, 'MUHAMMAD AL DEZAN', 'Tidak Lulus', 76.00, 72.00, 173.0, 66.0, 1, 24, 23, 26, 12.0, 72.00, 75.00, '2026-05-14 08:54:38'),
(93, 'MUHAMMAD AZIZUL HAKIM', 'Tidak Lulus', 76.00, 72.00, 170.0, 63.0, 1, 26, 25, 26, 11.0, 72.00, 73.00, '2026-05-14 08:54:38'),
(94, 'MUHAMMAD AZKA IBNUSSYFA', 'Lulus', 93.00, 81.00, 177.0, 69.0, 1, 29, 28, 25, 13.0, 94.00, 89.00, '2026-05-14 08:54:38'),
(95, 'MUHAMMAD DAFFA NAUFAL ANNADHIEF', 'Tidak Lulus', 78.00, 77.00, 173.0, 67.0, 0, 26, 26, 24, 11.0, 75.00, 78.00, '2026-05-14 08:54:38'),
(96, 'MUHAMMAD GALIH AL-FARIZI', 'Lulus', 89.00, 82.00, 170.0, 58.0, 1, 26, 30, 25, 13.0, 91.00, 91.00, '2026-05-14 08:54:38'),
(97, 'MUHAMMAD IKHSAN RAMBE', 'Tidak Lulus', 78.00, 72.00, 174.0, 61.0, 1, 25, 24, 25, 10.0, 72.00, 77.00, '2026-05-14 08:54:38'),
(98, 'MUHAMMAD NAFIL ATHA\'ILAH', 'Lulus', 94.00, 86.00, 176.0, 65.0, 1, 30, 26, 25, 13.0, 89.00, 83.00, '2026-05-14 08:54:38'),
(99, 'MUHAMMAD RIZKI HERAWAN', 'Tidak Lulus', 76.00, 75.00, 170.0, 56.0, 0, 24, 24, 26, 11.0, 78.00, 70.00, '2026-05-14 08:54:38'),
(100, 'MUHAMMAD WILDAN TAFTAZANI', 'Lulus', 89.00, 95.00, 170.0, 64.0, 1, 30, 27, 28, 14.0, 93.00, 93.00, '2026-05-14 08:54:38'),
(101, 'MUHAMMAD YUSUF.PIRDAUS', 'Tidak Lulus', 78.00, 70.00, 171.0, 57.0, 1, 25, 23, 26, 11.0, 72.00, 70.00, '2026-05-14 08:54:38'),
(102, 'MUHHAMAD ASRI IRAWAN', 'Tidak Lulus', 77.00, 77.00, 170.0, 60.0, 1, 26, 26, 24, 12.0, 70.00, 70.00, '2026-05-14 08:54:38'),
(103, 'NABILLA AZZAHRA', 'Tidak Lulus', 73.00, 73.00, 168.0, 58.0, 1, 23, 23, 23, 10.0, 75.00, 77.00, '2026-05-14 08:54:38'),
(104, 'NADZHIF ADLY ABBIATI', 'Tidak Lulus', 70.00, 76.00, 174.0, 69.0, 1, 24, 25, 23, 10.0, 78.00, 77.00, '2026-05-14 08:54:38'),
(105, 'NOVAL ADRIANSYAH', 'Tidak Lulus', 77.00, 73.00, 170.0, 60.0, 0, 26, 24, 25, 11.0, 77.00, 77.00, '2026-05-14 08:54:38'),
(106, 'NUR AFIFAH', 'Tidak Lulus', 74.00, 70.00, 171.0, 66.0, 1, 25, 23, 25, 11.0, 76.00, 71.00, '2026-05-14 08:54:38'),
(107, 'NURMAAD', 'Tidak Lulus', 74.00, 77.00, 175.0, 67.0, 1, 26, 26, 23, 11.0, 78.00, 74.00, '2026-05-14 08:54:38'),
(108, 'NURUL AULIA', 'Tidak Lulus', 71.00, 75.00, 172.0, 63.0, 1, 24, 23, 26, 11.0, 78.00, 72.00, '2026-05-14 08:54:38'),
(109, 'NURUL HIDAYAH', 'Tidak Lulus', 72.00, 72.00, 172.0, 58.0, 1, 23, 24, 26, 12.0, 74.00, 76.00, '2026-05-14 08:54:38'),
(110, 'OPICK FERDIANSYAH', 'Lulus', 90.00, 88.00, 172.0, 65.0, 1, 30, 28, 28, 12.0, 87.00, 82.00, '2026-05-14 08:54:38'),
(111, 'PADLAN FAHRUROZI', 'Tidak Lulus', 77.00, 73.00, 171.0, 56.0, 1, 26, 25, 25, 11.0, 71.00, 73.00, '2026-05-14 08:54:38'),
(112, 'PAJRI JULIYANTO', 'Tidak Lulus', 74.00, 71.00, 172.0, 57.0, 0, 26, 26, 26, 11.0, 72.00, 78.00, '2026-05-14 08:54:38'),
(113, 'PESAL MAULANA', 'Lulus', 95.00, 85.00, 175.0, 64.0, 1, 28, 28, 29, 14.0, 89.00, 85.00, '2026-05-14 08:54:38'),
(114, 'PUTRI AYU LESTARI', 'Tidak Lulus', 75.00, 72.00, 167.0, 60.0, 1, 24, 25, 26, 10.0, 76.00, 70.00, '2026-05-14 08:54:38'),
(115, 'PUTRI MAHARANI', 'Tidak Lulus', 75.00, 74.00, 166.0, 57.0, 1, 24, 25, 26, 12.0, 78.00, 77.00, '2026-05-14 08:54:38'),
(116, 'RAFLY FIADRI LATHIF', 'Tidak Lulus', 70.00, 77.00, 176.0, 70.0, 0, 24, 26, 24, 11.0, 77.00, 73.00, '2026-05-14 08:54:38'),
(117, 'RAISSA AURELLA', 'Lulus', 83.00, 85.00, 165.0, 59.0, 1, 27, 26, 26, 13.0, 86.00, 92.00, '2026-05-14 08:54:38'),
(118, 'RAMA KANZA PUTRA', 'Tidak Lulus', 78.00, 70.00, 173.0, 60.0, 0, 23, 26, 23, 11.0, 78.00, 70.00, '2026-05-14 08:54:38'),
(119, 'RAMDANI', 'Tidak Lulus', 76.00, 76.00, 171.0, 57.0, 0, 26, 23, 25, 10.0, 75.00, 76.00, '2026-05-14 08:54:38'),
(120, 'RANI WULANDARI', 'Tidak Lulus', 71.00, 74.00, 171.0, 65.0, 1, 23, 23, 25, 11.0, 75.00, 76.00, '2026-05-14 08:54:38'),
(121, 'RAPLI LUTFIANSAH SAMSUDIN', 'Tidak Lulus', 70.00, 77.00, 172.0, 61.0, 0, 25, 24, 24, 10.0, 72.00, 76.00, '2026-05-14 08:54:38'),
(122, 'RATU BILQIZ CAHAYA FADILAH', 'Lulus', 89.00, 81.00, 166.0, 56.0, 1, 28, 27, 27, 12.0, 93.00, 91.00, '2026-05-14 08:54:38'),
(123, 'RENI ADISSYA PUTRI', 'Lulus', 82.00, 93.00, 168.0, 59.0, 1, 25, 27, 26, 13.0, 93.00, 95.00, '2026-05-14 08:54:38'),
(124, 'RESA WAHYUDIN', 'Tidak Lulus', 76.00, 74.00, 176.0, 65.0, 1, 24, 25, 23, 11.0, 73.00, 73.00, '2026-05-14 08:54:38'),
(125, 'REZKI ADITIYA PRATAMA', 'Tidak Lulus', 75.00, 71.00, 170.0, 58.0, 0, 23, 23, 24, 12.0, 76.00, 70.00, '2026-05-14 08:54:38'),
(126, 'RIFALDI FAHMI NURFAUZI', 'Tidak Lulus', 77.00, 76.00, 176.0, 69.0, 0, 25, 25, 24, 10.0, 70.00, 74.00, '2026-05-14 08:54:38'),
(127, 'RIKA MONIKA', 'Lulus', 84.00, 89.00, 168.0, 53.0, 1, 26, 29, 25, 12.0, 92.00, 85.00, '2026-05-14 08:54:38'),
(128, 'RINA APRILIA', 'Tidak Lulus', 70.00, 77.00, 165.0, 50.0, 1, 24, 24, 26, 11.0, 76.00, 77.00, '2026-05-14 08:54:38'),
(129, 'RIZKA AMELIA', 'Tidak Lulus', 78.00, 70.00, 166.0, 55.0, 1, 24, 26, 25, 11.0, 73.00, 78.00, '2026-05-14 08:54:38'),
(130, 'RIZKI NUR AINI', 'Tidak Lulus', 72.00, 78.00, 168.0, 56.0, 1, 25, 23, 26, 12.0, 75.00, 77.00, '2026-05-14 08:54:38'),
(131, 'RIZKI RADITIA PERMANA', 'Tidak Lulus', 74.00, 70.00, 170.0, 61.0, 1, 23, 24, 24, 12.0, 73.00, 78.00, '2026-05-14 08:54:38'),
(132, 'ROBI YANSAH', 'Tidak Lulus', 70.00, 73.00, 171.0, 58.0, 0, 26, 23, 25, 10.0, 76.00, 73.00, '2026-05-14 08:54:38'),
(133, 'SAEPULLAH', 'Tidak Lulus', 75.00, 75.00, 180.0, 71.0, 0, 25, 23, 25, 11.0, 76.00, 73.00, '2026-05-14 08:54:38'),
(134, 'SAHRUL HIDAYAT', 'Tidak Lulus', 75.00, 70.00, 171.0, 63.0, 0, 23, 24, 23, 10.0, 78.00, 73.00, '2026-05-14 08:54:38'),
(135, 'SALSABILA', 'Lulus', 91.00, 84.00, 170.0, 61.0, 1, 29, 25, 25, 12.0, 85.00, 83.00, '2026-05-14 08:54:38'),
(136, 'SATRIA RENALDY', 'Tidak Lulus', 74.00, 70.00, 174.0, 69.0, 0, 25, 26, 24, 12.0, 75.00, 72.00, '2026-05-14 08:54:38'),
(137, 'SHELMA AGUSTINA RHAMADANI', 'Lulus', 89.00, 95.00, 170.0, 59.0, 1, 25, 26, 29, 13.0, 90.00, 93.00, '2026-05-14 08:54:39'),
(138, 'SIDIK MULYA', 'Tidak Lulus', 78.00, 77.00, 178.0, 63.0, 0, 25, 24, 26, 11.0, 72.00, 78.00, '2026-05-14 08:54:39'),
(139, 'SITI RAHMA', 'Tidak Lulus', 70.00, 71.00, 169.0, 58.0, 1, 26, 25, 23, 11.0, 76.00, 78.00, '2026-05-14 08:54:39'),
(140, 'SITTI NURAENI', 'Lulus', 94.00, 94.00, 166.0, 51.0, 1, 25, 28, 28, 13.0, 87.00, 84.00, '2026-05-14 08:54:39'),
(141, 'SRI RAHAYU', 'Lulus', 81.00, 92.00, 170.0, 63.0, 1, 27, 25, 29, 13.0, 82.00, 91.00, '2026-05-14 08:54:39'),
(142, 'STEVEN NATHANAEL FREDRIK WAWOLANGI', 'Tidak Lulus', 73.00, 71.00, 171.0, 62.0, 0, 24, 23, 23, 10.0, 70.00, 72.00, '2026-05-14 08:54:39'),
(143, 'SYIFA NURJANAH', 'Tidak Lulus', 76.00, 75.00, 165.0, 55.0, 1, 24, 25, 23, 12.0, 76.00, 70.00, '2026-05-14 08:54:39'),
(144, 'TATA NURUL FAUZAH', 'Lulus', 92.00, 86.00, 170.0, 59.0, 1, 28, 30, 28, 14.0, 87.00, 84.00, '2026-05-14 08:54:39'),
(145, 'TB NAUFAL NUR AGUNG', 'Tidak Lulus', 78.00, 73.00, 170.0, 63.0, 0, 26, 25, 26, 11.0, 73.00, 77.00, '2026-05-14 08:54:39'),
(146, 'TEGAR SAWALUDIN', 'Tidak Lulus', 78.00, 71.00, 173.0, 64.0, 0, 25, 23, 25, 10.0, 75.00, 76.00, '2026-05-14 08:54:39'),
(147, 'TIARA PUTRI ANANDA', 'Tidak Lulus', 78.00, 73.00, 166.0, 52.0, 1, 23, 24, 26, 10.0, 70.00, 72.00, '2026-05-14 08:54:39'),
(148, 'TRIA SEPTI FAJARITA RAMADANI', 'Lulus', 85.00, 91.00, 170.0, 56.0, 1, 28, 26, 29, 14.0, 93.00, 85.00, '2026-05-14 08:54:39'),
(149, 'WILDAN SEZAR PAKUILA', 'Tidak Lulus', 78.00, 78.00, 175.0, 67.0, 1, 25, 25, 23, 10.0, 70.00, 75.00, '2026-05-14 08:54:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `hasil_knn`
--

CREATE TABLE `hasil_knn` (
  `id` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  `status_kelulusan` enum('Lulus','Tidak Lulus') NOT NULL,
  `skor_knn` double DEFAULT NULL,
  `k_value` tinyint(4) NOT NULL DEFAULT 3,
  `vote_lulus` tinyint(4) NOT NULL DEFAULT 0,
  `vote_tidak_lulus` tinyint(4) NOT NULL DEFAULT 0,
  `keterangan_kuota` varchar(255) DEFAULT NULL,
  `detail_knn` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail_knn`)),
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `hasil_knn`
--

INSERT INTO `hasil_knn` (`id`, `id_peserta`, `status_kelulusan`, `skor_knn`, `k_value`, `vote_lulus`, `vote_tidak_lulus`, `keterangan_kuota`, `detail_knn`, `processed_by`, `processed_at`) VALUES
(254, 1, 'Tidak Lulus', 0.38607921, 3, 0, 3, NULL, '[{\"id_latih\":45,\"nama_referensi\":\"DIANA PUTRI\",\"jarak\":0.3860792107591902,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":45,\"nama_referensi\":\"DIANA PUTRI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"73.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"67.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":24,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.12,0.12,0.4117647058823529,0.68,1,0.42857142857142855,0.2857142857142857,0.14285714285714285,0,0.2,0.04],\"rank\":1},{\"id_latih\":36,\"nama_referensi\":\"ARYA FIRMAN\",\"jarak\":0.3881457934596731,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":36,\"nama_referensi\":\"ARYA FIRMAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"64.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":25,\"back_up\":23,\"lari\":\"10.0\",\"pbb\":\"73.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0,0.04,0.4117647058823529,0.56,1,0.2857142857142857,0.2857142857142857,0,0,0.12,0.08],\"rank\":2},{\"id_latih\":90,\"nama_referensi\":\"MUHAMAD RIZQI AL PIANSYAH\",\"jarak\":0.4471029232055941,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":90,\"nama_referensi\":\"MUHAMAD RIZQI AL PIANSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"66.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0.04,0.35294117647058826,0.64,1,0.42857142857142855,0.2857142857142857,0.2857142857142857,0,0.2,0.08],\"rank\":3}]', 1, '2026-05-18 07:02:31'),
(255, 2, 'Tidak Lulus', 0.29926275, 3, 0, 3, NULL, '[{\"id_latih\":93,\"nama_referensi\":\"MUHAMMAD AZIZUL HAKIM\",\"jarak\":0.29926275251478085,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":93,\"nama_referensi\":\"MUHAMMAD AZIZUL HAKIM\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"63.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":26,\"lari\":\"11.0\",\"pbb\":\"72.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.08,0.29411764705882354,0.52,1,0.42857142857142855,0.2857142857142857,0.42857142857142855,0.25,0.08,0.12],\"rank\":1},{\"id_latih\":50,\"nama_referensi\":\"FAJI SAPTA RAMADHAN\",\"jarak\":0.320662579350816,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":50,\"nama_referensi\":\"FAJI SAPTA RAMADHAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"77.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":23,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"71.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.28,0,0.47058823529411764,0.48,1,0.42857142857142855,0,0.2857142857142857,0.25,0.04,0.12],\"rank\":2},{\"id_latih\":92,\"nama_referensi\":\"MUHAMMAD AL DEZAN\",\"jarak\":0.33603024645203033,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":92,\"nama_referensi\":\"MUHAMMAD AL DEZAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"66.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":23,\"back_up\":26,\"lari\":\"12.0\",\"pbb\":\"72.00\",\"kepribadian\":\"75.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.08,0.47058823529411764,0.64,1,0.14285714285714285,0,0.42857142857142855,0.5,0.08,0.2],\"rank\":3}]', 1, '2026-05-18 07:02:31'),
(256, 3, 'Tidak Lulus', 0.29437588, 3, 0, 3, NULL, '[{\"id_latih\":25,\"nama_referensi\":\"ALIF BAROKAH\",\"jarak\":0.294375877035533,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":25,\"nama_referensi\":\"ALIF BAROKAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"78.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":23,\"back_up\":24,\"lari\":\"12.0\",\"pbb\":\"74.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.32,0.4117647058823529,0.48,1,0.2857142857142857,0,0.14285714285714285,0.5,0.16,0.24],\"rank\":1},{\"id_latih\":120,\"nama_referensi\":\"RANI WULANDARI\",\"jarak\":0.3199505756796866,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":120,\"nama_referensi\":\"RANI WULANDARI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"74.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"65.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":23,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.16,0.35294117647058826,0.6,1,0,0,0.2857142857142857,0.25,0.2,0.24],\"rank\":2},{\"id_latih\":89,\"nama_referensi\":\"MUHAMAD RASYA YUSUP\",\"jarak\":0.35059000781072586,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":89,\"nama_referensi\":\"MUHAMAD RASYA YUSUP\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"72.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"175.0\",\"berat_badan\":\"66.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":23,\"back_up\":25,\"lari\":\"12.0\",\"pbb\":\"76.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.08,0.2,0.5882352941176471,0.64,1,0,0,0.2857142857142857,0.5,0.24,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:31'),
(257, 4, 'Lulus', 0.4919277, 3, 3, 0, NULL, '[{\"id_latih\":54,\"nama_referensi\":\"FIRLIZA AULIA ARIFIN PUTRI\",\"jarak\":0.49192769862417907,\"label\":\"Lulus\",\"data_latih\":{\"id\":54,\"nama_referensi\":\"FIRLIZA AULIA ARIFIN PUTRI\",\"label\":\"Lulus\",\"nilai_pip\":\"82.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"169.0\",\"berat_badan\":\"60.0\",\"kesehatan\":1,\"push_up\":27,\"sit_up\":30,\"back_up\":26,\"lari\":\"13.0\",\"pbb\":\"93.00\",\"kepribadian\":\"93.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.48,0.56,0.23529411764705882,0.4,1,0.5714285714285714,1,0.42857142857142855,0.75,0.92,0.92],\"rank\":1},{\"id_latih\":76,\"nama_referensi\":\"MARSHA BERINDA MUTIA\",\"jarak\":0.6092275396292612,\"label\":\"Lulus\",\"data_latih\":{\"id\":76,\"nama_referensi\":\"MARSHA BERINDA MUTIA\",\"label\":\"Lulus\",\"nilai_pip\":\"93.00\",\"nilai_tiu\":\"91.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"63.0\",\"kesehatan\":1,\"push_up\":30,\"sit_up\":29,\"back_up\":26,\"lari\":\"14.0\",\"pbb\":\"91.00\",\"kepribadian\":\"92.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.92,0.84,0.47058823529411764,0.52,1,1,0.8571428571428571,0.42857142857142855,1,0.84,0.88],\"rank\":2},{\"id_latih\":78,\"nama_referensi\":\"MAULANA YUSUP\",\"jarak\":0.6587984045972378,\"label\":\"Lulus\",\"data_latih\":{\"id\":78,\"nama_referensi\":\"MAULANA YUSUP\",\"label\":\"Lulus\",\"nilai_pip\":\"87.00\",\"nilai_tiu\":\"85.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"63.0\",\"kesehatan\":1,\"push_up\":30,\"sit_up\":28,\"back_up\":28,\"lari\":\"14.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.68,0.6,0.47058823529411764,0.52,1,1,0.7142857142857143,0.7142857142857143,1,0.88,0.76],\"rank\":3}]', 1, '2026-05-18 07:02:31'),
(258, 5, 'Tidak Lulus', 0.34036597, 3, 0, 3, NULL, '[{\"id_latih\":63,\"nama_referensi\":\"HERDIANSYAH\",\"jarak\":0.3403659702660257,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":63,\"nama_referensi\":\"HERDIANSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"67.0\",\"kesehatan\":0,\"push_up\":25,\"sit_up\":26,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.4117647058823529,0.68,0,0.2857142857142857,0.42857142857142855,0.2857142857142857,0.25,0.28,0.08],\"rank\":1},{\"id_latih\":42,\"nama_referensi\":\"DEFRI AJI ALAMSYAH\",\"jarak\":0.38768676007698194,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":42,\"nama_referensi\":\"DEFRI AJI ALAMSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"63.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":26,\"back_up\":24,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.35294117647058826,0.52,0,0.42857142857142855,0.42857142857142855,0.14285714285714285,0.25,0.28,0.28],\"rank\":2},{\"id_latih\":145,\"nama_referensi\":\"TB NAUFAL NUR AGUNG\",\"jarak\":0.40300908988583894,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":145,\"nama_referensi\":\"TB NAUFAL NUR AGUNG\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"63.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":25,\"back_up\":26,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.32,0.12,0.29411764705882354,0.52,0,0.42857142857142855,0.2857142857142857,0.42857142857142855,0.25,0.12,0.28],\"rank\":3}]', 1, '2026-05-18 07:02:31'),
(259, 6, 'Tidak Lulus', 0.41042665, 3, 0, 3, NULL, '[{\"id_latih\":132,\"nama_referensi\":\"ROBI YANSAH\",\"jarak\":0.41042664603728746,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":132,\"nama_referensi\":\"ROBI YANSAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"58.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":23,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"76.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0,0.12,0.35294117647058826,0.32,0,0.42857142857142855,0,0.2857142857142857,0,0.24,0.12],\"rank\":1},{\"id_latih\":105,\"nama_referensi\":\"NOVAL ADRIANSYAH\",\"jarak\":0.4456227646001943,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":105,\"nama_referensi\":\"NOVAL ADRIANSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"77.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"60.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":24,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.28,0.12,0.29411764705882354,0.4,0,0.42857142857142855,0.14285714285714285,0.2857142857142857,0.25,0.28,0.28],\"rank\":2},{\"id_latih\":119,\"nama_referensi\":\"RAMDANI\",\"jarak\":0.46179002996753493,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":119,\"nama_referensi\":\"RAMDANI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"76.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"57.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":23,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.24,0.35294117647058826,0.28,0,0.42857142857142855,0,0.2857142857142857,0,0.2,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:31'),
(260, 7, 'Tidak Lulus', 0.40776019, 3, 0, 3, NULL, '[{\"id_latih\":38,\"nama_referensi\":\"BINTANG SYAHRU RAMADHAN\",\"jarak\":0.4077601879509118,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":38,\"nama_referensi\":\"BINTANG SYAHRU RAMADHAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"175.0\",\"berat_badan\":\"63.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":25,\"back_up\":23,\"lari\":\"12.0\",\"pbb\":\"76.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0,0.5882352941176471,0.52,1,0,0.2857142857142857,0,0.5,0.24,0.28],\"rank\":1},{\"id_latih\":124,\"nama_referensi\":\"RESA WAHYUDIN\",\"jarak\":0.430018983918864,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":124,\"nama_referensi\":\"RESA WAHYUDIN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"74.00\",\"tinggi_badan\":\"176.0\",\"berat_badan\":\"65.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":25,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.16,0.6470588235294118,0.6,1,0.14285714285714285,0.2857142857142857,0,0.25,0.12,0.12],\"rank\":2},{\"id_latih\":66,\"nama_referensi\":\"ILHAM\",\"jarak\":0.49805440972119003,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":66,\"nama_referensi\":\"ILHAM\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":25,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"78.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.47058823529411764,0.44,1,0.14285714285714285,0.2857142857142857,0,0.25,0.32,0.28],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(261, 8, 'Lulus', 0.58378166, 3, 3, 0, NULL, '[{\"id_latih\":61,\"nama_referensi\":\"HARIS DWI FIRMANSYAH\",\"jarak\":0.5837816577616904,\"label\":\"Lulus\",\"data_latih\":{\"id\":61,\"nama_referensi\":\"HARIS DWI FIRMANSYAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"90.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":26,\"back_up\":29,\"lari\":\"12.0\",\"pbb\":\"95.00\",\"kepribadian\":\"82.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.8,0.5294117647058824,0.36,1,0.42857142857142855,0.42857142857142855,0.8571428571428571,0.5,1,0.48],\"rank\":1},{\"id_latih\":110,\"nama_referensi\":\"OPICK FERDIANSYAH\",\"jarak\":0.6576155159332314,\"label\":\"Lulus\",\"data_latih\":{\"id\":110,\"nama_referensi\":\"OPICK FERDIANSYAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"88.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"65.0\",\"kesehatan\":1,\"push_up\":30,\"sit_up\":28,\"back_up\":28,\"lari\":\"12.0\",\"pbb\":\"87.00\",\"kepribadian\":\"82.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.72,0.4117647058823529,0.6,1,1,0.7142857142857143,0.7142857142857143,0.5,0.68,0.48],\"rank\":2},{\"id_latih\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"jarak\":0.6817257746171967,\"label\":\"Lulus\",\"data_latih\":{\"id\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.76,0.29411764705882354,0.48,1,0.7142857142857143,0.42857142857142855,0.8571428571428571,0.75,0.88,0.76],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(262, 9, 'Lulus', 0.63949525, 3, 3, 0, NULL, '[{\"id_latih\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"jarak\":0.6394952461928511,\"label\":\"Lulus\",\"data_latih\":{\"id\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.76,0.29411764705882354,0.48,1,0.7142857142857143,0.42857142857142855,0.8571428571428571,0.75,0.88,0.76],\"rank\":1},{\"id_latih\":73,\"nama_referensi\":\"KAYSEIVO NAUFAL AZIZ\",\"jarak\":0.643279325312864,\"label\":\"Lulus\",\"data_latih\":{\"id\":73,\"nama_referensi\":\"KAYSEIVO NAUFAL AZIZ\",\"label\":\"Lulus\",\"nilai_pip\":\"95.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"85.00\",\"kepribadian\":\"94.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[1,0.56,0.35294117647058826,0.48,1,0.8571428571428571,0.42857142857142855,0.8571428571428571,0.75,0.6,0.96],\"rank\":2},{\"id_latih\":47,\"nama_referensi\":\"DZIKRI AGUS RAMDANI\",\"jarak\":0.6448865479285281,\"label\":\"Lulus\",\"data_latih\":{\"id\":47,\"nama_referensi\":\"DZIKRI AGUS RAMDANI\",\"label\":\"Lulus\",\"nilai_pip\":\"95.00\",\"nilai_tiu\":\"88.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":29,\"back_up\":29,\"lari\":\"12.0\",\"pbb\":\"89.00\",\"kepribadian\":\"91.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[1,0.72,0.29411764705882354,0.24,1,0.7142857142857143,0.8571428571428571,0.8571428571428571,0.5,0.76,0.84],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(263, 10, 'Lulus', 0.40047807, 3, 3, 0, NULL, '[{\"id_latih\":135,\"nama_referensi\":\"SALSABILA\",\"jarak\":0.4004780703592162,\"label\":\"Lulus\",\"data_latih\":{\"id\":135,\"nama_referensi\":\"SALSABILA\",\"label\":\"Lulus\",\"nilai_pip\":\"91.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":25,\"back_up\":25,\"lari\":\"12.0\",\"pbb\":\"85.00\",\"kepribadian\":\"83.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.84,0.56,0.29411764705882354,0.44,1,0.8571428571428571,0.2857142857142857,0.2857142857142857,0.5,0.6,0.52],\"rank\":1},{\"id_latih\":98,\"nama_referensi\":\"MUHAMMAD NAFIL ATHA\'ILAH\",\"jarak\":0.5280934432831385,\"label\":\"Lulus\",\"data_latih\":{\"id\":98,\"nama_referensi\":\"MUHAMMAD NAFIL ATHA\'ILAH\",\"label\":\"Lulus\",\"nilai_pip\":\"94.00\",\"nilai_tiu\":\"86.00\",\"tinggi_badan\":\"176.0\",\"berat_badan\":\"65.0\",\"kesehatan\":1,\"push_up\":30,\"sit_up\":26,\"back_up\":25,\"lari\":\"13.0\",\"pbb\":\"89.00\",\"kepribadian\":\"83.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.96,0.64,0.6470588235294118,0.6,1,1,0.42857142857142855,0.2857142857142857,0.75,0.76,0.52],\"rank\":2},{\"id_latih\":72,\"nama_referensi\":\"KAYLA ZAIRA RAHMAN\",\"jarak\":0.5955010636783371,\"label\":\"Lulus\",\"data_latih\":{\"id\":72,\"nama_referensi\":\"KAYLA ZAIRA RAHMAN\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"168.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":28,\"back_up\":25,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"84.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.56,0.17647058823529413,0.48,1,0.8571428571428571,0.7142857142857143,0.2857142857142857,0.75,0.88,0.56],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(264, 11, 'Tidak Lulus', 0.35081102, 3, 0, 3, NULL, '[{\"id_latih\":139,\"nama_referensi\":\"SITI RAHMA\",\"jarak\":0.3508110187519241,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":139,\"nama_referensi\":\"SITI RAHMA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"169.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"76.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0,0.04,0.23529411764705882,0.32,1,0.42857142857142855,0.2857142857142857,0,0.25,0.24,0.32],\"rank\":1},{\"id_latih\":84,\"nama_referensi\":\"MUHAMAD DONA RIZKI\",\"jarak\":0.3622604768136105,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":84,\"nama_referensi\":\"MUHAMAD DONA RIZKI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"74.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.08,0.29411764705882354,0.36,1,0.14285714285714285,0.14285714285714285,0,0.25,0.16,0.32],\"rank\":2},{\"id_latih\":25,\"nama_referensi\":\"ALIF BAROKAH\",\"jarak\":0.39187411275273765,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":25,\"nama_referensi\":\"ALIF BAROKAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"78.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":23,\"back_up\":24,\"lari\":\"12.0\",\"pbb\":\"74.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.32,0.4117647058823529,0.48,1,0.2857142857142857,0,0.14285714285714285,0.5,0.16,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(266, 13, 'Tidak Lulus', 0.47777974, 3, 0, 3, NULL, '[{\"id_latih\":116,\"nama_referensi\":\"RAFLY FIADRI LATHIF\",\"jarak\":0.47777974372199106,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":116,\"nama_referensi\":\"RAFLY FIADRI LATHIF\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"77.00\",\"tinggi_badan\":\"176.0\",\"berat_badan\":\"70.0\",\"kesehatan\":0,\"push_up\":24,\"sit_up\":26,\"back_up\":24,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0,0.28,0.6470588235294118,0.8,0,0.14285714285714285,0.42857142857142855,0.14285714285714285,0.25,0.28,0.12],\"rank\":1},{\"id_latih\":63,\"nama_referensi\":\"HERDIANSYAH\",\"jarak\":0.4872204845642834,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":63,\"nama_referensi\":\"HERDIANSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"67.0\",\"kesehatan\":0,\"push_up\":25,\"sit_up\":26,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.4117647058823529,0.68,0,0.2857142857142857,0.42857142857142855,0.2857142857142857,0.25,0.28,0.08],\"rank\":2},{\"id_latih\":136,\"nama_referensi\":\"SATRIA RENALDY\",\"jarak\":0.4905828014358168,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":136,\"nama_referensi\":\"SATRIA RENALDY\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"69.0\",\"kesehatan\":0,\"push_up\":25,\"sit_up\":26,\"back_up\":24,\"lari\":\"12.0\",\"pbb\":\"75.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0,0.5294117647058824,0.76,0,0.2857142857142857,0.42857142857142855,0.14285714285714285,0.5,0.2,0.08],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(267, 14, 'Tidak Lulus', 0.30241884, 3, 0, 3, NULL, '[{\"id_latih\":55,\"nama_referensi\":\"FITRIANI RAHMA\",\"jarak\":0.3024188436265823,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":55,\"nama_referensi\":\"FITRIANI RAHMA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"73.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"169.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":24,\"lari\":\"12.0\",\"pbb\":\"73.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.12,0,0.23529411764705882,0.24,1,0.14285714285714285,0.14285714285714285,0.14285714285714285,0.5,0.12,0.12],\"rank\":1},{\"id_latih\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"jarak\":0.43236653007203346,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"57.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.35294117647058826,0.28,1,0.14285714285714285,0.14285714285714285,0.2857142857142857,0.25,0.12,0.04],\"rank\":2},{\"id_latih\":91,\"nama_referensi\":\"MUHAMAD SURIYANA\",\"jarak\":0.4412944940171212,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":91,\"nama_referensi\":\"MUHAMAD SURIYANA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"64.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":23,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"74.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.47058823529411764,0.56,1,0.14285714285714285,0,0,0.25,0.28,0.16],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(268, 15, 'Tidak Lulus', 0.31371377, 3, 0, 3, NULL, '[{\"id_latih\":105,\"nama_referensi\":\"NOVAL ADRIANSYAH\",\"jarak\":0.31371376528710415,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":105,\"nama_referensi\":\"NOVAL ADRIANSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"77.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"60.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":24,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.28,0.12,0.29411764705882354,0.4,0,0.42857142857142855,0.14285714285714285,0.2857142857142857,0.25,0.28,0.28],\"rank\":1},{\"id_latih\":134,\"nama_referensi\":\"SAHRUL HIDAYAT\",\"jarak\":0.4195247596256833,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":134,\"nama_referensi\":\"SAHRUL HIDAYAT\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"63.0\",\"kesehatan\":0,\"push_up\":23,\"sit_up\":24,\"back_up\":23,\"lari\":\"10.0\",\"pbb\":\"78.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0,0.35294117647058826,0.52,0,0,0.14285714285714285,0,0,0.32,0.12],\"rank\":2},{\"id_latih\":42,\"nama_referensi\":\"DEFRI AJI ALAMSYAH\",\"jarak\":0.42320328914008615,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":42,\"nama_referensi\":\"DEFRI AJI ALAMSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"63.0\",\"kesehatan\":0,\"push_up\":26,\"sit_up\":26,\"back_up\":24,\"lari\":\"11.0\",\"pbb\":\"77.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.35294117647058826,0.52,0,0.42857142857142855,0.42857142857142855,0.14285714285714285,0.25,0.28,0.28],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(269, 16, 'Lulus', 0.50470339, 3, 3, 0, NULL, '[{\"id_latih\":59,\"nama_referensi\":\"GIGA BELFAST ELFAREL\",\"jarak\":0.5047033918401043,\"label\":\"Lulus\",\"data_latih\":{\"id\":59,\"nama_referensi\":\"GIGA BELFAST ELFAREL\",\"label\":\"Lulus\",\"nilai_pip\":\"92.00\",\"nilai_tiu\":\"83.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":28,\"back_up\":26,\"lari\":\"12.0\",\"pbb\":\"94.00\",\"kepribadian\":\"95.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.88,0.52,0.5294117647058824,0.36,1,0.42857142857142855,0.7142857142857143,0.42857142857142855,0.5,0.96,1],\"rank\":1},{\"id_latih\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"jarak\":0.5977585950029207,\"label\":\"Lulus\",\"data_latih\":{\"id\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.76,0.29411764705882354,0.48,1,0.7142857142857143,0.42857142857142855,0.8571428571428571,0.75,0.88,0.76],\"rank\":2},{\"id_latih\":94,\"nama_referensi\":\"MUHAMMAD AZKA IBNUSSYFA\",\"jarak\":0.6095180894699884,\"label\":\"Lulus\",\"data_latih\":{\"id\":94,\"nama_referensi\":\"MUHAMMAD AZKA IBNUSSYFA\",\"label\":\"Lulus\",\"nilai_pip\":\"93.00\",\"nilai_tiu\":\"81.00\",\"tinggi_badan\":\"177.0\",\"berat_badan\":\"69.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":28,\"back_up\":25,\"lari\":\"13.0\",\"pbb\":\"94.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.92,0.44,0.7058823529411765,0.76,1,0.8571428571428571,0.7142857142857143,0.2857142857142857,0.75,0.96,0.76],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(270, 17, 'Lulus', 0.51409125, 3, 3, 0, NULL, '[{\"id_latih\":61,\"nama_referensi\":\"HARIS DWI FIRMANSYAH\",\"jarak\":0.5140912468051404,\"label\":\"Lulus\",\"data_latih\":{\"id\":61,\"nama_referensi\":\"HARIS DWI FIRMANSYAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"90.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":26,\"back_up\":29,\"lari\":\"12.0\",\"pbb\":\"95.00\",\"kepribadian\":\"82.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.8,0.5294117647058824,0.36,1,0.42857142857142855,0.42857142857142855,0.8571428571428571,0.5,1,0.48],\"rank\":1},{\"id_latih\":127,\"nama_referensi\":\"RIKA MONIKA\",\"jarak\":0.5605682752235119,\"label\":\"Lulus\",\"data_latih\":{\"id\":127,\"nama_referensi\":\"RIKA MONIKA\",\"label\":\"Lulus\",\"nilai_pip\":\"84.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"168.0\",\"berat_badan\":\"53.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":29,\"back_up\":25,\"lari\":\"12.0\",\"pbb\":\"92.00\",\"kepribadian\":\"85.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.56,0.76,0.17647058823529413,0.12,1,0.42857142857142855,0.8571428571428571,0.2857142857142857,0.5,0.88,0.6],\"rank\":2},{\"id_latih\":122,\"nama_referensi\":\"RATU BILQIZ CAHAYA FADILAH\",\"jarak\":0.5804960269577639,\"label\":\"Lulus\",\"data_latih\":{\"id\":122,\"nama_referensi\":\"RATU BILQIZ CAHAYA FADILAH\",\"label\":\"Lulus\",\"nilai_pip\":\"89.00\",\"nilai_tiu\":\"81.00\",\"tinggi_badan\":\"166.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":27,\"back_up\":27,\"lari\":\"12.0\",\"pbb\":\"93.00\",\"kepribadian\":\"91.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.76,0.44,0.058823529411764705,0.24,1,0.7142857142857143,0.5714285714285714,0.5714285714285714,0.5,0.92,0.84],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(271, 18, 'Lulus', 0.36207875, 3, 3, 0, NULL, '[{\"id_latih\":78,\"nama_referensi\":\"MAULANA YUSUP\",\"jarak\":0.36207875378015114,\"label\":\"Lulus\",\"data_latih\":{\"id\":78,\"nama_referensi\":\"MAULANA YUSUP\",\"label\":\"Lulus\",\"nilai_pip\":\"87.00\",\"nilai_tiu\":\"85.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"63.0\",\"kesehatan\":1,\"push_up\":30,\"sit_up\":28,\"back_up\":28,\"lari\":\"14.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.68,0.6,0.47058823529411764,0.52,1,1,0.7142857142857143,0.7142857142857143,1,0.88,0.76],\"rank\":1},{\"id_latih\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"jarak\":0.5007728861580382,\"label\":\"Lulus\",\"data_latih\":{\"id\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.76,0.29411764705882354,0.48,1,0.7142857142857143,0.42857142857142855,0.8571428571428571,0.75,0.88,0.76],\"rank\":2},{\"id_latih\":113,\"nama_referensi\":\"PESAL MAULANA\",\"jarak\":0.5268224259768283,\"label\":\"Lulus\",\"data_latih\":{\"id\":113,\"nama_referensi\":\"PESAL MAULANA\",\"label\":\"Lulus\",\"nilai_pip\":\"95.00\",\"nilai_tiu\":\"85.00\",\"tinggi_badan\":\"175.0\",\"berat_badan\":\"64.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":28,\"back_up\":29,\"lari\":\"14.0\",\"pbb\":\"89.00\",\"kepribadian\":\"85.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[1,0.6,0.5882352941176471,0.56,1,0.7142857142857143,0.7142857142857143,0.8571428571428571,1,0.76,0.6],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(272, 19, 'Tidak Lulus', 0.36163038, 3, 0, 3, NULL, '[{\"id_latih\":14,\"nama_referensi\":\"ABDUL AJIS\",\"jarak\":0.361630383324008,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":14,\"nama_referensi\":\"ABDUL AJIS\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"176.0\",\"berat_badan\":\"68.0\",\"kesehatan\":0,\"push_up\":23,\"sit_up\":23,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"76.00\",\"kepribadian\":\"75.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.6470588235294118,0.72,0,0,0,0.2857142857142857,0,0.24,0.2],\"rank\":1},{\"id_latih\":83,\"nama_referensi\":\"MUHAMAD AL HAFIDZI\",\"jarak\":0.37993424664988656,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":83,\"nama_referensi\":\"MUHAMAD AL HAFIDZI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"76.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"68.0\",\"kesehatan\":0,\"push_up\":24,\"sit_up\":23,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"75.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0.24,0.5294117647058824,0.72,0,0.14285714285714285,0,0,0.25,0.2,0.04],\"rank\":2},{\"id_latih\":133,\"nama_referensi\":\"SAEPULLAH\",\"jarak\":0.3836120371452425,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":133,\"nama_referensi\":\"SAEPULLAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"180.0\",\"berat_badan\":\"71.0\",\"kesehatan\":0,\"push_up\":25,\"sit_up\":23,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"76.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0.2,0.8823529411764706,0.84,0,0.2857142857142857,0,0.2857142857142857,0.25,0.24,0.12],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(273, 20, 'Lulus', 0.49083228, 3, 3, 0, NULL, '[{\"id_latih\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"jarak\":0.4908322794301657,\"label\":\"Lulus\",\"data_latih\":{\"id\":58,\"nama_referensi\":\"GHINI MALINI KUSUMAH\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"89.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.76,0.29411764705882354,0.48,1,0.7142857142857143,0.42857142857142855,0.8571428571428571,0.75,0.88,0.76],\"rank\":1},{\"id_latih\":148,\"nama_referensi\":\"TRIA SEPTI FAJARITA RAMADANI\",\"jarak\":0.5051893966925792,\"label\":\"Lulus\",\"data_latih\":{\"id\":148,\"nama_referensi\":\"TRIA SEPTI FAJARITA RAMADANI\",\"label\":\"Lulus\",\"nilai_pip\":\"85.00\",\"nilai_tiu\":\"91.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":26,\"back_up\":29,\"lari\":\"14.0\",\"pbb\":\"93.00\",\"kepribadian\":\"85.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.6,0.84,0.29411764705882354,0.24,1,0.7142857142857143,0.42857142857142855,0.8571428571428571,1,0.92,0.6],\"rank\":2},{\"id_latih\":73,\"nama_referensi\":\"KAYSEIVO NAUFAL AZIZ\",\"jarak\":0.5789513774129699,\"label\":\"Lulus\",\"data_latih\":{\"id\":73,\"nama_referensi\":\"KAYSEIVO NAUFAL AZIZ\",\"label\":\"Lulus\",\"nilai_pip\":\"95.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":26,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"85.00\",\"kepribadian\":\"94.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[1,0.56,0.35294117647058826,0.48,1,0.8571428571428571,0.42857142857142855,0.8571428571428571,0.75,0.6,0.96],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(274, 21, 'Lulus', 0.53580285, 3, 3, 0, NULL, '[{\"id_latih\":34,\"nama_referensi\":\"ANNISA MIRA RAMADHANI\",\"jarak\":0.5358028531170538,\"label\":\"Lulus\",\"data_latih\":{\"id\":34,\"nama_referensi\":\"ANNISA MIRA RAMADHANI\",\"label\":\"Lulus\",\"nilai_pip\":\"95.00\",\"nilai_tiu\":\"89.00\",\"tinggi_badan\":\"166.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":26,\"back_up\":29,\"lari\":\"12.0\",\"pbb\":\"88.00\",\"kepribadian\":\"86.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[1,0.76,0.058823529411764705,0.32,1,0.42857142857142855,0.42857142857142855,0.8571428571428571,0.5,0.72,0.64],\"rank\":1},{\"id_latih\":140,\"nama_referensi\":\"SITTI NURAENI\",\"jarak\":0.6753026558515907,\"label\":\"Lulus\",\"data_latih\":{\"id\":140,\"nama_referensi\":\"SITTI NURAENI\",\"label\":\"Lulus\",\"nilai_pip\":\"94.00\",\"nilai_tiu\":\"94.00\",\"tinggi_badan\":\"166.0\",\"berat_badan\":\"51.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":28,\"back_up\":28,\"lari\":\"13.0\",\"pbb\":\"87.00\",\"kepribadian\":\"84.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.96,0.96,0.058823529411764705,0.04,1,0.2857142857142857,0.7142857142857143,0.7142857142857143,0.75,0.68,0.56],\"rank\":2},{\"id_latih\":117,\"nama_referensi\":\"RAISSA AURELLA\",\"jarak\":0.7093759932768955,\"label\":\"Lulus\",\"data_latih\":{\"id\":117,\"nama_referensi\":\"RAISSA AURELLA\",\"label\":\"Lulus\",\"nilai_pip\":\"83.00\",\"nilai_tiu\":\"85.00\",\"tinggi_badan\":\"165.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":27,\"sit_up\":26,\"back_up\":26,\"lari\":\"13.0\",\"pbb\":\"86.00\",\"kepribadian\":\"92.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.52,0.6,0,0.36,1,0.5714285714285714,0.42857142857142855,0.42857142857142855,0.75,0.64,0.88],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(275, 22, 'Lulus', 0.37730385, 3, 3, 0, NULL, '[{\"id_latih\":68,\"nama_referensi\":\"INA APRIL LIA\",\"jarak\":0.37730384975868314,\"label\":\"Lulus\",\"data_latih\":{\"id\":68,\"nama_referensi\":\"INA APRIL LIA\",\"label\":\"Lulus\",\"nilai_pip\":\"89.00\",\"nilai_tiu\":\"94.00\",\"tinggi_badan\":\"168.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":30,\"back_up\":29,\"lari\":\"13.0\",\"pbb\":\"90.00\",\"kepribadian\":\"88.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.76,0.96,0.17647058823529413,0.24,1,0.7142857142857143,1,0.8571428571428571,0.75,0.8,0.72],\"rank\":1},{\"id_latih\":47,\"nama_referensi\":\"DZIKRI AGUS RAMDANI\",\"jarak\":0.542557251244293,\"label\":\"Lulus\",\"data_latih\":{\"id\":47,\"nama_referensi\":\"DZIKRI AGUS RAMDANI\",\"label\":\"Lulus\",\"nilai_pip\":\"95.00\",\"nilai_tiu\":\"88.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":28,\"sit_up\":29,\"back_up\":29,\"lari\":\"12.0\",\"pbb\":\"89.00\",\"kepribadian\":\"91.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[1,0.72,0.29411764705882354,0.24,1,0.7142857142857143,0.8571428571428571,0.8571428571428571,0.5,0.76,0.84],\"rank\":2},{\"id_latih\":67,\"nama_referensi\":\"IMAN\",\"jarak\":0.5901495862432413,\"label\":\"Lulus\",\"data_latih\":{\"id\":67,\"nama_referensi\":\"IMAN\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"86.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"64.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":28,\"back_up\":30,\"lari\":\"13.0\",\"pbb\":\"85.00\",\"kepribadian\":\"84.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.64,0.29411764705882354,0.56,1,0.8571428571428571,0.7142857142857143,1,0.75,0.6,0.56],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(276, 23, 'Tidak Lulus', 0.199018, 3, 0, 3, NULL, '[{\"id_latih\":139,\"nama_referensi\":\"SITI RAHMA\",\"jarak\":0.19901799734020567,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":139,\"nama_referensi\":\"SITI RAHMA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"169.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"76.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0,0.04,0.23529411764705882,0.32,1,0.42857142857142855,0.2857142857142857,0,0.25,0.24,0.32],\"rank\":1},{\"id_latih\":84,\"nama_referensi\":\"MUHAMAD DONA RIZKI\",\"jarak\":0.24874994300113715,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":84,\"nama_referensi\":\"MUHAMAD DONA RIZKI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"74.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.08,0.29411764705882354,0.36,1,0.14285714285714285,0.14285714285714285,0,0.25,0.16,0.32],\"rank\":2},{\"id_latih\":52,\"nama_referensi\":\"FATIMAH AZZAHRA\",\"jarak\":0.33023803079057873,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":52,\"nama_referensi\":\"FATIMAH AZZAHRA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":26,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.35294117647058826,0.44,1,0.14285714285714285,0.42857142857142855,0,0.25,0.2,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(277, 24, 'Tidak Lulus', 0.41855071, 3, 0, 3, NULL, '[{\"id_latih\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"jarak\":0.4185507106771832,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"57.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.35294117647058826,0.28,1,0.14285714285714285,0.14285714285714285,0.2857142857142857,0.25,0.12,0.04],\"rank\":1},{\"id_latih\":147,\"nama_referensi\":\"TIARA PUTRI ANANDA\",\"jarak\":0.4296558368343651,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":147,\"nama_referensi\":\"TIARA PUTRI ANANDA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"166.0\",\"berat_badan\":\"52.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":24,\"back_up\":26,\"lari\":\"10.0\",\"pbb\":\"70.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.32,0.12,0.058823529411764705,0.08,1,0,0.14285714285714285,0.42857142857142855,0,0,0.08],\"rank\":2},{\"id_latih\":114,\"nama_referensi\":\"PUTRI AYU LESTARI\",\"jarak\":0.4837175672214534,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":114,\"nama_referensi\":\"PUTRI AYU LESTARI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"167.0\",\"berat_badan\":\"60.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":25,\"back_up\":26,\"lari\":\"10.0\",\"pbb\":\"76.00\",\"kepribadian\":\"70.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0.08,0.11764705882352941,0.4,1,0.14285714285714285,0.2857142857142857,0.42857142857142855,0,0.24,0],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(278, 25, 'Tidak Lulus', 0.37343963, 3, 0, 3, NULL, '[{\"id_latih\":22,\"nama_referensi\":\"AKMAL MIZAZ\",\"jarak\":0.3734396296330094,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":22,\"nama_referensi\":\"AKMAL MIZAZ\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"67.0\",\"kesehatan\":0,\"push_up\":23,\"sit_up\":23,\"back_up\":24,\"lari\":\"10.0\",\"pbb\":\"77.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.12,0.4117647058823529,0.68,0,0,0,0.14285714285714285,0.2,0.28,0.08],\"rank\":1},{\"id_latih\":134,\"nama_referensi\":\"SAHRUL HIDAYAT\",\"jarak\":0.3877604866960054,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":134,\"nama_referensi\":\"SAHRUL HIDAYAT\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"63.0\",\"kesehatan\":0,\"push_up\":23,\"sit_up\":24,\"back_up\":23,\"lari\":\"10.0\",\"pbb\":\"78.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0,0.35294117647058826,0.52,0,0,0.14285714285714285,0,0.2,0.32,0.12],\"rank\":2},{\"id_latih\":146,\"nama_referensi\":\"TEGAR SAWALUDIN\",\"jarak\":0.4232032891400861,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":146,\"nama_referensi\":\"TEGAR SAWALUDIN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"64.0\",\"kesehatan\":0,\"push_up\":25,\"sit_up\":23,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.32,0.04,0.47058823529411764,0.56,0,0.2857142857142857,0,0.2857142857142857,0.2,0.2,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(279, 26, 'Tidak Lulus', 0.32228143, 3, 0, 3, NULL, '[{\"id_latih\":22,\"nama_referensi\":\"AKMAL MIZAZ\",\"jarak\":0.32228143019067257,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":22,\"nama_referensi\":\"AKMAL MIZAZ\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"67.0\",\"kesehatan\":0,\"push_up\":23,\"sit_up\":23,\"back_up\":24,\"lari\":\"10.0\",\"pbb\":\"77.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.12,0.4117647058823529,0.68,0,0,0,0.14285714285714285,0,0.28,0.08],\"rank\":1},{\"id_latih\":134,\"nama_referensi\":\"SAHRUL HIDAYAT\",\"jarak\":0.3751013515557993,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":134,\"nama_referensi\":\"SAHRUL HIDAYAT\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"63.0\",\"kesehatan\":0,\"push_up\":23,\"sit_up\":24,\"back_up\":23,\"lari\":\"10.0\",\"pbb\":\"78.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0,0.35294117647058826,0.52,0,0,0.14285714285714285,0,0,0.32,0.12],\"rank\":2},{\"id_latih\":146,\"nama_referensi\":\"TEGAR SAWALUDIN\",\"jarak\":0.4232708294257035,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":146,\"nama_referensi\":\"TEGAR SAWALUDIN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"64.0\",\"kesehatan\":0,\"push_up\":25,\"sit_up\":23,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.32,0.04,0.47058823529411764,0.56,0,0.2857142857142857,0,0.2857142857142857,0,0.2,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(280, 27, 'Tidak Lulus', 0.46202974, 3, 0, 3, NULL, '[{\"id_latih\":17,\"nama_referensi\":\"AHMAD WIRANTO\",\"jarak\":0.46202974478340036,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":17,\"nama_referensi\":\"AHMAD WIRANTO\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"77.00\",\"tinggi_badan\":\"175.0\",\"berat_badan\":\"69.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":23,\"back_up\":24,\"lari\":\"11.0\",\"pbb\":\"72.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.24,0.28,0.5882352941176471,0.76,1,0.42857142857142855,0,0.14285714285714285,0.25,0.08,0.12],\"rank\":1},{\"id_latih\":97,\"nama_referensi\":\"MUHAMMAD IKHSAN RAMBE\",\"jarak\":0.48920498448199695,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":97,\"nama_referensi\":\"MUHAMMAD IKHSAN RAMBE\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":24,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"72.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.32,0.08,0.5294117647058824,0.44,1,0.2857142857142857,0.14285714285714285,0.2857142857142857,0,0.08,0.28],\"rank\":2},{\"id_latih\":46,\"nama_referensi\":\"DIKA VERNANDA\",\"jarak\":0.4918914077115387,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":46,\"nama_referensi\":\"DIKA VERNANDA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"73.00\",\"nilai_tiu\":\"78.00\",\"tinggi_badan\":\"181.0\",\"berat_badan\":\"71.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":23,\"back_up\":26,\"lari\":\"11.0\",\"pbb\":\"78.00\",\"kepribadian\":\"74.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.12,0.32,0.9411764705882353,0.84,1,0.2857142857142857,0,0.42857142857142855,0.25,0.32,0.16],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(281, 28, 'Tidak Lulus', 0.39105181, 3, 0, 3, NULL, '[{\"id_latih\":143,\"nama_referensi\":\"SYIFA NURJANAH\",\"jarak\":0.3910518083860896,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":143,\"nama_referensi\":\"SYIFA NURJANAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"76.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"165.0\",\"berat_badan\":\"55.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":25,\"back_up\":23,\"lari\":\"12.0\",\"pbb\":\"76.00\",\"kepribadian\":\"70.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0.24,0.2,0,0.2,1,0.14285714285714285,0.2857142857142857,0,0.5,0.24,0],\"rank\":1},{\"id_latih\":52,\"nama_referensi\":\"FATIMAH AZZAHRA\",\"jarak\":0.42891534232032646,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":52,\"nama_referensi\":\"FATIMAH AZZAHRA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":26,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.35294117647058826,0.44,1,0.14285714285714285,0.42857142857142855,0,0.25,0.2,0.24],\"rank\":2},{\"id_latih\":66,\"nama_referensi\":\"ILHAM\",\"jarak\":0.481724189804418,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":66,\"nama_referensi\":\"ILHAM\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":25,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"78.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.47058823529411764,0.44,1,0.14285714285714285,0.2857142857142857,0,0.25,0.32,0.28],\"rank\":3}]', 1, '2026-05-18 07:02:32');
INSERT INTO `hasil_knn` (`id`, `id_peserta`, `status_kelulusan`, `skor_knn`, `k_value`, `vote_lulus`, `vote_tidak_lulus`, `keterangan_kuota`, `detail_knn`, `processed_by`, `processed_at`) VALUES
(282, 29, 'Tidak Lulus', 0.23553457, 3, 0, 3, NULL, '[{\"id_latih\":36,\"nama_referensi\":\"ARYA FIRMAN\",\"jarak\":0.23553457101467926,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":36,\"nama_referensi\":\"ARYA FIRMAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"64.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":25,\"back_up\":23,\"lari\":\"10.0\",\"pbb\":\"73.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0,0.04,0.4117647058823529,0.56,1,0.2857142857142857,0.2857142857142857,0,0,0.12,0.08],\"rank\":1},{\"id_latih\":45,\"nama_referensi\":\"DIANA PUTRI\",\"jarak\":0.32415510815513765,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":45,\"nama_referensi\":\"DIANA PUTRI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"73.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"67.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":24,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.12,0.12,0.4117647058823529,0.68,1,0.42857142857142855,0.2857142857142857,0.14285714285714285,0,0.2,0.04],\"rank\":2},{\"id_latih\":90,\"nama_referensi\":\"MUHAMAD RIZQI AL PIANSYAH\",\"jarak\":0.39955076814659063,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":90,\"nama_referensi\":\"MUHAMAD RIZQI AL PIANSYAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"66.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"72.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0.04,0.35294117647058826,0.64,1,0.42857142857142855,0.2857142857142857,0.2857142857142857,0,0.2,0.08],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(283, 30, 'Tidak Lulus', 0.4437503, 3, 0, 3, NULL, '[{\"id_latih\":120,\"nama_referensi\":\"RANI WULANDARI\",\"jarak\":0.4437502956339436,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":120,\"nama_referensi\":\"RANI WULANDARI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"74.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"65.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":23,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.16,0.35294117647058826,0.6,1,0,0,0.2857142857142857,0.25,0.2,0.2403038784486207],\"rank\":1},{\"id_latih\":43,\"nama_referensi\":\"DELLA ANGGRAENI\",\"jarak\":0.4446724070799586,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":43,\"nama_referensi\":\"DELLA ANGGRAENI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"78.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":26,\"back_up\":26,\"lari\":\"12.0\",\"pbb\":\"72.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0,0.32,0.29411764705882354,0.32,1,0.14285714285714285,0.42857142857142855,0.42857142857142855,0.5,0.08,0.12035185925629766],\"rank\":2},{\"id_latih\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"jarak\":0.4489980244196872,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"57.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.35294117647058826,0.28,1,0.14285714285714285,0.14285714285714285,0.2857142857142857,0.25,0.12,0.04038384646141563],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(284, 31, 'Tidak Lulus', 0.3771848, 3, 0, 3, NULL, '[{\"id_latih\":19,\"nama_referensi\":\"AISYAH NUR AZIZAH\",\"jarak\":0.37718479672139876,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":19,\"nama_referensi\":\"AISYAH NUR AZIZAH\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"76.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":26,\"back_up\":23,\"lari\":\"12.0\",\"pbb\":\"73.00\",\"kepribadian\":\"75.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0,0.24,0.35294117647058826,0.32,1,0.42857142857142855,0.42857142857142855,0,0.5,0.12,0.2],\"rank\":1},{\"id_latih\":102,\"nama_referensi\":\"MUHHAMAD ASRI IRAWAN\",\"jarak\":0.41569219381653055,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":102,\"nama_referensi\":\"MUHHAMAD ASRI IRAWAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"77.00\",\"nilai_tiu\":\"77.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"60.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":26,\"back_up\":24,\"lari\":\"12.0\",\"pbb\":\"70.00\",\"kepribadian\":\"70.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.28,0.28,0.29411764705882354,0.4,1,0.42857142857142855,0.42857142857142855,0.14285714285714285,0.5,0,0],\"rank\":2},{\"id_latih\":52,\"nama_referensi\":\"FATIMAH AZZAHRA\",\"jarak\":0.44542229843036296,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":52,\"nama_referensi\":\"FATIMAH AZZAHRA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":26,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"75.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.12,0.35294117647058826,0.44,1,0.14285714285714285,0.42857142857142855,0,0.25,0.2,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(285, 32, 'Tidak Lulus', 0.35317585, 3, 0, 3, NULL, '[{\"id_latih\":139,\"nama_referensi\":\"SITI RAHMA\",\"jarak\":0.3531758541279222,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":139,\"nama_referensi\":\"SITI RAHMA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"70.00\",\"nilai_tiu\":\"71.00\",\"tinggi_badan\":\"169.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"76.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:39\"},\"latih_norm\":[0,0.04,0.23529411764705882,0.32,1,0.42857142857142855,0.2857142857142857,0,0.25,0.24,0.32],\"rank\":1},{\"id_latih\":129,\"nama_referensi\":\"RIZKA AMELIA\",\"jarak\":0.3973734308066551,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":129,\"nama_referensi\":\"RIZKA AMELIA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"70.00\",\"tinggi_badan\":\"166.0\",\"berat_badan\":\"55.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":26,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.32,0,0.058823529411764705,0.2,1,0.14285714285714285,0.42857142857142855,0.2857142857142857,0.25,0.12,0.32],\"rank\":2},{\"id_latih\":111,\"nama_referensi\":\"PADLAN FAHRUROZI\",\"jarak\":0.3982740159455758,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":111,\"nama_referensi\":\"PADLAN FAHRUROZI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"77.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"56.0\",\"kesehatan\":1,\"push_up\":26,\"sit_up\":25,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"71.00\",\"kepribadian\":\"73.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.28,0.12,0.35294117647058826,0.24,1,0.42857142857142855,0.2857142857142857,0.2857142857142857,0.25,0.04,0.12],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(286, 33, 'Tidak Lulus', 0.42987994, 3, 0, 3, NULL, '[{\"id_latih\":103,\"nama_referensi\":\"NABILLA AZZAHRA\",\"jarak\":0.4298799436348377,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":103,\"nama_referensi\":\"NABILLA AZZAHRA\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"73.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"168.0\",\"berat_badan\":\"58.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":23,\"back_up\":23,\"lari\":\"10.0\",\"pbb\":\"75.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.12,0.12,0.17647058823529413,0.32,1,0,0,0.2222222222222222,0.1111111111111111,0.2,0.28],\"rank\":1},{\"id_latih\":69,\"nama_referensi\":\"INTAN LESTARI\",\"jarak\":0.5729542243581738,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":69,\"nama_referensi\":\"INTAN LESTARI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"72.00\",\"nilai_tiu\":\"73.00\",\"tinggi_badan\":\"167.0\",\"berat_badan\":\"52.0\",\"kesehatan\":1,\"push_up\":23,\"sit_up\":24,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"78.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.08,0.12,0.11764705882352941,0.08,1,0,0.14285714285714285,0.4444444444444444,0.1111111111111111,0.32,0.04],\"rank\":2},{\"id_latih\":84,\"nama_referensi\":\"MUHAMAD DONA RIZKI\",\"jarak\":0.6177733070778558,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":84,\"nama_referensi\":\"MUHAMAD DONA RIZKI\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"71.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"59.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":23,\"lari\":\"11.0\",\"pbb\":\"74.00\",\"kepribadian\":\"78.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.04,0.08,0.29411764705882354,0.36,1,0.14285714285714285,0.14285714285714285,0.2222222222222222,0.3333333333333333,0.16,0.32],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(287, 34, 'Tidak Lulus', 0.42699817, 3, 0, 3, NULL, '[{\"id_latih\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"jarak\":0.4269981705367405,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":86,\"nama_referensi\":\"MUHAMAD GILMAN\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"74.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"171.0\",\"berat_badan\":\"57.0\",\"kesehatan\":1,\"push_up\":24,\"sit_up\":24,\"back_up\":25,\"lari\":\"11.0\",\"pbb\":\"73.00\",\"kepribadian\":\"71.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.16,0.2,0.35294117647058826,0.28,1,0.14285714285714285,0.14285714285714285,0.2857142857142857,0.3333333333333333,0.12,0.04],\"rank\":1},{\"id_latih\":97,\"nama_referensi\":\"MUHAMMAD IKHSAN RAMBE\",\"jarak\":0.4646457906725967,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":97,\"nama_referensi\":\"MUHAMMAD IKHSAN RAMBE\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"78.00\",\"nilai_tiu\":\"72.00\",\"tinggi_badan\":\"174.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":24,\"back_up\":25,\"lari\":\"10.0\",\"pbb\":\"72.00\",\"kepribadian\":\"77.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.32,0.08,0.5294117647058824,0.44,1,0.2857142857142857,0.14285714285714285,0.2857142857142857,0.1111111111111111,0.08,0.28],\"rank\":2},{\"id_latih\":24,\"nama_referensi\":\"ALIEF AL FAJAR\",\"jarak\":0.4777517168481271,\"label\":\"Tidak Lulus\",\"data_latih\":{\"id\":24,\"nama_referensi\":\"ALIEF AL FAJAR\",\"label\":\"Tidak Lulus\",\"nilai_pip\":\"75.00\",\"nilai_tiu\":\"75.00\",\"tinggi_badan\":\"172.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":25,\"sit_up\":25,\"back_up\":26,\"lari\":\"10.0\",\"pbb\":\"74.00\",\"kepribadian\":\"76.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.2,0.2,0.4117647058823529,0.48,1,0.2857142857142857,0.2857142857142857,0.42857142857142855,0.1111111111111111,0.16,0.24],\"rank\":3}]', 1, '2026-05-18 07:02:32'),
(438, 12, 'Lulus', 0.53130184, 3, 3, 0, NULL, '[{\"id_latih\":135,\"nama_referensi\":\"SALSABILA\",\"jarak\":0.5313018414953571,\"label\":\"Lulus\",\"data_latih\":{\"id\":135,\"nama_referensi\":\"SALSABILA\",\"label\":\"Lulus\",\"nilai_pip\":\"91.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"170.0\",\"berat_badan\":\"61.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":25,\"back_up\":25,\"lari\":\"12.0\",\"pbb\":\"85.00\",\"kepribadian\":\"83.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.84,0.56,0.29411764705882354,0.44,1,0.8571428571428571,0.2857142857142857,0.2857142857142857,0.5,0.6,0.52],\"rank\":1},{\"id_latih\":72,\"nama_referensi\":\"KAYLA ZAIRA RAHMAN\",\"jarak\":0.5375093734351171,\"label\":\"Lulus\",\"data_latih\":{\"id\":72,\"nama_referensi\":\"KAYLA ZAIRA RAHMAN\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"84.00\",\"tinggi_badan\":\"168.0\",\"berat_badan\":\"62.0\",\"kesehatan\":1,\"push_up\":29,\"sit_up\":28,\"back_up\":25,\"lari\":\"13.0\",\"pbb\":\"92.00\",\"kepribadian\":\"84.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.56,0.17647058823529413,0.48,1,0.8571428571428571,0.7142857142857143,0.2857142857142857,0.75,0.88,0.56],\"rank\":2},{\"id_latih\":16,\"nama_referensi\":\"ADITYA ZIQRI WARDANA\",\"jarak\":0.5763565068293061,\"label\":\"Lulus\",\"data_latih\":{\"id\":16,\"nama_referensi\":\"ADITYA ZIQRI WARDANA\",\"label\":\"Lulus\",\"nilai_pip\":\"90.00\",\"nilai_tiu\":\"94.00\",\"tinggi_badan\":\"173.0\",\"berat_badan\":\"60.0\",\"kesehatan\":1,\"push_up\":30,\"sit_up\":30,\"back_up\":26,\"lari\":\"12.0\",\"pbb\":\"91.00\",\"kepribadian\":\"87.00\",\"created_at\":\"2026-05-14 15:54:38\"},\"latih_norm\":[0.8,0.96,0.47058823529411764,0.4,1,1,1,0.42857142857142855,0.5,0.84,0.68],\"rank\":3}]', 1, '2026-05-18 07:02:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `nama_admin` varchar(150) DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `id_admin`, `nama_admin`, `aksi`, `detail`, `ip_address`, `user_agent`, `created_at`) VALUES
(302, 2, 'Super Administrator', 'HAPUS_LOG', 'Semua log dihapus', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:13:53'),
(303, 2, 'Super Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:14:03'),
(304, 1, 'Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:14:20'),
(305, 1, 'Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:14:38'),
(306, 1, 'Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:14:47'),
(307, 1, 'Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 11:15:27'),
(308, 1, 'Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-22 02:07:57'),
(309, 1, 'Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-22 02:08:09'),
(310, 2, 'Super Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-22 02:08:42'),
(311, 2, 'Super Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-22 02:09:06'),
(312, 1, 'Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-05-22 02:55:18'),
(313, 1, 'Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 06:39:11'),
(314, 1, 'Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:21:15'),
(315, 2, 'Super Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:21:31'),
(316, 2, 'Super Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-18 07:22:30'),
(317, 1, 'Administrator', 'LOGIN', 'Admin login berhasil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 15:11:26'),
(318, 1, 'Administrator', 'LOGOUT', 'Admin logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-19 15:17:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `kunci` varchar(80) NOT NULL,
  `nilai` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`kunci`, `nilai`, `updated_at`) VALUES
('kota', 'Lebak', '2026-05-18 06:57:20'),
('kuota_lulus_laki', '27', '2026-05-18 06:58:22'),
('kuota_lulus_perempuan', '27', '2026-05-18 06:58:22'),
('k_value', '3', '2026-05-14 08:47:28'),
('nama_instansi', 'Purna Paskibraka Indonesia (PPI) Lebak', '2026-05-15 17:25:34'),
('nama_kegiatan', 'Seleksi Paskibraka Kabupaten Lebak', '2026-05-16 05:28:21'),
('nama_kepala', 'Panitia Seleksi', '2026-05-15 17:25:34'),
('nip_kepala', '', '2026-05-14 15:37:23'),
('prefix_nomor', 'PSK', '2026-05-14 08:47:28'),
('tahun_seleksi', '2026', '2026-05-14 08:47:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peserta`
--

CREATE TABLE `peserta` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `nomor_peserta` varchar(30) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `asal_sekolah` varchar(200) DEFAULT NULL,
  `nilai_pip` decimal(5,2) DEFAULT NULL,
  `nilai_tiu` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,1) DEFAULT NULL,
  `berat_badan` decimal(5,1) DEFAULT NULL,
  `kesehatan` tinyint(1) DEFAULT NULL,
  `tekanan_darah_sistolik` smallint(6) DEFAULT NULL,
  `tekanan_darah_diastolik` smallint(6) DEFAULT NULL,
  `push_up` smallint(6) DEFAULT NULL,
  `sit_up` smallint(6) DEFAULT NULL,
  `back_up` smallint(6) DEFAULT NULL,
  `lari` decimal(4,1) DEFAULT NULL,
  `pbb` decimal(5,2) DEFAULT NULL,
  `kepribadian` decimal(5,2) DEFAULT NULL,
  `keterangan_nilai` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peserta`
--

INSERT INTO `peserta` (`id`, `nama`, `nomor_peserta`, `jenis_kelamin`, `asal_sekolah`, `nilai_pip`, `nilai_tiu`, `tinggi_badan`, `berat_badan`, `kesehatan`, `tekanan_darah_sistolik`, `tekanan_darah_diastolik`, `push_up`, `sit_up`, `back_up`, `lari`, `pbb`, `kepribadian`, `keterangan_nilai`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ADAM ANANDI', 'PSK-2026-001', 'L', 'SMAN 1 Cigemblong', 75.00, 76.00, 170.0, 61.0, 1, NULL, NULL, 26, 26, 23, 10.0, 72.00, 72.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:52:07'),
(2, 'ADRIAN DESTIAWAN', 'PSK-2026-002', 'L', 'MAN 1 LEBAK', 76.00, 73.00, 173.0, 66.0, 1, NULL, NULL, 25, 24, 26, 11.0, 71.00, 73.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:51:12'),
(3, 'ALVAZRI AHMAD', 'PSK-2026-003', 'L', 'SMA NEGERI 1 RANGKASBITUNG', 72.00, 75.00, 170.0, 63.0, 1, NULL, NULL, 24, 23, 25, 12.0, 77.00, 76.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:50:01'),
(4, 'ARIF RAHMATULAH PUTRA', 'PSK-2026-004', 'L', 'SMAN 1 MAJA', 81.00, 91.00, 170.0, 58.0, 1, NULL, NULL, 29, 30, 26, 14.0, 94.00, 95.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:48:55'),
(5, 'ARIS AWALUDIN', 'PSK-2026-005', 'L', 'SMAN 1 MALINGPING', 70.00, 76.00, 170.0, 64.0, 0, NULL, NULL, 25, 26, 26, 11.0, 74.00, 76.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:47:46'),
(6, 'ARYA FAIRUS', 'PSK-2026-006', 'L', 'SMAN 2 LEUWIDAMAR', 70.00, 76.00, 174.0, 62.0, 0, NULL, NULL, 26, 23, 26, 11.0, 76.00, 76.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:46:35'),
(7, 'ASEP SAEFULLAH', 'PSK-2026-007', 'L', 'SMAN 1 CURUGBITUNG', 77.00, 70.00, 176.0, 61.0, 1, NULL, NULL, 23, 26, 23, 12.0, 76.00, 70.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:45:32'),
(8, 'AZIZAH NURAWAL RAMADANI', 'PSK-2026-008', 'P', 'SMAN 3 CIBEBER', 91.00, 81.00, 173.0, 62.0, 1, NULL, NULL, 28, 26, 30, 11.0, 91.00, 83.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:44:17'),
(9, 'AZLIA TALITA ZATMIKA', 'PSK-2026-009', 'P', 'SMAN 2 RANGKASBITUNG', 94.00, 93.00, 165.0, 57.0, 1, NULL, NULL, 29, 27, 27, 13.0, 84.00, 91.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:43:03'),
(10, 'BAYU IBRAHIM', 'PSK-2026-010', 'L', 'MAN 1 LEBAK', 94.00, 86.00, 173.0, 61.0, 1, NULL, NULL, 28, 27, 25, 12.0, 83.00, 83.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:40:57'),
(11, 'DEL FIKRI', 'PSK-2026-011', 'L', 'SMKN 1 CIPANAS', 72.00, 76.00, 170.0, 62.0, 1, NULL, NULL, 26, 24, 23, 11.0, 72.00, 77.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:39:32'),
(12, 'FARACH ADZAR FAUZIYYAH', 'PSK-2026-012', 'P', 'SMA LA TANSA 2', 91.00, 92.00, 168.0, 61.0, 1, NULL, NULL, 30, 27, 25, 12.0, 87.00, 89.00, '', 1, '2026-05-14 08:51:31', '2026-05-18 07:02:16'),
(13, 'FASKAL AJI PRATAMA', 'PSK-2026-013', 'L', 'SMAN 1 BAYAH', 70.00, 72.00, 178.0, 63.0, 0, NULL, NULL, 26, 26, 24, 11.0, 77.00, 70.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:36:06'),
(14, 'KIKI AL-FARIZI', 'PSK-2026-014', 'L', 'SMAN 1 CIJAKU', 74.00, 73.00, 171.0, 56.0, 1, NULL, NULL, 24, 23, 23, 12.0, 76.00, 71.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:34:46'),
(15, 'MAULANA WIDYA OCTA HAEPI', 'PSK-2026-015', 'L', 'SMKN 1 CIKULUR', 74.00, 70.00, 170.0, 61.0, 0, NULL, NULL, 25, 24, 24, 11.0, 78.00, 73.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:33:09'),
(16, 'MAURA ADILA RAHAYU', 'PSK-2026-016', 'P', 'SMAN 1 LEUWIDAMAR', 95.00, 87.00, 173.0, 64.0, 1, NULL, NULL, 28, 26, 26, 12.0, 93.00, 93.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:31:55'),
(17, 'MUHAMAD FARHAN', 'PSK-2026-017', 'L', 'SMKN 1 RANGKASBITUNG', 83.00, 85.00, 172.0, 58.0, 1, NULL, NULL, 27, 27, 27, 12.0, 93.00, 83.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:30:07'),
(18, 'MUHAMAD HABIB RIZAL', 'PSK-2026-018', 'L', 'SMAN 1 CIPANAS', 86.00, 84.00, 172.0, 64.0, 1, NULL, NULL, 28, 28, 29, 14.0, 95.00, 91.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:28:45'),
(19, 'MUHAMAD SENJA PRATAMA PUTRA', 'PSK-2026-019', 'L', 'SMAN 1 MAJA', 76.00, 70.00, 177.0, 67.0, 0, NULL, NULL, 24, 23, 24, 11.0, 77.00, 75.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:27:10'),
(20, 'MUHAMMAD SYAHRU MAULID', 'PSK-2026-020', 'L', 'SMAN 1 RANGKASBITUNG', 91.00, 82.00, 170.0, 56.0, 1, NULL, NULL, 28, 25, 30, 14.0, 92.00, 89.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:25:58'),
(21, 'NAMIRA', 'PSK-2026-021', 'P', 'SMAN 1 RANGKASBITUNG', 86.00, 88.00, 167.0, 54.0, 1, NULL, NULL, 25, 25, 28, 12.0, 83.00, 82.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:24:15'),
(22, 'NAQI ABRIR SETIAWAN', 'PSK-2026-022', 'P', 'SMAN 2 RANGKASBITUNG', 84.00, 91.00, 171.0, 56.0, 1, NULL, NULL, 28, 29, 30, 13.0, 89.00, 91.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:22:56'),
(23, 'NISA AYU LESTARI', 'PSK-2026-023', 'P', 'SMAN 2 MALINGPING', 70.00, 74.00, 169.0, 57.0, 1, NULL, NULL, 25, 25, 23, 11.0, 75.00, 77.00, '', 1, '2026-05-14 08:51:31', '2026-05-16 05:09:55'),
(24, 'NUR AZZAHRA', 'PSK-2026-024', 'P', 'SMAN 1 LEUWIDAMAR', 78.00, 73.00, 170.0, 56.0, 1, NULL, NULL, 23, 25, 24, 10.0, 70.00, 71.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:21:30'),
(25, 'RAFLI NOPALDI', 'PSK-2026-025', 'L', 'SMK PLUS MIFTAHUL IHSAN CILOGRANG', 77.00, 74.00, 174.0, 63.0, 0, NULL, NULL, 24, 23, 23, 9.0, 78.00, 75.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:19:33'),
(26, 'RANDI AIDIL FITRA', 'PSK-2026-026', 'L', 'SMKN 2 RANGKASBITUNG', 73.00, 72.00, 170.0, 65.0, 0, NULL, NULL, 24, 24, 25, 10.0, 77.00, 70.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:18:01'),
(27, 'RANGGA VAIZ SEPTIAN', 'PSK-2026-027', 'L', 'SMAN 3 RANGKASBITUNG', 73.00, 72.00, 179.0, 66.0, 1, NULL, NULL, 26, 23, 25, 10.0, 74.00, 73.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:23:16'),
(28, 'RIDWAN', 'PSK-2026-028', 'L', 'SMA NEGERI 1 WANASALAM', 78.00, 75.00, 170.0, 57.0, 1, NULL, NULL, 23, 26, 23, 12.0, 78.00, 72.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:22:54'),
(29, 'ROVEE HANUTOMO RAHARDJO', 'PSK-2026-029', 'L', 'SMAN 1 RANGKASBITUNG', 70.00, 70.00, 171.0, 63.0, 1, NULL, NULL, 26, 26, 23, 10.0, 75.00, 73.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:18:13'),
(30, 'SALSABILA PUTRI', 'PSK-2026-030', 'P', 'SMAN 2 RANGKASBITUNG', 71.00, 78.00, 170.0, 64.0, 1, NULL, NULL, 23, 24, 25, 12.0, 71.00, 69.99, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:16:21'),
(31, 'SEPTIAN RIZKI RAMADAN', 'PSK-2026-031', 'L', 'SMKN 1 BAYAH', 77.00, 74.00, 170.0, 59.0, 1, NULL, NULL, 26, 26, 24, 12.0, 77.00, 77.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 16:14:38'),
(32, 'SITI AMALIA', 'PSK-2026-032', 'P', 'SMAN 2 RANGKASBITUNG', 73.00, 70.00, 168.0, 57.0, 1, NULL, NULL, 26, 26, 25, 11.0, 76.00, 76.99, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:12:32'),
(33, 'SITI NURHALIZA', 'PSK-2026-033', 'P', 'SMAN 1 CIPANAS', 77.00, 76.00, 167.0, 58.0, 1, NULL, NULL, 23, 24, 21, 9.5, 76.00, 71.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:04:41'),
(34, 'YUNI KARTIKA', 'PSK-2026-034', 'P', 'SMAN 1 LEUWIDAMAR', 73.00, 77.00, 171.0, 56.0, 1, NULL, NULL, 25, 23, 25, 9.5, 70.00, 73.00, '', 1, '2026-05-14 08:51:31', '2026-05-14 09:02:40'),
(35, 'Fauzi Sofyan', 'PSK-2026-035', 'L', 'SMAN 1 Banjarsari', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 14:05:02', '2026-05-14 16:14:57'),
(36, 'Budi Santoso', 'PSK-2026-036', 'L', 'SMA Negeri 1 Contoh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 16:55:26', '2026-05-14 16:55:35'),
(37, 'Siti Rahayu', 'PSK-2026-037', 'P', 'SMA Negeri 2 Contoh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 16:55:26', '2026-05-14 16:55:41'),
(38, 'Ahmad Fauzi', 'PSK-2026-038', 'L', 'MAN 1 Contoh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 16:55:26', '2026-05-14 16:55:45'),
(39, 'Fauzi Sofyan', 'PSK-2026-039', 'L', 'SMAN 1 Banjarsari', 80.00, 80.00, 170.0, 60.0, 1, NULL, NULL, 25, 25, 25, 12.0, 80.00, 80.00, '', 0, '2026-05-15 11:41:01', '2026-05-15 13:27:01'),
(40, 'Fauzi Sofyan', 'PSK-2026-040', 'L', 'SMAN 1 Banjarsari', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-16 05:24:58', '2026-05-16 05:25:18'),
(41, 'Fauzi Sofyan', 'PSK-2026-041', 'L', 'SMAN 1 Banjarsari', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-16 12:17:29', '2026-05-16 12:19:18'),
(42, 'Fauzi Sofyan', 'PSK-2026-042', 'L', 'SMAN 1 Banjarsari', 88.00, 88.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-16 12:45:53', '2026-05-16 12:46:48'),
(43, 'Fauzi Sofyan', 'PSK-2026-043', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-16 12:52:36', '2026-05-16 12:52:47'),
(44, 'Fauzi Sofyan', 'PSK-2026-044', 'L', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-19 05:19:11', '2026-05-19 05:19:45'),
(45, 'Fauzi Sofyan', 'PSK-2026-045', 'L', 'SMAN 1 Banjarsari', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-20 15:33:54', '2026-05-20 15:35:46');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `data_latih`
--
ALTER TABLE `data_latih`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `hasil_knn`
--
ALTER TABLE `hasil_knn`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_peserta` (`id_peserta`),
  ADD KEY `idx_status` (`status_kelulusan`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aksi` (`aksi`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`kunci`);

--
-- Indeks untuk tabel `peserta`
--
ALTER TABLE `peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aktif` (`is_active`),
  ADD KEY `idx_nomor` (`nomor_peserta`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `data_latih`
--
ALTER TABLE `data_latih`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT untuk tabel `hasil_knn`
--
ALTER TABLE `hasil_knn`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=461;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=319;

--
-- AUTO_INCREMENT untuk tabel `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `hasil_knn`
--
ALTER TABLE `hasil_knn`
  ADD CONSTRAINT `hasil_knn_ibfk_1` FOREIGN KEY (`id_peserta`) REFERENCES `peserta` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
