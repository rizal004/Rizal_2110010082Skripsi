<?php
// Pastikan file koneksi sudah ada
if (file_exists("inc/koneksi.php")) {
    include "inc/koneksi.php";
} else {
    die("Koneksi database tidak ditemukan!");
}

// Ambil ID transaksi dari URL
if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];
    
    // Verifikasi ID transaksi dengan prepared statement
    $cek = $koneksi->prepare("SELECT id_transaksi, status FROM tb_transaksi WHERE id_transaksi = ?");
    $cek->bind_param("s", $id_transaksi);
    $cek->execute();
    $result = $cek->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Periksa apakah status masih pending
        if ($data['status'] === 'pending') {
            // Update status menjadi approved
            $update = $koneksi->prepare("UPDATE tb_transaksi SET status = 'approved' WHERE id_transaksi = ?");
            $update->bind_param("s", $id_transaksi);
            
            if ($update->execute()) {
                echo "<script>
                    alert('Transaksi berhasil diapprove!');
                    window.location.href = '?page=MyApp/transaksi_wisata';
                </script>";
            } else {
                echo "<script>
                    alert('Gagal mengupdate status transaksi: " . $koneksi->error . "');
                    window.location.href = '?page=MyApp/transaksi_wisata';
                </script>";
            }
        } else {
            echo "<script>
                alert('Transaksi ini tidak dalam status pending!');
                window.location.href = '?page=MyApp/transaksi_wisata';
            </script>";
        }
    } else {
        echo "<script>
            alert('ID Transaksi tidak valid!');
            window.location.href = '?page=MyApp/transaksi_wisata';
        </script>";
    }
    
    // Tutup prepared statement
    $cek->close();
    if (isset($update)) {
        $update->close();
    }
} else {
    echo "<script>
        alert('ID Transaksi tidak ditemukan!');
        window.location.href = '?page=MyApp/transaksi_wisata';
    </script>";
}
?>