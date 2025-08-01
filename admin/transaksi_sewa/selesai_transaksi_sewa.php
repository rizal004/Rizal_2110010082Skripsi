<?php
// inc/koneksi.php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$ses_id    = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';

// Cek apakah user adalah pengguna
if ($ses_level !== 'pengguna') {
    echo "<script>
        alert('Akses ditolak! Halaman ini hanya untuk pengguna.');
        window.location.href = '?page=MyApp/transaksi_sewa';
    </script>";
    exit;
}

// Ambil ID transaksi dari parameter URL
$id_transaksi = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($id_transaksi)) {
    echo "<script>
        alert('ID Transaksi tidak valid!');
        window.location.href = '?page=MyApp/transaksi_sewa';
    </script>";
    exit;
}

// Cek apakah transaksi ada, milik user, dan statusnya approved
$sql_check = $koneksi->prepare("SELECT t.id_transaksi, t.status, t.id_motor, t.id_pengguna, 
                                        m.nama_motor, m.merk, m.jenis_kendaraan, p.nama_pengguna, 
                                        t.tanggal_mulai, t.tanggal_selesai, t.total_bayar, t.lama_sewa,
                                        t.tanggal, m.harga_sewa
                                 FROM tb_transaksi_sewa t
                                 LEFT JOIN tb_motor m ON t.id_motor = m.id_motor
                                 LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
                                 WHERE t.id_transaksi = ? AND t.id_pengguna = ?");

if (!$sql_check) {
    die("Error preparing statement: " . $koneksi->error);
}

$sql_check->bind_param("si", $id_transaksi, $ses_id);
$sql_check->execute();
$result = $sql_check->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        alert('Transaksi tidak ditemukan atau bukan milik Anda!');
        window.location.href = '?page=MyApp/transaksi_sewa';
    </script>";
    exit;
}

$data_transaksi = $result->fetch_assoc();

if ($data_transaksi['status'] !== 'approved') {
    echo "<script>
        alert('Transaksi ini belum dapat diselesaikan! Status saat ini: " . $data_transaksi['status'] . "');
        window.location.href = '?page=MyApp/transaksi_sewa';
    </script>";
    exit;
}

// Function untuk cek apakah kolom 'status' ada di tabel tb_motor
function checkMotorStatusColumn($koneksi) {
    $sql = "SHOW COLUMNS FROM tb_motor LIKE 'status'";
    $result = $koneksi->query($sql);
    return $result && $result->num_rows > 0;
}

// Proses form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $testimoni = isset($_POST['testimoni']) ? trim($_POST['testimoni']) : '';
    
    // Validasi rating
    if ($rating < 1 || $rating > 5) {
        $error = 'Rating harus antara 1-5!';
    } else {
        // Mulai transaksi database
        $koneksi->begin_transaction();
        
        try {
            // Update status transaksi dan rating/testimoni
            $sql_update = $koneksi->prepare("UPDATE tb_transaksi_sewa 
                                            SET status = 'selesai', rating = ?, testimoni = ?, 
                                                tanggal_selesai = CURDATE() 
                                            WHERE id_transaksi = ? AND id_pengguna = ?");
            
            if (!$sql_update) {
                throw new Exception('Error preparing update statement: ' . $koneksi->error);
            }
            
            $sql_update->bind_param("issi", $rating, $testimoni, $id_transaksi, $ses_id);
            
            if (!$sql_update->execute()) {
                throw new Exception('Gagal menyelesaikan transaksi: ' . $sql_update->error);
            }
            
            // Cek dan update status motor jika kolom status ada
            if (checkMotorStatusColumn($koneksi)) {
                $sql_motor = $koneksi->prepare("UPDATE tb_motor SET status = 'tersedia' WHERE id_motor = ?");
                
                if (!$sql_motor) {
                    throw new Exception('Error preparing motor update statement: ' . $koneksi->error);
                }
                
                $sql_motor->bind_param("i", $data_transaksi['id_motor']);
                
                if (!$sql_motor->execute()) {
                    throw new Exception('Gagal mengupdate status motor: ' . $sql_motor->error);
                }
                $sql_motor->close();
            } else {
                // Jika kolom status tidak ada, buat log atau tindakan alternatif
                // Misalnya: tambahkan ke tabel log atau update field lain
                error_log("Warning: Kolom 'status' tidak ditemukan di tabel tb_motor. Motor ID: " . $data_transaksi['id_motor']);
            }
            
            // Commit transaksi
            $koneksi->commit();
            $sql_update->close();
            
            echo "<script>
                alert('Transaksi berhasil diselesaikan! Terima kasih atas rating dan testimoni Anda.');
                window.location.href = '?page=MyApp/transaksi_sewa';
            </script>";
            exit;
            
        } catch (Exception $e) {
            // Rollback jika ada error
            $koneksi->rollback();
            $error = $e->getMessage();
        }
    }
}

