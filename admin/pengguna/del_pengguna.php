<?php
include "inc/koneksi.php";
$id = $_GET['kode'];
$sql = $koneksi->query("DELETE FROM tb_pengguna WHERE id_pengguna='$id'");
if ($sql) {
    echo "<script>alert('Data berhasil dihapus!');window.location='?page=MyApp/data_pengguna';</script>";
} else {
    echo "<script>alert('Gagal hapus data!');</script>";
}
?>
