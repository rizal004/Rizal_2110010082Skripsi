<?php 
// File: del_kritik_event.php (Hapus kritik dan saran event)
include "inc/koneksi.php";

// Get kritik ID from URL parameter
if (isset($_GET['id'])) {
    $id_kritik_saran_event = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Check if kritik exists
    $query = $koneksi->query("SELECT 
        ks.id_kritik_saran_event,
        ks.id_event,
        ks.id_pengguna,
        ks.rating,
        ks.komentar,
        ks.tanggal,
        e.nama_event,
        p.nama_pengguna
        FROM tb_kritik_saran_event ks
        LEFT JOIN tb_event e ON ks.id_event = e.id_event
        LEFT JOIN tb_pengguna p ON ks.id_pengguna = p.id_pengguna
        WHERE ks.id_kritik_saran_event = '$id_kritik_saran_event'");
    
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        
        // Delete record from database
        $sql = $koneksi->query("DELETE FROM tb_kritik_saran_event WHERE id_kritik_saran_event = '$id_kritik_saran_event'");
        
        if ($sql) {
            echo "<script>
                    alert('Kritik dan saran event berhasil dihapus!');
                    window.location='index.php?page=MyApp/data_kritik_event';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data kritik dan saran!');
                    window.location='index.php?page=MyApp/data_kritik_event';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Kritik dan saran tidak ditemukan!');
                window.location='index.php?page=MyApp/data_kritik_event';
              </script>";
    }
} else {
    echo "<script>
            alert('ID Kritik dan Saran tidak valid!');
            window.location='index.php?page=MyApp/data_kritik_event';
          </script>";
}
?>