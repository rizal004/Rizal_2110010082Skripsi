<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Cek apakah user adalah admin
if ($ses_level === 'administrator' || $ses_level === 'admin') {
    $q = $koneksi->query("SELECT * FROM tb_transaksi_sewa WHERE id_transaksi='$id' AND status='pending'");
    
    if ($q && $q->num_rows) {
        $koneksi->query("UPDATE tb_transaksi_sewa SET status='approved' WHERE id_transaksi='$id'");
        echo "<script>alert('Transaksi berhasil diapprove!');window.location='?page=MyApp/transaksi_sewa';</script>";
    } else {
        echo "<script>alert('Transaksi tidak ditemukan atau sudah diproses!');window.location='?page=MyApp/transaksi_sewa';</script>";
    }
} else {
    echo "<script>alert('Akses ditolak! Hanya admin yang dapat approve transaksi.');window.location='?page=MyApp/transaksi_sewa';</script>";
}
?>