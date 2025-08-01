
<?php
// File: del_hotel.php (Hapus hotel)
include "inc/koneksi.php";

// Get hotel ID from URL parameter
if (isset($_GET['id'])) {
    $id_motor = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Get image names first to delete files
    $query = $koneksi->query("SELECT gambar FROM tb_motor WHERE id_motor = '$id_motor'");
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        
        // Delete image files if they exist
        if (!empty($data['gambar'])) {
            $gambar_files = explode(',', $data['gambar']);
            foreach ($gambar_files as $file) {
                $file_path = "uploads/" . $file;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
        
        // Delete record from database
        $sql = $koneksi->query("DELETE FROM tb_motor WHERE id_motor = '$id_motor'");
        
        if ($sql) {
            echo "<script>
                    alert('berhasil dihapus!');
                    window.location='index.php?page=MyApp/tabel_sewa';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data!');
                    window.location='index.php?page=MyApp/tabel_sewa';
                  </script>";
        }
    } else {
        echo "<script>
                alert(' tidak ditemukan!');
                window.location='index.php?page=MyApp/tabel_sewa';
              </script>";
    }
} else {
    echo "<script>
            alert('ID  tidak valid!');
            window.location='index.php?page=MyApp/tabel_sewa';
          </script>";
}
?>