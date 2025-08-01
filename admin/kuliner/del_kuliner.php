<?php
include "inc/koneksi.php";

// Pastikan ada parameter id_kuliner
if (!isset($_GET['id'])) {
    echo "<script>
            alert('ID Kuliner tidak ditemukan!');
            window.location='index.php?page=MyApp/tabel_kuliner';
          </script>";
    exit;
}

// Ambil ID kuliner
$id_kuliner = mysqli_real_escape_string($koneksi, $_GET['id']);

// Cek data kuliner & ambil nama file gambar
$sql = $koneksi->query("SELECT gambar FROM tb_kuliner WHERE id_kuliner = '$id_kuliner'");
$data = $sql->fetch_assoc();

if (!$data) {
    echo "<script>
            alert('Data kuliner tidak ditemukan!');
            window.location='index.php?page=MyApp/tabel_kuliner';
          </script>";
    exit;
}

// Hapus file gambar di folder uploads (jika ada)
if (!empty($data['gambar'])) {
    $gambarList = explode(',', $data['gambar']);
    foreach ($gambarList as $img) {
        $imgPath = "uploads/" . $img;
        if (file_exists($imgPath) && is_file($imgPath)) {
            unlink($imgPath);
        }
    }
}

// Hapus data dari database
$del = $koneksi->query("DELETE FROM tb_kuliner WHERE id_kuliner = '$id_kuliner'");

if ($del) {
    echo "<script>
            alert('Data kuliner berhasil dihapus!');
            window.location='index.php?page=MyApp/tabel_kuliner';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus data!');
            window.location='index.php?page=MyApp/tabel_kuliner';
          </script>";
}
?>
