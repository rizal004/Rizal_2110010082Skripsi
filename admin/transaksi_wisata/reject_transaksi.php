<?php
// MyApp/reject_transaksi.php
include "inc/koneksi.php";

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';

// Cek apakah user adalah admin atau administrator
if ($ses_level !== 'admin' && $ses_level !== 'administrator') {
    echo "<script>
            alert('Akses ditolak! Hanya admin yang dapat menolak transaksi.');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
    exit;
}

// Ambil ID transaksi dari parameter GET
$id_transaksi = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($id_transaksi)) {
    echo "<script>
            alert('ID Transaksi tidak valid!');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
    exit;
}

// Cek apakah transaksi exists dan statusnya pending
$cek_query = "SELECT id_transaksi, status FROM tb_transaksi WHERE id_transaksi = ?";
$stmt_cek = $koneksi->prepare($cek_query);

if (!$stmt_cek) {
    echo "<script>
            alert('Database error: " . addslashes($koneksi->error) . "');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
    exit;
}

$stmt_cek->bind_param("s", $id_transaksi);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();

if ($result_cek->num_rows === 0) {
    echo "<script>
            alert('Transaksi tidak ditemukan!');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
    exit;
}

$data_transaksi = $result_cek->fetch_assoc();

// Cek apakah status masih pending
if ($data_transaksi['status'] !== 'pending') {
    echo "<script>
            alert('Hanya transaksi dengan status pending yang dapat ditolak! Status saat ini: " . ucfirst($data_transaksi['status']) . "');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
    exit;
}

// Update status transaksi menjadi ditolak
$update_query = "UPDATE tb_transaksi SET status = 'ditolak' WHERE id_transaksi = ?";
$stmt_update = $koneksi->prepare($update_query);

if (!$stmt_update) {
    echo "<script>
            alert('Database error: " . addslashes($koneksi->error) . "');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
    exit;
}

$stmt_update->bind_param("s", $id_transaksi);

if ($stmt_update->execute()) {
    // Berhasil menolak transaksi
    echo "<script>
            alert('Transaksi ID: $id_transaksi berhasil ditolak!');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
} else {
    // Gagal menolak transaksi
    echo "<script>
            alert('Gagal menolak transaksi! Error: " . addslashes($koneksi->error) . "');
            window.location.href = 'index.php?page=MyApp/transaksi_wisata';
          </script>";
}

$stmt_cek->close();
$stmt_update->close();
$koneksi->close();
?>