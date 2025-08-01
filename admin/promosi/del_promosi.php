<?php
$id = $_GET['id'];
$query = $koneksi->query("SELECT gambar FROM tb_promosi WHERE id_promosi='$id'");
$data = $query->fetch_assoc();

// Hapus gambar jika ada
if($data['gambar'] && file_exists("uploads/promosi/".$data['gambar'])) {
    unlink("uploads/promosi/".$data['gambar']);
}

$query = $koneksi->query("DELETE FROM tb_promosi WHERE id_promosi='$id'");
if($query){
    echo "<script>alert('Data berhasil dihapus!');window.location='?page=MyApp/data_promosi';</script>";
} else {
    echo "<script>alert('Gagal hapus data');window.location='?page=MyApp/data_promosi';</script>";
}
?>
