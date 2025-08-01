<?php 
// File: del_kritik_hotel.php (Hapus kritik dan saran hotel)
include "inc/koneksi.php";

// Get kritik ID from URL parameter
if (isset($_GET['id'])) {
    $id_kritik_saran = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Check if kritik exists
    $query = $koneksi->query("SELECT 
        ks.id_kritik_saran,
        ks.id_kuliner,
        ks.id_pengguna,
        ks.rating,
        ks.komentar,
        ks.tanggal,
        h.nama_kuliner,
        p.nama_pengguna
        FROM tb_kritik_saran_kuliner ks
        LEFT JOIN tb_kuliner h ON ks.id_kuliner = h.id_kuliner
        LEFT JOIN tb_pengguna p ON ks.id_pengguna = p.id_pengguna
        WHERE ks.id_kritik_saran = '$id_kritik_saran'");
    
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        
        // Delete record from database
        $sql = $koneksi->query("DELETE FROM tb_kritik_saran_kuliner WHERE id_kritik_saran = '$id_kritik_saran'");
        
        if ($sql) {
            echo "<script>
                    alert('Kritik dan saran kuliner berhasil dihapus!');
                    window.location='index.php?page=MyApp/data_kritik_kuliner';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data kritik dan saran!');
                    window.location='index.php?page=MyApp/data_kritik_kuliner';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Kritik dan saran tidak ditemukan!');
                window.location='index.php?page=MyApp/data_kritik_kuliner';
              </script>";
    }
} else {
    echo "<script>
            alert('ID Kritik dan Saran tidak valid!');
            window.location='index.php?page=MyApp/data_kritik_kuliner';
          </script>";
}
?>