-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 01:30 PM
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
-- Database: `sistem_perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_buku`
--

CREATE TABLE `booking_buku` (
  `id_booking` int(11) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `tanggal_booking` datetime DEFAULT NULL,
  `batas_booking` datetime DEFAULT NULL,
  `status_booking` enum('masa booking','dibatalkan','buku dipinjam','lewat masa booking') DEFAULT NULL,
  `status_tampil` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_buku`
--

INSERT INTO `booking_buku` (`id_booking`, `id_pengguna`, `id_buku`, `tanggal_booking`, `batas_booking`, `status_booking`, `status_tampil`) VALUES
(48, 4, 4, '2025-12-03 00:00:00', '2025-12-05 00:00:00', 'dibatalkan', 1);

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `penerbit` varchar(255) DEFAULT NULL,
  `tahun_terbit` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status_buku` enum('dipinjam','dibooking','tersedia') DEFAULT 'tersedia',
  `cover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `id_kategori`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `deskripsi`, `status_buku`, `cover`) VALUES
(3, 5, 'Matahari', 'Tere Liye', 'Gramedia', 2016, 'Novel ini melanjutkan kisah petualangan Raib, Seli, dan Ali, yang biasanya melibatkan perjalanan antar-dimensi, pertempuran melawan makhluk atau kekuatan jahat, dan penggunaan kemampuan spesial (seperti menghilang, pengendalian petir, dan kecerdasan luar biasa).', 'tersedia', 'SINOPSIS dan RESENSI Novel MATAHARI Karya TERE LIYE.jpg'),
(4, 6, 'Alpha Girls Guide', 'Henry Manampiring ', 'Gramedia', 2015, 'The Alpha Girl’s Guide juga berisi wawancara inspiratif dengan dua Alpha Female Indonesia dari dua generasi: Najwa Shihab dan Alanda Kariza', 'tersedia', '[SE046] The Alpha Girl s Guide (Henry Manampiring)_pdf.jpg'),
(5, 6, 'Atomic Habits', 'James Clear', 'Gramedia', 2018, 'Buku ini berfokus pada perubahan kebiasaan melalui langkah-langkah kecil yang konsisten, seperti menyusun ulang sistem untuk membentuk kebiasaan yang baik dan menghilangkan kebiasaan buruk. Clear menjelaskan bagaimana perubahan kecil dapat menghasilkan efek yang luar biasa dalam jangka panjang melalui empat hukum perubahan perilaku. ', 'tersedia', 'Books 📚 (@bookpill) on X.jpg'),
(6, 2, 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 'Novel ini berlatar akhir abad ke-19 dan menggambarkan kehidupan seorang siswa HBS bernama Minke', 'tersedia', 'Bumi manusia_ Roman terbaik Indonesia_.jpg'),
(7, 2, 'Laut Bercerita', 'Leila S Chudor', 'Gramedia', 2017, 'novel historical fiction yang berlatar belakang kisah kelam masa Orde Baru terkait penghilangan paksa aktivis', 'tersedia', 'Laut Bercerita _ Leila S_ Chudori ₊⊹.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_buku`
--

CREATE TABLE `kategori_buku` (
  `id_kategori` int(11) NOT NULL,
  `kategori` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori_buku`
--

INSERT INTO `kategori_buku` (`id_kategori`, `kategori`) VALUES
(2, 'Sastra'),
(3, 'Sejarah'),
(4, 'Teknologi'),
(5, 'Fantasi'),
(6, 'Motivasi');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_pengguna` enum('admin','anggota') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama`, `password`, `email`, `no_telp`, `alamat`, `status_pengguna`) VALUES
(3, 'Ana Anti', '827ccb0eea8a706c4c34a16891f84e7b', 'ana@gmail.com', '0812345678', 'Perumahan Cinta Damai Jln. Aman Sejahtera', 'admin'),
(4, 'Yaya Maraya', '1e01ba3e07ac48cbdab2d3284d1dd0fa', 'yaya@gmail.com', '0898345678', 'Perumahan Bintang Jln. Kejora', 'anggota');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_peminjaman`
--

CREATE TABLE `transaksi_peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `id_buku` int(11) DEFAULT NULL,
  `tanggal_peminjaman` date DEFAULT NULL,
  `tanggal_pengembalian` date DEFAULT NULL,
  `status_peminjaman` enum('dipinjam','dikembalikan') DEFAULT NULL,
  `status_tampil` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi_peminjaman`
--

INSERT INTO `transaksi_peminjaman` (`id_peminjaman`, `id_pengguna`, `id_buku`, `tanggal_peminjaman`, `tanggal_pengembalian`, `status_peminjaman`, `status_tampil`) VALUES
(23, 4, 3, '2025-12-03', '2025-12-03', 'dikembalikan', 1),
(24, 4, 4, '2025-12-03', '2025-12-03', 'dikembalikan', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_buku`
--
ALTER TABLE `booking_buku`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `kategori_buku`
--
ALTER TABLE `kategori_buku`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`);

--
-- Indexes for table `transaksi_peminjaman`
--
ALTER TABLE `transaksi_peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_buku` (`id_buku`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_buku`
--
ALTER TABLE `booking_buku`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kategori_buku`
--
ALTER TABLE `kategori_buku`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi_peminjaman`
--
ALTER TABLE `transaksi_peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_buku`
--
ALTER TABLE `booking_buku`
  ADD CONSTRAINT `booking_buku_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `booking_buku_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`);

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_buku` (`id_kategori`);

--
-- Constraints for table `transaksi_peminjaman`
--
ALTER TABLE `transaksi_peminjaman`
  ADD CONSTRAINT `transaksi_peminjaman_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `transaksi_peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
