<?php 
// File: del_kritik_hotel.php (Hapus kritik dan saran hotel)
include "inc/koneksi.php";

// Get kritik ID from URL parameter
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Check if kritik exists
    $query = $koneksi->query("SELECT 
        ks.id,
        ks.id_oleh2,
        ks.id_pengguna,
        ks.rating,
        ks.komentar,
        ks.tanggal,
        h.nama_toko,
        p.nama_pengguna
        FROM tb_kritik_saran_oleh2 ks
        LEFT JOIN tb_oleh2 h ON ks.id_oleh2 = h.id_oleh2
        LEFT JOIN tb_pengguna p ON ks.id_pengguna = p.id_pengguna
        WHERE ks.id = '$id'");
    
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        
        // Delete record from database
        $sql = $koneksi->query("DELETE FROM tb_kritik_saran_oleh2 WHERE id = '$id'");
        
        if ($sql) {
            echo "<script>
                    alert('Kritik dan saran oleh2 berhasil dihapus!');
                    window.location='index.php?page=MyApp/data_kritik_oleh2';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data kritik dan saran!');
                    window.location='index.php?page=MyApp/data_kritik_oleh2';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Kritik dan saran tidak ditemukan!');
                window.location='index.php?page=MyApp/data_kritik_oleh2';
              </script>";
    }
} else {
    echo "<script>
            alert('ID Kritik dan Saran tidak valid!');
            window.location='index.php?page=MyApp/data_kritik_oleh2';
          </script>";
}
?>