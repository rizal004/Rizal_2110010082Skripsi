<?php
// del_oleh2.php
include "inc/koneksi.php";

if (isset($_GET['id'])) {
    $id_oleh2 = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Hapus record dari tb_oleh2 (FK akan menghapus kritik_saran_oleh2 otomatis jika cascade)
    $sql = $koneksi->query("
        DELETE FROM tb_oleh2
        WHERE id_oleh2 = '$id_oleh2'
    ");

    if ($sql) {
        echo "<script>
                alert('Data oleh‐oleh berhasil dihapus!');
                window.location = 'index.php?page=MyApp/tabel_oleh2';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . addslashes($koneksi->error) . "');
                window.location = 'index.php?page=MyApp/tabel_oleh2';
              </script>";
    }
} else {
    // Kalau id tidak ada, kembalikan ke daftar
    header("Location: index.php?page=MyApp/tabel_oleh2");
    exit;
}
