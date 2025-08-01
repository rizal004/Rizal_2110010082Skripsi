<?php
// hapus_wisata.php
include "inc/koneksi.php";

// Validasi apakah ID tersedia
if (isset($_GET['id'])) {
    $id_wisata = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Cek apakah data wisata dengan ID tersebut ada
    $cek = $koneksi->query("SELECT * FROM tb_wisata WHERE id_wisata = '$id_wisata'");
    
    if ($cek->num_rows > 0) {
        // Ambil informasi gambar sebelum dihapus
        $data = $cek->fetch_assoc();
        $gambar_files = explode(',', $data['gambar']);
        
        // Hapus data dari database
        $sql = $koneksi->query("DELETE FROM tb_wisata WHERE id_wisata = '$id_wisata'");
        
        if ($sql) {
            // Hapus file gambar dari folder uploads
            foreach ($gambar_files as $file) {
                if (!empty($file)) {
                    $path = "uploads/" . $file;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
            
            echo "<script>
                alert('Data wisata berhasil dihapus!');
                window.location.href='index.php?page=MyApp/tabel_wisata';
            </script>";
        } else {
            echo "<script>
                alert('Data wisata gagal dihapus! Error: " . mysqli_error($koneksi) . "');
                window.location.href='index.php?page=MyApp/tabel_wisata';
            </script>";
        }
    } else {
        echo "<script>
            alert('Data wisata tidak ditemukan!');
            window.location.href='index.php?page=MyApp/tabel_wisata';
        </script>";
    }
} else {
    // Jika tidak ada ID yang diberikan
    echo "<script>
        alert('ID wisata tidak diberikan!');
        window.location.href='index.php?page=MyApp/tabel_wisata';
    </script>";
}
?>