$sql_check->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Selesaikan Transaksi Sewa</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }
        .content-header h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-weight: 700;
        }
        .breadcrumb {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .breadcrumb a {
            color: #fff;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            color: #f8f9fa;
        }
        .box {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 800px;
            overflow: hidden;
        }
        .box-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            border-bottom: none;
        }
        .box-body {
            padding: 30px;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .rating-section {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .star-rating {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .star-rating i {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .star-rating i:hover {
            color: #ffc107;
            transform: scale(1.1);
        }
        .star-rating i.active {
            color: #ffc107 !important;
            transform: scale(1.1);
        }
        .star-rating i.hover-active {
            color: #ffc107;
            transform: scale(1.1);
        }
        .rating-text {
            text-align: center;
            font-weight: bold;
            color: #667eea;
            margin-top: 10px;
        }
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-1px);
        }
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .completion-icon {
            text-align: center;
            margin-bottom: 20px;
        }
        .completion-icon i {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .box {
                margin: 10px;
            }
            .box-body {
                padding: 20px;
            }
            .star-rating i {
                font-size: 25px;
                margin: 0 3px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="content-header">
            <h1><i class="fa fa-flag-checkered"></i> Selesaikan Transaksi Sewa</h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> <b>Sistem Sewa</b></a></li>
                <li><a href="?page=MyApp/transaksi_sewa">Transaksi Sewa</a></li>
                <li class="active">Selesaikan Transaksi</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">
                        <i class="fa fa-check-circle"></i> Konfirmasi Penyelesaian Sewa
                    </h3>
                </div>
                <div class="box-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="completion-icon">
                        <i class="fa fa-check-circle"></i>
                        <h4>Selesaikan Sewa Kendaraan</h4>
                        <p class="text-muted">Pastikan kendaraan sudah dikembalikan dengan kondisi baik</p>
                    </div>

                    <!-- Info Transaksi -->
                    <div class="info-card">
                        <h5><i class="fa fa-info-circle"></i> Detail Transaksi</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID Transaksi:</strong><br><?= htmlspecialchars($data_transaksi['id_transaksi']) ?></p>
                                <p><strong>Kendaraan:</strong><br><?= htmlspecialchars($data_transaksi['nama_motor']) ?></p>
                                <p><strong>Merk/Jenis:</strong><br><?= htmlspecialchars($data_transaksi['merk'] . ' - ' . $data_transaksi['jenis_kendaraan']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Tanggal Mulai:</strong><br><?= date('d-m-Y', strtotime($data_transaksi['tanggal_mulai'])) ?></p>
                                <p><strong>Lama Sewa:</strong><br><?= htmlspecialchars($data_transaksi['lama_sewa']) ?> hari</p>
                                <p><strong>Total Bayar:</strong><br><strong class="text-primary">Rp<?= number_format($data_transaksi['total_bayar'], 0, ',', '.') ?></strong></p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" onsubmit="return validateForm()">
                        <!-- Rating Section -->
                        <div class="rating-section">
                            <h5 class="text-center"><i class="fa fa-star"></i> Berikan Rating Anda</h5>
                            <p class="text-center text-muted">Bagaimana pengalaman sewa kendaraan Anda?</p>
                            
                            <div class="star-rating" id="star-rating">
                                <i class="fa fa-star" data-rating="1" onclick="setRating(1)" onmouseover="hoverRating(1)" onmouseout="resetHover()"></i>
                                <i class="fa fa-star" data-rating="2" onclick="setRating(2)" onmouseover="hoverRating(2)" onmouseout="resetHover()"></i>
                                <i class="fa fa-star" data-rating="3" onclick="setRating(3)" onmouseover="hoverRating(3)" onmouseout="resetHover()"></i>
                                <i class="fa fa-star" data-rating="4" onclick="setRating(4)" onmouseover="hoverRating(4)" onmouseout="resetHover()"></i>
                                <i class="fa fa-star" data-rating="5" onclick="setRating(5)" onmouseover="hoverRating(5)" onmouseout="resetHover()"></i>
                            </div>
                            
                            <div class="rating-text" id="rating-text">Pilih rating Anda</div>
                            
                            <input type="hidden" name="rating" id="rating-input" value="0" required>
                        </div>

                        <!-- Testimoni Section -->
                        <div class="form-group">
                            <label for="testimoni">
                                <i class="fa fa-comment"></i> Testimoni (Opsional)
                            </label>
                            <textarea name="testimoni" id="testimoni" class="form-control" rows="4" 
                                      placeholder="Ceritakan pengalaman Anda selama menyewa kendaraan ini..."></textarea>
                            <small class="text-muted">Testimoni Anda akan membantu pengguna lain dalam memilih kendaraan</small>
                        </div>

                        <!-- Konfirmasi -->
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            <strong>Perhatian:</strong> Pastikan kendaraan sudah dikembalikan dengan kondisi baik. 
                            Setelah menyelesaikan transaksi ini, status akan berubah menjadi "Selesai" dan tidak dapat diubah kembali.
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                <i class="fa fa-check"></i> Selesaikan Transaksi
                            </button>
                            <a href="?page=MyApp/transaksi_sewa" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    
    <script>
        let currentRating = 0;
        
        const ratingTexts = {
            0: 'Pilih rating Anda',
            1: 'Sangat Tidak Puas',
            2: 'Tidak Puas', 
            3: 'Biasa Saja',
            4: 'Puas',
            5: 'Sangat Puas'
        };

        function setRating(rating) {
            currentRating = rating;
            document.getElementById('rating-input').value = rating;
            
            // Update stars
            updateStars(rating);
            
            // Update text
            document.getElementById('rating-text').textContent = rating + '/5 - ' + ratingTexts[rating];
            
            // Enable button
            document.getElementById('submit-btn').disabled = false;
            
            console.log('Rating set to:', rating);
        }

        function hoverRating(rating) {
            updateStars(rating);
        }

        function resetHover() {
            updateStars(currentRating);
        }

        function updateStars(rating) {
            const stars = document.querySelectorAll('.star-rating i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#ffc107';
                    star.style.transform = 'scale(1.1)';
                } else {
                    star.style.color = '#ddd';
                    star.style.transform = 'scale(1)';
                }
            });
        }

        // Form validation
        function validateForm() {
            if (currentRating === 0) {
                alert('Silakan berikan rating terlebih dahulu!');
                return false;
            }
            
            if (!confirm('Yakin ingin menyelesaikan transaksi ini? Status akan berubah menjadi "Selesai" dan tidak dapat diubah kembali.')) {
                return false;
            }
            
            return true;
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateStars(0);
        });
    </script>
</body>
</html>