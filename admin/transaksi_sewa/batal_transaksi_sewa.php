
<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$ses_id = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;
$id = isset($_GET['id']) ? $_GET['id'] : '';

$q = $koneksi->query("SELECT * FROM tb_transaksi_sewa WHERE id_transaksi='$id' AND id_pengguna=$ses_id AND status='pending'");

if ($q && $q->num_rows) {
    $koneksi->query("UPDATE tb_transaksi_sewa SET status='cancelled' WHERE id_transaksi='$id'");
    echo "<script>alert('Transaksi berhasil dibatalkan!');window.location='?page=MyApp/data_transaksi_sewa';</script>";
} else {
    echo "<script>alert('Akses ditolak!');window.location='?page=MyApp/data_transaksi_sewa';</script>";
}
?>