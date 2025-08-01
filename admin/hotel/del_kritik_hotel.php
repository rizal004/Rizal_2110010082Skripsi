<?php 
// File: del_kritik_hotel.php (Hapus kritik dan saran hotel)
include "inc/koneksi.php";

// Get kritik ID from URL parameter
if (isset($_GET['id'])) {
    $id_kritik_saran_hotel = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Check if kritik exists
    $query = $koneksi->query("SELECT 
        ks.id_kritik_saran_hotel,
        ks.id_hotel,
        ks.id_pengguna,
        ks.rating,
        ks.komentar,
        ks.tanggal,
        h.nama_hotel,
        p.nama_pengguna
        FROM tb_kritik_saran_hotel ks
        LEFT JOIN tb_hotel h ON ks.id_hotel = h.id_hotel
        LEFT JOIN tb_pengguna p ON ks.id_pengguna = p.id_pengguna
        WHERE ks.id_kritik_saran_hotel = '$id_kritik_saran_hotel'");
    
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        
        // Delete record from database
        $sql = $koneksi->query("DELETE FROM tb_kritik_saran_hotel WHERE id_kritik_saran_hotel = '$id_kritik_saran_hotel'");
        
        if ($sql) {
            echo "<script>
                    alert('Kritik dan saran hotel berhasil dihapus!');
                    window.location='index.php?page=MyApp/data_kritik_hotel';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data kritik dan saran!');
                    window.location='index.php?page=MyApp/data_kritik_hotel';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Kritik dan saran tidak ditemukan!');
                window.location='index.php?page=MyApp/data_kritik_hotel';
              </script>";
    }
} else {
    echo "<script>
            alert('ID Kritik dan Saran tidak valid!');
            window.location='index.php?page=MyApp/data_kritik_hotel';
          </script>";
}
?>