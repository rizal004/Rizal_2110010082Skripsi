-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Waktu pembuatan: 01 Agu 2025 pada 10.52
-- Versi server: 10.4.11-MariaDB
-- Versi PHP: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `data_siwisata`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_event`
--

CREATE TABLE `tb_event` (
  `id_event` varchar(20) NOT NULL,
  `nama_event` varchar(255) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `latitude` varchar(32) DEFAULT NULL,
  `longitude` varchar(32) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `gambar` text DEFAULT NULL,
  `harga_tiket` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `jam_operasional` varchar(50) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_event`
--

INSERT INTO `tb_event` (`id_event`, `nama_event`, `kategori`, `deskripsi`, `provinsi`, `kabupaten`, `kecamatan`, `alamat`, `latitude`, `longitude`, `tanggal_mulai`, `tanggal_selesai`, `gambar`, `harga_tiket`, `no_hp`, `jam_operasional`, `tanggal_upload`) VALUES
('EVT68400ea373ffd', 'Festival Budaya Isen Mulang (FBIM)', 'Budaya', 'Festival ini bertujuan untuk mengangkat kearifan lokal dan geliat ekonomi daerah, serta melestarikan budaya dan lingkungan. ', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Kec. Jekan Raya', 'Jl. Cilik Riwut km. 4.5 73111, Jl. Bukit Palangka, Bukit Tunggal 74874', '-2.1766247', '113.8842275', '2025-05-17', '2025-05-23', 'festival.jpeg,19062019083355_0.jpg,festival-budaya-isen-mulang_169.jpeg,WhatsApp Image 2025-05-18 at 07.49.18.jpeg', 'Gratis', NULL, '20:27 - 20:27', '2025-06-04 03:15:15'),
('EVT686dc742c9a12', 'PALANGKA RAYA FAIR 2025', 'UMKM', 'Dalam Rangka Memperingati Hari Jadi Pemerintah Kota Palngka Raya ke-60 dan Hari Jadi Kota Palangka Raya ke-68', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Tjilik Riwut No.Km. 5 Halaman Gor serba guna Indoor', '-2.1757031', '113.882034', '2025-07-15', '2025-07-19', 'zxzxzz.jpg', 'Gratis', NULL, '08:00 - 16:00', '2025-07-08 19:34:58'),
('EVT686dcb8d07250', 'LEWU PALANGKA FESTIVAL 2025', 'Festival', 'Peringatan Hari Jadi Pemerintah Kota Palangka Raya ke-60Th dan Hari Jadi Kota Palangka Raya ke-68Th', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Tjilik Riwut KM 5.5', '-2.178053', '113.8809168', '2025-07-18', '2025-07-18', 'qqq.jpg', 'Gratis', NULL, '18:00 - Sampai Selesai', '2025-07-08 19:53:17'),
('EVT686f8a002843f', 'Jalan Sehat Hari Koperasi Nasional Ke 78 Tahun', 'Olahraga', 'Hari Koperasi Nasional Ke 78 Tahun', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. HM. Rafi\'i, Madurejo,  74181', '-2.195626', '113.901848', '2025-07-11', '2025-07-11', 'zzx.jpg', 'Gratis', '', '17:41 - Sampai Selesai', '2025-07-10 03:38:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_feedback_sewa`
--

CREATE TABLE `tb_feedback_sewa` (
  `id_feedback` int(11) NOT NULL,
  `id_motor` int(11) DEFAULT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_feedback_sewa`
--

INSERT INTO `tb_feedback_sewa` (`id_feedback`, `id_motor`, `id_pengguna`, `rating`, `komentar`, `tanggal`) VALUES
(2, 9, 10, 5, 'xzxXZxZxzX', '2025-07-06 08:32:45'),
(3, 10, 10, 5, 'qeqeqqeqeqeqe', '2025-07-11 14:01:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_hotel`
--

CREATE TABLE `tb_hotel` (
  `id_hotel` varchar(30) NOT NULL,
  `nama_hotel` varchar(100) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kabupaten` varchar(50) DEFAULT NULL,
  `kecamatan` varchar(50) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `latitude` varchar(32) DEFAULT NULL,
  `longitude` varchar(32) DEFAULT NULL,
  `harga_hotel` varchar(50) DEFAULT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `fasilitas` text DEFAULT NULL,
  `gambar` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_hotel`
--

INSERT INTO `tb_hotel` (`id_hotel`, `nama_hotel`, `provinsi`, `kabupaten`, `kecamatan`, `alamat`, `latitude`, `longitude`, `harga_hotel`, `kontak`, `fasilitas`, `gambar`, `deskripsi`, `tanggal_upload`) VALUES
('HTL684b6503351f9', 'Swiss-Belhotel Danum Palangka Raya', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jalan Tjilik Riwut KM.5 No.9, Bukit Tunggal, 73112', '-2.1780736', '113.8819948', 'Rp 600.000 - Rp 1.200.000 / malam', '087638638', 'Kolam renang, pusat kebugaran, Wi‑Fi, restoran, spa, antar-jemput bandara', '562253331.jpg,Cuplikan layar 2025-07-02 080833.png,Cuplikan layar 2025-07-02 080916.png,Cuplikan layar 2025-07-02 081000.png,Cuplikan layar 2025-07-02 081053.png', 'Hotel bintang 4 terkenal dengan Instagramable spot seperti kolam dan lounge, cocok untuk tamu bisnis maupun liburan keluarga', '2025-06-12 17:38:43'),
('HTL6853413c629e7', ' Best Western Batang Garing Hotel', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. RTA Milono No.Km 1 5, Menteng', '-2.2227345', '113.9197695', 'Rp 700.000 - Rp 1.100.000 / malam', '082256375640', 'Fitness center, restoran, Wi‑Fi, parkir, meeting room', '24068289.avif,Cuplikan layar 2025-07-02 073514.png,Cuplikan layar 2025-07-02 073541.png,Cuplikan layar 2025-07-02 073627.png,Cuplikan layar 2025-07-02 073713.png,Cuplikan layar 2025-07-02 073742.png', 'Jaringan hotel terpercaya dengan fasilitas modern dan pelayanan ramah, ideal untuk perjalanan bisnis dan keluarga', '2025-06-18 16:44:12'),
('HTL6864877954ae5', 'Urbanview Hotel Diamond Palangkaraya', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Bukit Kaminting XV No.12, Palangka, 73112', '-2.2112785', '113.8898821', 'Rp 90.000 - Rp 300.000 / malam', '083344757727', 'AC, Wi‑Fi, LED TV, meja kerja, parkir, 24‑jam resepsionis, air mineral, hot shower', '21.jpg,Cuplikan layar 2025-07-02 090747.png,Cuplikan layar 2025-07-02 090807.png,Cuplikan layar 2025-07-02 090836.png', 'Budget hotel cocok untuk solo traveler dan backpacker; lokasi strategis dekat pusat kota, nyaman dan terjangkau', '2025-07-01 19:12:25'),
('HTL68648e1fee12c', 'Midtown Xpress Sampit', 'Kalimantan Tengah', 'Kotawaringin Timur', 'Mentawa Baru Hulu', 'Jl. MT. Haryono No.81, 74322', '-2.5411512', '112.9462881', 'Rp 374.000 - Rp 800.000 / malam', '05312067301', 'Wi‑Fi gratis, AC, TV, spa, restoran, bar, layanan kamar 24 jam, parkir gratis, antar-jemput bandara, lift, safe deposit box', 'Cuplikan layar 2025-07-02 093858.png,Cuplikan layar 2025-07-02 093921.png,Cuplikan layar 2025-07-02 093939.png,Cuplikan layar 2025-07-02 094003.png,Cuplikan layar 2025-07-02 094027.png', 'Hotel bintang 2 modern dengan rating “Very Good” (8,5/10), cocok untuk liburan dan bisnis. Menyediakan restoran dengan menu nusantara dan bar, ideal untuk keluarga maupun tamu bisnis', '2025-07-01 19:40:47'),
('HTL686c50db40812', 'Hotel Neo Palma', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Tjilik Riwut Bundaran Besar No.Km. 1 No. 1, 73111', '-2.2064907', '113.9152945', 'Rp 450.000 - Rp 600.000 / malam', '(0536) 3221555', 'AC, Wi-Fi, restoran, ruang meeting, lift, parkir luas', 'Cuplikan layar 2025-07-08 065550.png,Cuplikan layar 2025-07-08 065610.png,Cuplikan layar 2025-07-08 065623.png,Cuplikan layar 2025-07-08 065639.png,Cuplikan layar 2025-07-08 065658.png,Cuplikan layar 2025-07-08 065715.png', 'Hotel modern di pusat kota, dekat pusat perbelanjaan dan bandara.', '2025-07-07 16:57:31'),
('HTL686c52df92217', 'Urbanview Hotel Bundaran Besar Palangkaraya', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Cakra Buana, Palangka,  73112', '-2.2038379', '113.9071647', 'Rp 150.000 - Rp 200.000 / malam', '081258477014', 'AC, Wi-Fi, kamar mandi dalam', 'Cuplikan layar 2025-07-08 070144.png,Cuplikan layar 2025-07-08 070657.png,Cuplikan layar 2025-07-08 070709.png,Cuplikan layar 2025-07-08 070722.png,Cuplikan layar 2025-07-08 070735.png', 'Hotel modern budget di pusat kota.', '2025-07-07 17:06:07'),
('HTL686c577742888', 'Grand Kecubung Hotel', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Arut Selatan', 'Jl. Domba No.1, Mendawai, 74111', '-2.6826566', '111.6284455', 'Rp 600.000 - Rp 800.000 / malam', '053221211', 'Kolam renang, ruang pertemuan, spa, AC, Wi-Fi, restoran, ballroom, layanan kamar', 'Cuplikan layar 2025-07-08 071815.png,Cuplikan layar 2025-07-08 072044.png,Cuplikan layar 2025-07-08 072112.png,Cuplikan layar 2025-07-08 072136.png,Cuplikan layar 2025-07-08 072152.png', 'Hotel besar untuk bisnis dan wisata, dekat bandara dan pusat kota.', '2025-07-07 17:25:43'),
('HTL686c661a23fc0', 'Aquarius Boutique Hotel Sampit', 'Kalimantan Tengah', 'Kotawaringin Timur', 'Mentaya Hulu', 'Jl. Jenderal Sudirman No.Km. 2, RW.5,  74322', '-2.5375455', '112.9173446', 'Rp 500.000 - Rp 1.300.000 / malam', '05312067000', 'AC, air panas, TV, Wi-Fi, restoran sederhana, area parkir, layanan resepsionis', 'Cuplikan layar 2025-07-08 082556.png,Cuplikan layar 2025-07-08 082620.png,Cuplikan layar 2025-07-08 082634.png,Cuplikan layar 2025-07-08 082701.png', 'Hotel dengan fasilitas standar di pusat Sampit.', '2025-07-07 18:28:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kritik_saran`
--

CREATE TABLE `tb_kritik_saran` (
  `id_kritik_saran` int(11) NOT NULL,
  `id_wisata` varchar(20) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT '1–5',
  `komentar` text NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Kritik, saran, dan rating pengguna per objek wisata';

--
-- Dumping data untuk tabel `tb_kritik_saran`
--

INSERT INTO `tb_kritik_saran` (`id_kritik_saran`, `id_wisata`, `id_pengguna`, `rating`, `komentar`, `tanggal`) VALUES
(23, 'WIS683b87e196152', 10, 5, 'sqqqqsqqqs', '2025-06-20 10:24:55'),
(24, 'WIS683b87e196152', 10, 5, 'saSSAsSAsS', '2025-06-28 09:03:10'),
(26, 'WIS683b86d27282b', 10, 2, 'asasaasasasa', '2025-07-05 15:37:53'),
(28, 'WIS683b86d27282b', 10, 5, 'Tolong diperbaiki Fasilitasnya', '2025-07-30 00:17:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kritik_saran_event`
--

CREATE TABLE `tb_kritik_saran_event` (
  `id_kritik_saran_event` int(11) NOT NULL,
  `id_event` varchar(20) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT '1–5',
  `komentar` text NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_kritik_saran_event`
--

INSERT INTO `tb_kritik_saran_event` (`id_kritik_saran_event`, `id_event`, `id_pengguna`, `rating`, `komentar`, `tanggal`) VALUES
(1, 'EVT68400ea373ffd', 10, 5, 'ssadsadsadsasdadsas', '2025-06-29 04:07:07'),
(2, 'EVT686f8a002843f', 10, 5, 'sangat asik jalan sehatnya', '2025-07-11 01:25:49'),
(3, 'EVT686f8a002843f', 10, 5, 'sangat asik jalan sehatnyaddd', '2025-07-11 01:37:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kritik_saran_hotel`
--

CREATE TABLE `tb_kritik_saran_hotel` (
  `id_kritik_saran_hotel` int(11) NOT NULL,
  `id_hotel` varchar(20) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `komentar` text NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_kritik_saran_hotel`
--

INSERT INTO `tb_kritik_saran_hotel` (`id_kritik_saran_hotel`, `id_hotel`, `id_pengguna`, `rating`, `komentar`, `tanggal`) VALUES
(2, 'HTL686c661a23fc0', 10, 5, 'Hotellnya sangat bagus pelayanannya', '2025-07-11 04:32:42'),
(3, 'HTL686c661a23fc0', 10, 5, 'Hotellnya sangat bagus pelaasasasaayanannya', '2025-07-11 04:37:07'),
(4, 'HTL6853413c629e7', 10, 5, 'pelayanan sangat baik', '2025-07-11 04:38:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kritik_saran_kuliner`
--

CREATE TABLE `tb_kritik_saran_kuliner` (
  `id_kritik_saran` int(11) NOT NULL,
  `id_kuliner` varchar(20) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT '1–5',
  `komentar` text NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `tb_kritik_saran_kuliner`
--

INSERT INTO `tb_kritik_saran_kuliner` (`id_kritik_saran`, `id_kuliner`, `id_pengguna`, `rating`, `komentar`, `tanggal`) VALUES
(12, 'KUL683c70e4710a6', 10, 5, 'enak benrlkl', '2025-06-20 03:39:02'),
(13, 'KUL683c70e4710a6', 10, 5, 'enak benrlkl', '2025-06-20 03:39:05'),
(20, 'KUL68677b14c102f', 10, 5, 'Sangat enak makanannya', '2025-07-11 01:25:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kritik_saran_oleh2`
--

CREATE TABLE `tb_kritik_saran_oleh2` (
  `id` int(11) NOT NULL,
  `id_oleh2` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT '1–5',
  `komentar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tb_kritik_saran_oleh2`
--

INSERT INTO `tb_kritik_saran_oleh2` (`id`, `id_oleh2`, `id_pengguna`, `rating`, `komentar`, `tanggal`) VALUES
(6, 3, 10, 5, 'sfsfsfsdfsfdfs', '2025-06-20 03:38:37'),
(7, 4, 10, 5, 'asasasaa', '2025-07-03 02:21:57'),
(8, 4, 10, 5, 'asasasaa', '2025-07-03 02:22:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kuliner`
--

CREATE TABLE `tb_kuliner` (
  `id_kuliner` varchar(20) NOT NULL,
  `nama_kuliner` varchar(100) NOT NULL,
  `provinsi` varchar(100) NOT NULL,
  `kabupaten` varchar(100) NOT NULL,
  `kecamatan` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `harga_range` varchar(50) DEFAULT NULL,
  `jam_operasional` varchar(100) DEFAULT NULL,
  `menu` text DEFAULT NULL,
  `special_menu` varchar(100) DEFAULT NULL,
  `gambar` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp(),
  `latitude` varchar(32) DEFAULT NULL,
  `longitude` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `tb_kuliner`
--

INSERT INTO `tb_kuliner` (`id_kuliner`, `nama_kuliner`, `provinsi`, `kabupaten`, `kecamatan`, `alamat`, `harga_range`, `jam_operasional`, `menu`, `special_menu`, `gambar`, `deskripsi`, `tanggal_upload`, `latitude`, `longitude`) VALUES
('KUL683c3fcbb8a24', 'Rumah Makan Samba', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut,', 'Jl. RTA Milono No.15, Langkai, 73111', '25000 - 75000', 'Senin - Minggu, 09:00 - 16:00', 'Wadi patin, Juhu umbut rotan, Mandai goreng, Ikan bakar, Ayam kampung', 'Wadi Patin, Juhu Umbut Rotan', 'kuliner-3.jpg', 'Salah satu tempat makan khas Dayak di Palangka Raya, terkenal dengan masakan tradisional sungai dan menu sayur lokal seperti juhu umbut rotan. Cocok untuk wisata kuliner khas Kalteng.', '2025-06-01 13:55:55', '-2.2221355', '113.919787'),
('KUL683c70e4710a6', 'La Sarai Cafe & Resto', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut', 'l. M.H. Thamrin No.7 Blok.D, Menteng 74874', '25000 - 75000', 'Senin - Minggu, 11:00 - 22:00', 'Ikan bakar, Ayam taliwang, Nasi goreng, Spaghetti, Sate ayam, Es kopi susu, juhu rotan, udang galah, tanak ikan ', 'Ikan Bakar, juhu rotan, udang galah, tanak ikan ', '17.jpg,Cuplikan layar 2025-07-08 060808.png,Cuplikan layar 2025-07-08 060823.png,14.jpg,15.jpg', 'Tempat makan dan nongkrong favorit keluarga serta anak muda, mengusung menu khas lokal dan nusantara, dengan spot outdoor dan live music di Palangka Raya.', '2025-06-01 17:25:24', '-2.2133181', '113.9138332'),
('KUL6864940f8f21b', 'Rumah Makan Bang Jali', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. S. Parman No.31, 73112', '20000 - 50000', 'Senin - Minggu, 08:00 - 15:00', 'Sop kikil, Gulai kambing/daging, Sate kambing, Sop kambing, sate ayam', ' Sate & gulai kambing konsistensi lembut, rempah sempurna', 'Cuplikan layar 2025-07-02 100341.png,Cuplikan layar 2025-07-02 100414.png,Cuplikan layar 2025-07-02 100437.png,Cuplikan layar 2025-07-02 100501.png', 'Legendaris sejak 1980, ramai pengunjung, pelayanan cepa', '2025-07-02 04:06:07', '-2.2015241', '113.9162231'),
('KUL68677b14c102f', 'Kampung Lauk', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut', 'Jl. Bukit Rawi Km No.2, Pahandut Seberang 73111', '25000 - 50000', 'Senin - Minggu, 09:00 - 21:00', ' patin, nila, lais, papuyu, ayam bakar/goreng, sayur asam, sayur santan, sayur rotan, udang galah', 'Ikan bakar patin, nila, lais, papuyu, gurame', 'img20190129122253-largejpg.jpg,img20190129122303-largejpg.jpg', 'Restoran tradisional tepi sungai dengan gazebo kayu dan suasana alami, menyajikan ikan air tawar segar yang bisa dipilih sendiri. Menawarkan menu bakaran lezat, lauk khas Kalteng, dan pemandangan sungai yang menenangkan—sempurna untuk santai bersama keluarga atau rombongan.', '2025-07-04 08:56:20', '-2.197824', '113.930852'),
('KUL686b5a8963896', 'Tepi Laut', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Arut Selatan', 'Gg. Remaja, Raja,  74112', '25000 - 40000', 'Senin - Minggu, 15:00 - 22:00', 'Ikan bakar laut, cumi goreng tepung, kepiting saus padang, sambal dabu-dabu, nasi panas', 'Ikan bakar, cumi goreng tepung', 'Cuplikan layar 2025-07-07 094927.png,Cuplikan layar 2025-07-07 132409.png,Cuplikan layar 2025-07-07 132430.png', 'Tempat makan seafood tepi sungai yang menyajikan ikan laut bakar, cumi goreng tepung, dan kepiting saus padang. Lokasinya strategis dan nyaman, dengan suasana terbuka yang sejuk cocok untuk makan malam santai bersama keluarga.', '2025-07-07 07:26:33', '-2.6740084', '111.6310441'),
('KUL686b5f4037830', 'Rumah Makan Khas Pangkalan Bun', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Arut Selatan', 'Jl. Duku, Pasir Panjang, 74117', '20000 - 50000', 'Senin - Minggu, 09:00 - 19:00', 'Ikan bakar/manis, Ikan Goreng, sayur asam, santan rampak rampuk, Oseng lembiding, Bening, sambal mentah, sambal goreng', 'Ikan bakar, sayur asam, sambal', 'Cuplikan layar 2025-07-07 093701.png,Cuplikan layar 2025-07-08 060250.png,Cuplikan layar 2025-07-08 060308.png,Cuplikan layar 2025-07-08 060332.png', 'Rumah makan ini menyajikan aneka masakan khas Kalimantan Tengah dengan sentuhan Melayu lokal. Suasananya sederhana namun nyaman, cocok untuk makan bersama keluarga, rombongan wisata, atau tamu resmi', '2025-07-07 07:46:40', '-2.7316615', '111.6576778'),
('KUL686b652668aa9', 'Soto Kwali Sampit', 'Kalimantan Tengah', 'Kotawaringin Timur', 'Mentawa', 'Jl. MT. Haryono Bar., Mentawa Baru Hulu, 74311', '10000 - 35000', 'Senin - Minggu, 06:00 - 22:00', 'Soto Daging, Soto Ayam, Soto Babat, sate Kikil, Soto Campur (Daging, Babat, Kikil), Sop Buntut, Sop Iga, Sop Kikil, Rawon, Nasi Pecel', ' Sop Buntut, Sop Iga, Sop Kikil', 'Cuplikan layar 2025-07-07 135241.png,Cuplikan layar 2025-07-07 141513.png,Cuplikan layar 2025-07-07 141533.png', 'Soto khas dengan kuah bening & aroma rempah kuat, dimasak dalam kwali tanah liat.', '2025-07-07 08:11:50', '-2.5409937', '112.9548067'),
('KUL686c3a911f1b5', 'Kuliner Tempoe Doeloe Serba Kandas', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Kutilang No.54, 74874', '25000 - 50000', 'Senin - Sabtu, 09:00 - 21:00', 'Ikan lais kandas, ayam kandas, telor kandas, sambal tempoyak, nasi uduk, lalapan, tumis, kandas kecombrang, udang goreng, juhu rimbang patin, ikang salung, kandas pare, kandas daun juna', ' lalapan, tumis, kandas kecombrang, udang goreng, juhu rimbang patin, ikang salung, kandas pare, kan', 'Cuplikan layar 2025-07-07 153506.png,Cuplikan layar 2025-07-08 060526.png,Cuplikan layar 2025-07-08 060542.png,Cuplikan layar 2025-07-08 060557.png,Cuplikan layar 2025-07-08 060617.png', 'Warung hits khas Kalimantan dengan konsep tempo dulu dan rasa sambal khas lokal.', '2025-07-07 23:22:25', '-2.1993228', '113.9017298'),
('KUL686c3f6b1f31c', 'Rumah Tjilik Riwut Gallery & Resto', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Jend. Sudirman No.1, 73111', '50000 - 75000', 'Senin - Sabtu, 12:00 - 21:00', 'ayam bakar Dayak, nasi goreng sambal terasi, kopi lokal, juhu umbut rotan ikan baung, baram madu, tumis kalakai, sayur umbut rotan', 'ayam bakar dayak, juhu umbut rotan ikan baung, baram madu, tumis kalakai, sayur umbut rotan', 'Cuplikan layar 2025-07-08 053612.png,Cuplikan layar 2025-07-08 053639.png,Cuplikan layar 2025-07-08 053706.png,Cuplikan layar 2025-07-08 053734.png', 'Resto bergaya heritage, menyatu dengan galeri sejarah Tjilik Riwut. Cocok untuk tamu & wisatawan.', '2025-07-07 23:43:07', '-2.2071621', '113.9209321');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_motor`
--

CREATE TABLE `tb_motor` (
  `id_motor` int(11) NOT NULL,
  `nama_motor` varchar(100) NOT NULL,
  `jenis_kendaraan` enum('Motor','Mobil') NOT NULL,
  `merk` varchar(50) NOT NULL,
  `tahun` year(4) NOT NULL,
  `warna` varchar(30) NOT NULL,
  `harga_sewa` decimal(10,2) NOT NULL,
  `kecamatan` varchar(50) NOT NULL,
  `kabupaten` varchar(50) NOT NULL,
  `provinsi` varchar(50) NOT NULL,
  `fasilitas` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `nama_kontak` varchar(100) NOT NULL,
  `no_telepon` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_motor`
--

INSERT INTO `tb_motor` (`id_motor`, `nama_motor`, `jenis_kendaraan`, `merk`, `tahun`, `warna`, `harga_sewa`, `kecamatan`, `kabupaten`, `provinsi`, `fasilitas`, `gambar`, `tanggal_upload`, `nama_kontak`, `no_telepon`) VALUES
(5, 'Honda Beat', 'Motor', 'Honda', 2022, 'biru', '150000.00', 'Jekan Raya', 'Kota Palangka Raya', 'Kalimantan Tengah', 'Helm, Jas hujan, STNK Lengkap, BBM Penuh', '68660ce5c31fa.jpeg', '2025-07-02 22:53:57', '', ''),
(7, 'Scoopy', 'Motor', 'Honda', 2020, 'Merah', '150000.00', 'Dusun Selatan', 'Barito Selatan', 'Kalimantan Tengah', 'Helm, Jas hujan, STNK Lengkap, BBM full', '6866154d2f157.jpg', '2025-07-02 23:29:49', '', ''),
(8, 'Toyota Avanza	 ', 'Mobil', 'Toyota	', 2022, 'Putih', '400000.00', 'Pahandut', 'Kota Palangka Raya', 'Kalimantan Tengah', 'STNK Lengkap, BBM Full', '68661a9f4a5d6.jpg', '2025-07-02 23:52:31', '', ''),
(9, 'Avanza G', 'Mobil', 'Toyota ', 2022, 'Hitam', '400000.00', 'Bukit Batu', 'Kota Palangka Raya', 'Kalimantan Tengah', 'STNK Lengkap, BBM Full', '6867803638fde.png', '2025-07-04 01:18:14', 'Rizal', '082256375640'),
(10, 'Beat Street', 'Motor', 'Honda	', 2020, 'Putih', '130000.00', 'Kapuas Hilir', 'Kapuas', 'Kalimantan Tengah', 'Helm, Jas Hujan, STNK Lengkap, BBm Full', '686b0dbf2348a.jpg', '2025-07-06 17:55:58', 'Deddy', '082233445522'),
(11, 'Fortuner VRZ', 'Mobil', 'Toyota ', 2023, 'Hitam Mica', '600000.00', 'Jekan Raya', 'Kota Palangka Raya', 'Kalimantan Tengah', 'STNK Lengkap', '686b0e5ed435d.png', '2025-07-06 18:01:34', 'Praz', '082233441123'),
(12, 'Rush TRD', 'Mobil', 'Toyota ', 2022, 'Merah', '750000.00', 'Pahandut', 'Kota Palangka Raya', 'Kalimantan Tengah', 'STNK Lengkap', '686b0ee3d8238.png', '2025-07-06 18:03:47', 'Mamat', '082736618273'),
(13, 'Xenia R			', 'Mobil', 'Daihatsu', 2020, 'Silver', '600000.00', 'Dusun Selatan', 'Barito Selatan', 'Kalimantan Tengah', 'STNK Lengkap', '686b1023ebc67.jpg', '2025-07-06 18:09:07', 'Midi', '082256483045');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_oleh2`
--

CREATE TABLE `tb_oleh2` (
  `id_oleh2` int(11) NOT NULL,
  `nama_toko` varchar(255) NOT NULL,
  `provinsi` varchar(100) NOT NULL,
  `kabupaten` varchar(100) NOT NULL,
  `kecamatan` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `harga_range` varchar(50) DEFAULT NULL,
  `jam_operasional` varchar(50) DEFAULT NULL,
  `barang_dijual` text DEFAULT NULL,
  `gambar` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_upload` datetime NOT NULL DEFAULT current_timestamp(),
  `latitude` varchar(32) DEFAULT NULL,
  `longitude` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `tb_oleh2`
--

INSERT INTO `tb_oleh2` (`id_oleh2`, `nama_toko`, `provinsi`, `kabupaten`, `kecamatan`, `alamat`, `harga_range`, `jam_operasional`, `barang_dijual`, `gambar`, `deskripsi`, `tanggal_upload`, `latitude`, `longitude`) VALUES
(3, 'PERMATA ZAHRA', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut', 'Jl. Batam, Pahandut 74874', '5000 - 200000', 'Senin - Minggu, 08:00 - 21:00', 'Kerajinan Rotan, Perahi Getah Nyatu, Kain Benang Batik Kalteng, Kaos Dayak, Aksesories Batu, Mandau, Tameng, Makanan khas Kalteng, Dll', 'Toko-PERMATA-ZAHRA-Pusat-Oleholeh-PalangkarayaKalteng-tXd.webp,Cuplikan layar 2025-06-04 164544.png,Cuplikan layar 2025-06-04 164616.png', 'Toko menjual oleh2 khas Kalimantan Tengah', '2025-06-04 10:47:15', '-2.2065002', '113.9377776'),
(4, 'Souvenir Fauzi²', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut', 'Jl. Batam No.59,  74874', '5000 - 200000', 'Senin - Minggu, 07:00 - 19:30', 'Makanan tradisional,  camilan khas Kalteng, Kerajinan tangan, souvenir lokal', 'Cuplikan layar 2025-07-02 122237.png,Cuplikan layar 2025-07-02 122327.png,Cuplikan layar 2025-07-02 122354.png,Cuplikan layar 2025-07-02 122434.png,Cuplikan layar 2025-07-02 122452.png,Cuplikan layar 2025-07-02 122525.png', 'souvenir dan kerajinan khas Dayak, cocok untuk oleh-oleh budaya dengan kualitas baik', '2025-07-02 06:25:50', '-2.206462', '113.937862'),
(5, 'Toko Oleh-Oleh Rumah Kelakai F108', 'Kalimantan Tengah', 'Kapuas', 'Selat', 'Jl. Meranti No.7,  73516', '30000 - 100000', 'Senin - Minggu, 08:00 - 16:00', 'Akar pinang kelakai, keripik ikan lais, keripik ikan saluang, keripik pisang, tapai ansterdam, aneka kerajinan, dan lain-lain', 'Cuplikan layar 2025-07-03 223100.png,Cuplikan layar 2025-07-03 223129.png,Cuplikan layar 2025-07-03 223156.png,Cuplikan layar 2025-07-03 223232.png,Cuplikan layar 2025-07-03 223323.png,Cuplikan layar 2025-07-03 223356.png', 'Toko yang telah berdiri sejak 2002 yang menjual aneka keripik, kerajinan dan lain lain', '2025-07-03 16:45:57', '-2.997366', '114.3797382'),
(6, 'Souvenir Erwin', 'Kalimantan Tengah', 'Kapuas', 'Kapuas Hilir', 'Jl. Kapuas Seberang, Hampatung, 73581', '50000 - 1000000', 'Senin - Minggu, 07:00 - 22:00', 'Mandau, Tameng, Kerajinan Tas, Anyaman, Miniatur Kapal, dan lain - lain', 'Cuplikan layar 2025-07-03 225620.png,Cuplikan layar 2025-07-03 225704.png,Cuplikan layar 2025-07-03 225731.png,Cuplikan layar 2025-07-03 225751.png,Cuplikan layar 2025-07-03 225817.png,Cuplikan layar 2025-07-03 225838.png,Cuplikan layar 2025-07-03 225859.png,Cuplikan layar 2025-07-03 225943.png,Cuplikan layar 2025-07-03 230012.png,Cuplikan layar 2025-07-03 230036.png', 'Menjual Khas Dayak ', '2025-07-03 17:06:15', '-3.0099156', '114.40656'),
(7, 'Galeri Dekranasda Kalteng', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. G. Obos, Menteng, 74874', '20000 - 500000', 'Senin - Kamis, 09:00 - 21:00', 'Batik khas Dayak, kerajinan manik, tas rotan, ukiran', 'Cuplikan layar 2025-07-08 150839.png,Cuplikan layar 2025-07-08 150757.png,Cuplikan layar 2025-07-08 150816.png', 'Pusat produk UMKM & kerajinan khas Dayak, cocok untuk oleh-oleh berkualitas.', '2025-07-08 09:09:06', '-2.2193052', '113.9127582'),
(8, 'Batik Benang Bintik Galeri', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut', 'Jl. Pinus Permai II No.23, Panarung, 73111', '100000 - 500000', 'Senin - Minggu, 08:00 - 22:00', 'Batik Dayak benang bintik, kain tenun, baju batik pria/wanita, tas', 'Cuplikan layar 2025-07-08 152115.png,Cuplikan layar 2025-07-08 152639.png,Cuplikan layar 2025-07-08 152710.png', 'Galeri batik khas Dayak eksklusif dan asli buatan tangan.', '2025-07-08 09:28:07', '-2.2330493', '113.936164'),
(9, 'Kawal Pusat Oleh-Oleh Pangkalan Bun', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Arut Selatan', 'Jl. HM. Rafi\'i, Madurejo,  74181', '10000 - 300000', 'Senin - Minggu, 08:00 - 21:00', 'Amplang ikan tenggiri, madu hutan, keripik kelakai, batik Dayak, tas rotan, manik, ukiran kayu, keripik singkong', 'Cuplikan layar 2025-07-08 194557.png,Cuplikan layar 2025-07-09 005012.png,Cuplikan layar 2025-07-09 005038.png,Cuplikan layar 2025-07-09 005103.png,Cuplikan layar 2025-07-09 005127.png', 'Tempat oleh-oleh paling lengkap dan nyaman di Pangkalan Bun. Produk makanan khas Kalimantan Tengah dan kerajinan lokal tersedia dengan harga bersahabat. Tempat bersih, pelayanan ramah.', '2025-07-08 13:43:22', '-2.7106288', '111.6463998'),
(10, 'Toko Souvenir Istana Kecubung', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Arut Selatan', 'Jl. Pangeran Antasari, Raja,  74112', '60000 - 1000000', 'Senin - Minggu, 08:00 - 21:59', 'Aksesori & perhiasan batu kecubung (cincin, gelang, liontin), bongkahan batu, dekor kristal, tasbih dari kecubung, manik-manik, ukiran kayu pukaha, kaos Dayak, mandau & tameng mini.', 'Cuplikan layar 2025-07-09 005737.png,Cuplikan layar 2025-07-09 010102.png,Cuplikan layar 2025-07-09 010145.png,Cuplikan layar 2025-07-09 010211.png,Cuplikan layar 2025-07-09 010230.png', 'Toko souvenir paling lengkap di Pangkalan Bun untuk berbagai produk dari batu kecubung asli dan kerajinan lokal Kalimantan. Cocok untuk oleh-oleh bernilai estetika dan budaya.', '2025-07-08 19:00:03', '-2.67359', '111.634407');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_pengguna`
--

CREATE TABLE `tb_pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_pengguna` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(35) NOT NULL,
  `level` enum('Administrator','pengguna','','') NOT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `tb_pengguna`
--

INSERT INTO `tb_pengguna` (`id_pengguna`, `nama_pengguna`, `username`, `password`, `level`, `alamat`, `no_hp`) VALUES
(7, 'admin1', 'admin1', '202cb962ac59075b964b07152d234b70', 'Administrator', 'buntok', '082256375640'),
(10, 'midi', 'midi', '202cb962ac59075b964b07152d234b70', 'pengguna', NULL, NULL),
(11, 'madan', 'madan', '202cb962ac59075b964b07152d234b70', 'pengguna', NULL, NULL),
(12, 'bintang', 'bintang', '202cb962ac59075b964b07152d234b70', 'pengguna', 'Jl. Begau Raya', '082736373612');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_promosi`
--

CREATE TABLE `tb_promosi` (
  `id_promosi` int(11) NOT NULL,
  `judul_promosi` varchar(100) DEFAULT NULL,
  `jenis_promosi` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_promosi`
--

INSERT INTO `tb_promosi` (`id_promosi`, `judul_promosi`, `jenis_promosi`, `deskripsi`, `lokasi`, `tanggal_mulai`, `tanggal_selesai`, `harga`, `kontak`, `gambar`, `tanggal_upload`) VALUES
(1, 'Taman Nasional Tanjung Puting', 'Sewa Konten Kreator', 'agar menarik pengunjung', 'jl. urbanus marcun', '2025-06-01', '2025-06-06', 5000000, '082256354779', '', '2025-06-04 14:14:33'),
(2, 'Museum Balanga', 'Pembuatan Baliho', 'untuk meningkatkan daya tarik', 'jl. urbanus marcun', '2025-07-30', '2025-08-05', 200000, '081258477014', '', '2025-07-29 14:27:05'),
(3, 'Meningkat daya tarik ', 'Sewa Konten Kreator', 'agar menarik minat ', 'jl. uria mapas', '2025-08-08', '2025-08-09', 300000, '(0536) 3221552', '', '2025-07-29 14:28:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_transaksi` varchar(20) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_wisata` varchar(20) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp(),
  `jumlah_tiket` int(11) NOT NULL,
  `total_bayar` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','cancelled','ditolak') NOT NULL DEFAULT 'pending',
  `bukti_pembayaran` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_transaksi`, `id_pengguna`, `id_wisata`, `tanggal`, `jumlah_tiket`, `total_bayar`, `status`, `bukti_pembayaran`) VALUES
('WIS686096b35b826', 10, 'WIS683b88dda6202', '2025-06-29 09:28:19', 1, '7000.00', 'approved', 'WIS686096b35b826_1751160499_13.jpeg'),
('WIS6872f958584d0', 10, 'WIS683b87e196152', '2025-07-13 08:10:00', 1, '10000.00', 'approved', 'WIS6872f958584d0_1752365400_211 (2).jpg'),
('WIS6872ff5bb072d', 10, 'WIS683b87e196152', '2025-07-13 08:35:39', 1, '10000.00', 'cancelled', 'WIS6872ff5bb072d_1752366939_1.jpg'),
('WIS6882a4f616bf3', 10, 'WIS683b86d27282b', '2025-07-25 05:26:14', 1, '20000.00', 'ditolak', 'WIS6882a4f616bf3_1753392374_61c161511efb8.jpg'),
('WIS688950ca673d0', 10, 'WIS683b86d27282b', '2025-07-30 06:52:58', 2, '40000.00', 'pending', 'WIS688950ca673d0_1753829578_Informasi Oleh2.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_wisata`
--

CREATE TABLE `tb_wisata` (
  `id_wisata` varchar(20) NOT NULL,
  `kategori` varchar(20) NOT NULL,
  `nama_wisata` varchar(100) NOT NULL,
  `provinsi` varchar(50) NOT NULL,
  `kabupaten` varchar(50) NOT NULL,
  `kecamatan` varchar(50) NOT NULL,
  `alamat` varchar(200) NOT NULL,
  `harga_tiket` varchar(20) NOT NULL,
  `jam_operasional` varchar(50) NOT NULL,
  `fasilitas` varchar(100) NOT NULL,
  `kondisi_jalan` varchar(100) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `tanggal_upload` date NOT NULL,
  `latitude` varchar(32) DEFAULT NULL,
  `longitude` varchar(32) DEFAULT NULL,
  `harga_makanan_min` int(11) DEFAULT NULL,
  `harga_makanan_max` int(11) DEFAULT NULL,
  `harga_minuman_min` int(11) DEFAULT NULL,
  `harga_minuman_max` int(11) DEFAULT NULL,
  `biaya_sewa` varchar(100) DEFAULT NULL,
  `estimasi_biaya` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tb_wisata`
--

INSERT INTO `tb_wisata` (`id_wisata`, `kategori`, `nama_wisata`, `provinsi`, `kabupaten`, `kecamatan`, `alamat`, `harga_tiket`, `jam_operasional`, `fasilitas`, `kondisi_jalan`, `gambar`, `deskripsi`, `tanggal_upload`, `latitude`, `longitude`, `harga_makanan_min`, `harga_makanan_max`, `harga_minuman_min`, `harga_minuman_max`, `biaya_sewa`, `estimasi_biaya`) VALUES
('WIS683b86d27282b', 'Wisata Alam', 'Taman Nasional Tanjung Puting', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Kumai', 'Pangkalan Bun', '20000', 'Senin - Minggu, 07:00 - 17:00', 'Penginapan, Pemandu Wisata, Toilet', 'Aspal & Tanah', 'shop.jpg,download.jpg,images.jpg,istockphoto-807541052-612x612.jpg', 'Taman nasional terbesar di Kalteng, habitat utama orangutan liar.', '2025-06-01', '-3.078659', '112.015615', 25000, 45000, 7000, 20000, 'Kapal Rp 500.000 hingga Rp 1.500.000 per hari', '1.000.000 per orang'),
('WIS683b87e196152', 'Wisata Sejarah', 'Museum Balanga', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Jekan Raya', 'Jl. Tjilik Riwut No.2', '10000', 'Senin - Sabtu, 09:00 - 15:00', 'Ruang Pameran, Kafe, Parki', 'aspal', 'zxzxz.jpg', 'Museum budaya Dayak, koleksi sejarah dan etnografi Kalteng.', '2025-06-01', '-2.195626', '113.901848', 15000, 25000, 5000, 12000, '', 'Rp50.000 – Rp100.000 Per orang'),
('WIS683b88dda6202', 'Wisata Sejarah', 'Keraton Kotawaringin', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Arut Selatan', 'Pangkalan Bun', '7000', 'Senin - Minggu, 08:00 - 17:00', 'Guide, Souvenir, Toilet', 'Aspal & Beton', '6.jpg,7.jpg,8.jpeg,9.jpg', 'Istana peninggalan Kesultanan Kotawaringin, ikon sejarah lokal.', '2025-06-01', '-2.675897', '111.632718', 18000, 30000, 5000, 12000, '', 'Rp150.000 – Rp250.000 Per orang'),
('WIS683b89e5a39f5', 'Wisata Religi', 'Makam Kyai Gede ', 'Kalimantan Tengah', 'Kotawaringin Barat', 'Kotawaringin Hilir', 'Desa Kotawaringin Hili', 'Gratis', 'Senin - Minggu, 08:00 - 17:00', 'Area Ziarah, Parkir, Istirahat', 'Aspal & Tanah', '10.jpg,26042021-Kiai-Gede.jpg', 'Makam ulama besar, ramai diziarahi.', '2025-06-01', '-2.4849742', '111.4438012', 7000, 15000, 3000, 10000, '', 'Rp10.000 – Rp50.000 Per orang'),
('WIS683bd029c81a8', 'Wisata Buatan', 'Taman Pasuk Kameloh', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Pahandut', 'Jl. S. Parman, Palangka Raya', 'Gratis', 'Senin - Minggu, 06:00 - 21:00', 'Jogging Track, Playground, Kafe, Toilet, Tempat Ibadah, Wifi Gratis', 'Aspal ', '1 (1).jpg,12.jpeg,13.jpeg', 'Taman kota yang terletak di tepi Sungai Kahayan, Palangka Raya. Memiliki area hijau yang asri, spot foto menarik seperti patung burung Enggang dan Masjid Terapung Darul Amin, serta dilengkapi fasilitas umum dan kuliner. Cocok untuk bersantai, berfoto, dan rekreasi keluarga', '2025-06-01', '-2.203524', '113.920525', 10000, 25000, 5000, 12000, 'Sewa mainan Rp15.000', 'Rp30.000 – Rp100.000 per orang'),
('WIS6867102d1ed62', 'Wisata Alam', 'Air Terjun Batu Mahasur', 'Kalimantan Tengah', 'Gunung Mas', 'Kurun', 'Jl. Damang Pijar, 74571', '10000', 'Senin - Minggu, 08:00 - 16:00', 'Gazebo (balai tempat duduk), Ruang Ganti, Toilet, Ruang Terbuka Hijau (RTH) & Panggung Hiburan', 'tanah dan berbatu', 'Cuplikan layar 2025-07-04 071803.png,Cuplikan layar 2025-07-04 071828.png,Cuplikan layar 2025-07-04 071854.png', 'Air Terjun Batu Mahasur adalah salah satu tempat wisata alam tersembunyi yang berada di tengah kawasan hutan Kalimantan Tengah. Keindahan alamnya yang masih alami, dengan aliran air yang jernih serta batu-batu besar di sekitarnya, menjadikan tempat ini cocok untuk wisata petualangan, berfoto, atau sekadar menikmati suasana tenang.', '2025-07-04', '-1.0897519', '113.8546795', 3000, 25000, 5000, 10000, '', 'Rp100.000 per orang'),
('WIS686714fe11c0e', 'Wisata Alam', 'Wisata Bukit Batu Pertapaan Tjilik Riwut', 'Kalimantan Tengah', 'Katingan', 'Katingan Hilir', 'Jl. Trans Kalimantan, Kasongan Lama, 74461', '5000', 'Senin - Minggu, 08:00 - 16:30', 'Parkir, toilet, mushola, warung makan, spot foto', 'aspal ', '1.jpg,Cuplikan layar 2025-07-04 072931.png,Cuplikan layar 2025-07-04 073003.png,211 (1).jpg,211 (2).jpg', 'Wisata alam dan spiritual di Kasongan, Kabupaten Katingan, Kalimantan Tengah. Bukit ini dikenal sebagai tempat bertapa Pahlawan Nasional Tjilik Riwut. Terdapat batu-batu besar yang dipercaya memiliki makna spiritual. Tersedia fasilitas seperti parkir, toilet, mushola, warung makan, dan spot foto. ', '2025-07-04', '-1.8951535', '113.467887', 5000, 20000, 5000, 10000, '', 'Rp.50.000 per orang'),
('WIS68672692703c4', 'Wisata Edukasi', 'Wisata Edukasi Berkuda', 'Kalimantan Tengah', 'Kota Palangka Raya', 'Sebangau', 'Jl. Surung, Kereng Bangkirai, 74874', '25000', 'Senin - Minggu, 08:00 - 17:00', ' Lapangan berkuda, pendamping, toilet', 'Aspal ', 'Cuplikan layar 2025-07-04 084618.png,Cuplikan layar 2025-07-04 084650.png,Cuplikan layar 2025-07-04 084727.png,Cuplikan layar 2025-07-04 084752.png,Cuplikan layar 2025-07-04 084826.png', 'Tempat rekreasi edukatif yang menawarkan pengalaman berkuda untuk anak-anak hingga dewasa. Berlokasi di Kereng Bangkirai, wisata ini mengajarkan dasar berkuda, cara merawat kuda, dan manfaat berkuda bagi tubuh. Cocok untuk wisata keluarga, pelajar, dan pecinta hewan.', '2025-07-04', '-2.298767', '113.917771', 5000, 25000, 5000, 10000, '', 'Rp.50.000 per orang');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tb_event`
--
ALTER TABLE `tb_event`
  ADD PRIMARY KEY (`id_event`);

--
-- Indeks untuk tabel `tb_feedback_sewa`
--
ALTER TABLE `tb_feedback_sewa`
  ADD PRIMARY KEY (`id_feedback`),
  ADD KEY `fk_feedback_motor` (`id_motor`),
  ADD KEY `fk_feedback_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `tb_hotel`
--
ALTER TABLE `tb_hotel`
  ADD PRIMARY KEY (`id_hotel`);

--
-- Indeks untuk tabel `tb_kritik_saran`
--
ALTER TABLE `tb_kritik_saran`
  ADD PRIMARY KEY (`id_kritik_saran`),
  ADD KEY `idx_kritik_wisata` (`id_wisata`),
  ADD KEY `idx_kritik_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `tb_kritik_saran_event`
--
ALTER TABLE `tb_kritik_saran_event`
  ADD PRIMARY KEY (`id_kritik_saran_event`),
  ADD KEY `idx_kritik_event` (`id_event`),
  ADD KEY `idx_kritik_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `tb_kritik_saran_hotel`
--
ALTER TABLE `tb_kritik_saran_hotel`
  ADD PRIMARY KEY (`id_kritik_saran_hotel`),
  ADD KEY `idx_hotel` (`id_hotel`),
  ADD KEY `idx_pengguna` (`id_pengguna`),
  ADD KEY `idx_tanggal` (`tanggal`);

--
-- Indeks untuk tabel `tb_kritik_saran_kuliner`
--
ALTER TABLE `tb_kritik_saran_kuliner`
  ADD PRIMARY KEY (`id_kritik_saran`),
  ADD KEY `idx_kuliner` (`id_kuliner`),
  ADD KEY `idx_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `tb_kritik_saran_oleh2`
--
ALTER TABLE `tb_kritik_saran_oleh2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kso_oleh2` (`id_oleh2`),
  ADD KEY `idx_kso_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `tb_kuliner`
--
ALTER TABLE `tb_kuliner`
  ADD PRIMARY KEY (`id_kuliner`);

--
-- Indeks untuk tabel `tb_motor`
--
ALTER TABLE `tb_motor`
  ADD PRIMARY KEY (`id_motor`);

--
-- Indeks untuk tabel `tb_oleh2`
--
ALTER TABLE `tb_oleh2`
  ADD PRIMARY KEY (`id_oleh2`);

--
-- Indeks untuk tabel `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  ADD PRIMARY KEY (`id_pengguna`);

--
-- Indeks untuk tabel `tb_promosi`
--
ALTER TABLE `tb_promosi`
  ADD PRIMARY KEY (`id_promosi`);

--
-- Indeks untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `idx_pengguna` (`id_pengguna`),
  ADD KEY `idx_wisata` (`id_wisata`);

--
-- Indeks untuk tabel `tb_wisata`
--
ALTER TABLE `tb_wisata`
  ADD PRIMARY KEY (`id_wisata`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tb_feedback_sewa`
--
ALTER TABLE `tb_feedback_sewa`
  MODIFY `id_feedback` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tb_kritik_saran`
--
ALTER TABLE `tb_kritik_saran`
  MODIFY `id_kritik_saran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `tb_kritik_saran_event`
--
ALTER TABLE `tb_kritik_saran_event`
  MODIFY `id_kritik_saran_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tb_kritik_saran_hotel`
--
ALTER TABLE `tb_kritik_saran_hotel`
  MODIFY `id_kritik_saran_hotel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tb_kritik_saran_kuliner`
--
ALTER TABLE `tb_kritik_saran_kuliner`
  MODIFY `id_kritik_saran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `tb_kritik_saran_oleh2`
--
ALTER TABLE `tb_kritik_saran_oleh2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `tb_motor`
--
ALTER TABLE `tb_motor`
  MODIFY `id_motor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `tb_oleh2`
--
ALTER TABLE `tb_oleh2`
  MODIFY `id_oleh2` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tb_promosi`
--
ALTER TABLE `tb_promosi`
  MODIFY `id_promosi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tb_feedback_sewa`
--
ALTER TABLE `tb_feedback_sewa`
  ADD CONSTRAINT `fk_feedback_motor` FOREIGN KEY (`id_motor`) REFERENCES `tb_motor` (`id_motor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_feedback_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_kritik_saran`
--
ALTER TABLE `tb_kritik_saran`
  ADD CONSTRAINT `fk_ks_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ks_wisata` FOREIGN KEY (`id_wisata`) REFERENCES `tb_wisata` (`id_wisata`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_kritik_saran_event`
--
ALTER TABLE `tb_kritik_saran_event`
  ADD CONSTRAINT `fk_event_kritik` FOREIGN KEY (`id_event`) REFERENCES `tb_event` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengguna_event` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_kritik_saran_hotel`
--
ALTER TABLE `tb_kritik_saran_hotel`
  ADD CONSTRAINT `tb_kritik_saran_hotel_ibfk_1` FOREIGN KEY (`id_hotel`) REFERENCES `tb_hotel` (`id_hotel`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_kritik_saran_hotel_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_kritik_saran_kuliner`
--
ALTER TABLE `tb_kritik_saran_kuliner`
  ADD CONSTRAINT `fk_ksk_kuliner` FOREIGN KEY (`id_kuliner`) REFERENCES `tb_kuliner` (`id_kuliner`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ksk_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_kritik_saran_oleh2`
--
ALTER TABLE `tb_kritik_saran_oleh2`
  ADD CONSTRAINT `fk_kso_oleh2` FOREIGN KEY (`id_oleh2`) REFERENCES `tb_oleh2` (`id_oleh2`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kso_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `fk_transaksi_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `tb_pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_wisata` FOREIGN KEY (`id_wisata`) REFERENCES `tb_wisata` (`id_wisata`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
