<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Hanya admin/administrator yang bisa hapus!
if ($ses_level === 'admin' || $ses_level === 'administrator') {
    // Opsional: hapus file bukti pembayaran jika ada
    $q = $koneksi->query("SELECT bukti_pembayaran FROM tb_transaksi WHERE id_transaksi='$id'");
    if ($q && $d = $q->fetch_assoc()) {
        if (!empty($d['bukti_pembayaran']) && file_exists('uploads/bukti/' . $d['bukti_pembayaran'])) {
            unlink('uploads/bukti/' . $d['bukti_pembayaran']);
        }
    }
    // Hapus transaksi
    $koneksi->query("DELETE FROM tb_transaksi WHERE id_transaksi='$id'");
    echo "<script>alert('Transaksi berhasil dihapus!');window.location='?page=MyApp/transaksi_wisata';</script>";
} else {
    echo "<script>alert('Akses ditolak!');window.location='?page=MyApp/transaksi_wisata';</script>";
}
?>
