<?php 
// File: del_kritik_sewa.php (Hapus kritik dan saran sewa)
include "inc/koneksi.php";

// Get kritik ID from URL parameter
if (isset($_GET['id'])) {
    $id_feedback = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Check if kritik exists
    $query = $koneksi->query("SELECT 
        fs.id_feedback,
        fs.id_motor,
        fs.id_pengguna,
        fs.rating,
        fs.komentar,
        fs.tanggal,
        m.nama_motor,
        m.jenis_kendaraan,
        p.nama_pengguna
        FROM tb_feedback_sewa fs
        LEFT JOIN tb_motor m ON fs.id_motor = m.id_motor
        LEFT JOIN tb_pengguna p ON fs.id_pengguna = p.id_pengguna
        WHERE fs.id_feedback = '$id_feedback'");
    
    if ($query->num_rows > 0) {
        $data = $query->fetch_assoc();
        
        // Delete record from database
        $sql = $koneksi->query("DELETE FROM tb_feedback_sewa WHERE id_feedback = '$id_feedback'");
        
        if ($sql) {
            echo "<script>
                    alert('Kritik dan saran sewa berhasil dihapus!');
                    window.location='index.php?page=MyApp/data_kritik_sewa';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data kritik dan saran!');
                    window.location='index.php?page=MyApp/data_kritik_sewa';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Kritik dan saran tidak ditemukan!');
                window.location='index.php?page=MyApp/data_kritik_sewa';
              </script>";
    }
} else {
    echo "<script>
            alert('ID Kritik dan Saran tidak valid!');
            window.location='index.php?page=MyApp/data_kritik_sewa';
          </script>";
}
?>