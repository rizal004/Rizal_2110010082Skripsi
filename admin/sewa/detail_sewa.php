<?php
// File: detail_sewa.php - Detail Kendaraan Sewa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "inc/koneksi.php";

// Ambil ID kendaraan dari URL
$id_motor = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_motor <= 0) {
    echo "<script>alert('ID kendaraan tidak valid!'); window.location.href='?page=MyApp/data_sewa';</script>";
    exit;
}

// Ambil data kendaraan
$query = "SELECT * FROM tb_motor WHERE id_motor = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_motor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Kendaraan tidak ditemukan!'); window.location.href='?page=MyApp/data_sewa';</script>";
    exit;
}

$data = $result->fetch_assoc();

// Proses gambar
$gambarList = [];
if (!empty($data['gambar'])) {
    $gambarArray = array_filter(array_map('trim', explode(',', $data['gambar'])));
    foreach ($gambarArray as $gambar) {
        // Cek path mana yang ada
        $pathUploads = "uploads/" . $gambar;
        $pathGambar = "gambar/" . $gambar;
        
        if (file_exists($pathUploads)) {
            $gambarList[] = $gambar;
        } elseif (file_exists($pathGambar)) {
            $gambarList[] = $gambar;
        }
    }
}

// Fungsi untuk format nomor WhatsApp
function formatWhatsAppNumber($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (substr($number, 0, 1) === '0') {
        $number = '62' . substr($number, 1);
    }
    return $number;
}

// Cek apakah user sudah login dan tentukan hak akses
$is_logged_in = isset($_SESSION['ses_username']) && isset($_SESSION['ses_level']) && isset($_SESSION['ses_id']);
$user_level = $is_logged_in ? strtolower($_SESSION['ses_level']) : '';
$user_id = $is_logged_in ? intval($_SESSION['ses_id']) : 0;

// Tentukan hak akses
$show_kritik_form = ($is_logged_in && $user_level === 'pengguna');
$show_feedback_list = ($is_logged_in && $user_level !== 'admin' && $user_level !== 'administrator');

// Debug info (hapus di production)
// echo "<!-- Debug: Logged in: " . ($is_logged_in ? 'Yes' : 'No') . " -->";
// echo "<!-- Debug: User level: " . $user_level . " -->";
// echo "<!-- Debug: Show form: " . ($show_kritik_form ? 'Yes' : 'No') . " -->";
// echo "<!-- Debug: Show list: " . ($show_feedback_list ? 'Yes' : 'No') . " -->";

// Proses form kritik & saran
$success_msg = '';
$error_msg = '';

if (isset($_POST['btnSubmit']) && $show_kritik_form && $user_id > 0) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';
    
    if ($rating >= 1 && $rating <= 5 && !empty($komentar)) {
        // Escape komentar untuk keamanan
        $komentar_safe = mysqli_real_escape_string($koneksi, $komentar);
        
        // Cek apakah user sudah memberikan feedback untuk kendaraan ini
        $check_query = "SELECT id_feedback FROM tb_feedback_sewa WHERE id_motor = ? AND id_pengguna = ?";
        $check_stmt = $koneksi->prepare($check_query);
        $check_stmt->bind_param("ii", $id_motor, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update feedback yang sudah ada
            $update_query = "UPDATE tb_feedback_sewa SET rating = ?, komentar = ?, tanggal = NOW() WHERE id_motor = ? AND id_pengguna = ?";
            $update_stmt = $koneksi->prepare($update_query);
            $update_stmt->bind_param("isii", $rating, $komentar_safe, $id_motor, $user_id);
            
            if ($update_stmt->execute()) {
                $success_msg = "Feedback Anda berhasil diperbarui!";
            } else {
                $error_msg = "Gagal memperbarui feedback. Silakan coba lagi.";
            }
        } else {
            // Insert feedback baru
            $insert_query = "INSERT INTO tb_feedback_sewa (id_motor, id_pengguna, rating, komentar, tanggal) VALUES (?, ?, ?, ?, NOW())";
            $insert_stmt = $koneksi->prepare($insert_query);
            $insert_stmt->bind_param("iiis", $id_motor, $user_id, $rating, $komentar_safe);
            
            if ($insert_stmt->execute()) {
                $success_msg = "Terima kasih atas feedback Anda!";
            } else {
                $error_msg = "Gagal mengirim feedback. Silakan coba lagi.";
            }
        }
    } else {
        $error_msg = "Silakan lengkapi rating (1-5) dan komentar Anda.";
    }
}

// Ambil feedback dari pengguna (untuk ditampilkan)
$fb = null;
$feedback_count = 0;

if ($show_feedback_list) {
    $fb_query = "SELECT f.*, p.nama_pengguna 
                 FROM tb_feedback_sewa f 
                 JOIN tb_pengguna p ON f.id_pengguna = p.id_pengguna 
                 WHERE f.id_motor = ? 
                 ORDER BY f.tanggal DESC";
    $fb_stmt = $koneksi->prepare($fb_query);
    $fb_stmt->bind_param("i", $id_motor);
    $fb_stmt->execute();
    $fb = $fb_stmt->get_result();
    $feedback_count = $fb->num_rows;
}

// Debug feedback count
// echo "<!-- Debug: Feedback count: " . $feedback_count . " -->";

// Mencari kendaraan serupa (berdasarkan jenis dan merk)
$similar_vehicles = [];
if (!empty($data['jenis_kendaraan']) && !empty($data['merk'])) {
    $similar_query = "SELECT * FROM tb_motor 
                      WHERE id_motor != ? 
                      AND (jenis_kendaraan = ? OR merk = ?) 
                      ORDER BY RAND() 
                      LIMIT 6";
    $similar_stmt = $koneksi->prepare($similar_query);
    $similar_stmt->bind_param("iss", $id_motor, $data['jenis_kendaraan'], $data['merk']);
    $similar_stmt->execute();
    $similar_result = $similar_stmt->get_result();
    
    while ($similar = $similar_result->fetch_assoc()) {
        $similar_vehicles[] = $similar;
    }
}

// Ambil feedback user saat ini (jika ada)
$user_feedback = null;
if ($is_logged_in && $user_id > 0) {
    $user_fb_query = "SELECT * FROM tb_feedback_sewa WHERE id_motor = ? AND id_pengguna = ?";
    $user_fb_stmt = $koneksi->prepare($user_fb_query);
    $user_fb_stmt->bind_param("ii", $id_motor, $user_id);
    $user_fb_stmt->execute();
    $user_fb_result = $user_fb_stmt->get_result();
    if ($user_fb_result->num_rows > 0) {
        $user_feedback = $user_fb_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Kendaraan Sewa</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
body {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: #2c3e50;
}
.container { 
    margin-top: 30px; 
    margin-bottom: 38px; 
}
.content-header h1 {
    font-weight: 900;
    color: black;
    text-align: center;
    margin-bottom: 24px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}
.box-primary {
    background: rgba(255,255,255,0.99);
    border-radius: 18px;
    padding: 20px 20px 15px 20px;
    box-shadow: 0 6px 22px 0 rgba(79,172,254,0.12);
    max-width: 1500px;
    margin: 0 auto 30px auto;
    border: 2px solid #4facfe;
    border-top: 4px solid #4facfe !important;
}
.gallery-container { 
    margin-bottom: 30px; 
    text-align: center; 
}
.main-image-container {
    position: relative; 
    border-radius: 15px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(79,172,254,0.11); 
    margin-bottom: 10px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
.main-image-container img {
    max-width: 100%; 
    width: 100%; 
    height: 430px; 
    object-fit: cover;
    transition: transform 0.3s ease;
}
.nav-arrows {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}
.nav-arrows button {
    background: rgba(79,172,254,0.2);
    backdrop-filter: blur(5px);
    color: #4facfe;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    cursor: pointer;
    font-size: 22px;
    opacity: 0.92;
    box-shadow: 0 0 10px rgba(0,0,0,0.12);
    transition: background 0.3s, opacity 0.3s;
}
.nav-arrows button:hover {
    background: #4facfe;
    color: white;
    opacity: 1;
    transform: scale(1.1);
}
.image-counter {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: #4facfe;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: bold;
    z-index: 5;
    letter-spacing: 1px;
}
.image-title {
    position: absolute;
    bottom: 15px;
    left: 15px;
    color: white;
    z-index: 5;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
}
.info-container {
    background: #f8f9ff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(79,172,254,0.09);
    margin-bottom: 25px;
    border: 2px solid #4facfe;
}
.info-container h4 { 
    color: #4a5568; 
    font-weight: 700; 
    margin-bottom: 15px;
}
.vehicle-spec {
    background: #e6f3ff;
    padding: 15px;
    border-radius: 10px;
    margin: 15px 0;
    border-left: 4px solid #4facfe;
    box-shadow: 0 2px 8px rgba(79,172,254,0.08);
}
.spec-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.spec-item {
    background: #fff;
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid #4facfe;
    box-shadow: 0 2px 5px rgba(79,172,254,0.1);
    display: flex;
    align-items: center;
    gap: 10px;
}
.spec-item i {
    font-size: 1.2em;
    color: #4facfe;
    width: 24px;
    text-align: center;
}
.similar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 15px;
}
.similar-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(79,172,254,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}
.similar-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(79,172,254,0.2);
    border-color: #4facfe;
    text-decoration: none;
    color: inherit;
}
.similar-card-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
}
.similar-card-body {
    padding: 15px;
}
.similar-card-title {
    font-weight: bold;
    color: #4a5568;
    margin-bottom: 8px;
    font-size: 1.1em;
}
.similar-card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}
.similar-card-price {
    background: #e6f3ff;
    color: #4facfe;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.9em;
    font-weight: bold;
    border: 1px solid #4facfe;
}
.similar-card-type {
    background: #f0fff4;
    color: #28a745;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}
.price-highlight {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin: 15px 0;
    box-shadow: 0 4px 15px rgba(79,172,254,0.3);
}
.price-amount {
    font-size: 1.5em;
    font-weight: bold;
    color: #2c3e50;
    background: #f0fff4;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    border: 2px solid #28a745;
    margin: 10px 0;
}
.btn-vehicle {
    border-radius: 20px;
    font-weight: bold;
    padding: 8px 22px;
    background: linear-gradient(90deg, #4facfe 20%, #00f2fe 100%);
    border: none;
    color: white;
    margin: 5px;
    transition: background 0.3s;
}
.btn-vehicle:hover { 
    background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
    color: white;
}
.vehicle-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: bold;
    margin: 5px;
}
.badge-motor {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #1976d2;
}
.badge-mobil {
    background: #e8f5e8;
    color: #388e3c;
    border: 1px solid #388e3c;
}
.contact-info {
    background: #f8f9ff;
    padding: 15px;
    border-radius: 10px;
    border: 2px solid #4facfe;
    margin: 15px 0;
}
.star-rating { 
    display: flex; 
    flex-direction: row-reverse; 
    justify-content: flex-start; 
    font-size: 30px;
}
.star-rating input { display: none; }
.star-rating label { 
    color: #cbd5e0; 
    cursor: pointer; 
    margin-right: 4px;
    transition: color 0.18s;
}
.star-rating input:checked ~ label, 
.star-rating label:hover, 
.star-rating label:hover ~ label { 
    color: #4facfe;
}
.card-feedback {
    margin-bottom: 1rem; 
    background: #f8f9ff; 
    border-radius: 10px; 
    border: 2px solid #4facfe;
    box-shadow: 0 2px 8px rgba(79,172,254,0.09);
    padding: 15px;
    transition: box-shadow 0.16s;
}
.card-feedback:hover {
    box-shadow: 0 4px 12px rgba(79,172,254,0.15);
}
.panel.panel-default {
    background: #fff; 
    padding: 14px 13px 17px 13px;
    border-radius: 10px; 
    box-shadow: 0 2px 7px rgba(79,172,254,0.07);
    margin-top: 18px;
    margin-bottom: 14px;
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
    border: 2px solid #4facfe;
}
.no-data {
    text-align: center;
    color: #4a5568;
    font-style: italic;
    padding: 20px;
}
.alert-success {
    background-color: #f8f9ff !important;
    border-color: #4facfe !important;
    color: #4a5568 !important;
}
.alert-danger {
    background-color: #ffeaea !important;
    border-color: #ff6b6b !important;
    color: #d63384 !important;
}
.form-control:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 0.2rem rgba(79,172,254,0.25);
}
.section-title {
    color: #4a5568;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-title i {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
    padding: 8px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.wa-contact-link {
    color: #25D366;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
    padding: 8px 15px;
    border-radius: 20px;
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
    display: inline-block;
    margin: 5px 0;
}
.wa-contact-link:hover {
    color: #128C7E;
    text-decoration: none;
    background: rgba(37, 211, 102, 0.2);
    border-color: #25D366;
    transform: translateY(-2px);
}
.wa-contact-link i {
    margin-right: 5px;
    font-size: 1.1em;
}
.btn-wa {
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
    border: none;
    border-radius: 25px;
    padding: 12px 25px;
    font-weight: bold;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    margin: 5px;
}
.btn-wa:hover {
    background: linear-gradient(135deg, #128C7E, #25D366);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
    text-decoration: none;
}

@media (max-width: 800px) { 
    .box-primary, .main-image-container { 
        max-width: 99vw; 
    }
}
@media (max-width: 768px) {
    .main-image-container img { 
        height: 250px; 
    }
    .box-primary { 
        padding: 15px;
    }
    .similar-grid {
        grid-template-columns: 1fr;
    }
    .spec-grid {
        grid-template-columns: 1fr;
    }
    .nav-arrows {
        padding: 0 10px;
    }
    .nav-arrows button {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
}
@media (max-width: 500px) { 
    .main-image-container img { 
        height: 200px; 
    } 
    .box-primary { 
        padding: 3vw 2vw; 
    }
}
.swal2-popup { 
    font-size: 1.18rem !important; 
}
.swal2-confirm {
    background-color: #4facfe !important;
}
.swal2-confirm:hover {
    background-color: #00f2fe !important;
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container">
    <section class="content-header">
        <h1>Detail Kendaraan Sewa</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <h2 style="margin: 0;"><?= htmlspecialchars($data['nama_motor']) ?></h2>
                    <span class="vehicle-badge <?= $data['jenis_kendaraan'] == 'Motor' ? 'badge-motor' : 'badge-mobil' ?>">
                        <i class="fa <?= $data['jenis_kendaraan'] == 'Motor' ? 'fa-motorcycle' : 'fa-car' ?>"></i>
                        <?= htmlspecialchars($data['jenis_kendaraan']) ?>
                    </span>
                </div>
                
                <!-- Galeri Gambar -->
                <div class="gallery-container">
                    <div class="main-image-container">
                        <?php if (!empty($gambarList)): ?>
                            <?php
                            $firstImage = $gambarList[0];
                            $imagePath = file_exists("uploads/" . $firstImage) ? "uploads/" . $firstImage : "gambar/" . $firstImage;
                            ?>
                            <img id="mainImage" src="<?= $imagePath ?>" alt="<?= htmlspecialchars($data['nama_motor']) ?>">
                            
                            <?php if (count($gambarList) > 1): ?>
                            <div class="nav-arrows">
                                <button id="prevBtn"><i class="fa fa-chevron-left"></i></button>
                                <button id="nextBtn"><i class="fa fa-chevron-right"></i></button>
                            </div>
                            <div class="image-counter">
                                <span id="currentImageNum">1</span>/<?= count($gambarList) ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="image-title">
                                <h4><?= htmlspecialchars($data['nama_motor']) ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <i class="fa fa-image" style="font-size: 4em; color: #ccc; margin-bottom: 10px;"></i>
                                <p>Tidak ada gambar tersedia</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info Kendaraan -->
                <div class="info-container">
                    <h4><i class="fa <?= $data['jenis_kendaraan'] == 'Motor' ? 'fa-motorcycle' : 'fa-car' ?> text-primary"></i> Informasi Kendaraan</h4>
                    
                    <div class="vehicle-spec">
                        <div class="spec-grid">
                            <div class="spec-item">
                                <i class="fa fa-tag"></i>
                                <div>
                                    <strong>Merk:</strong><br>
                                    <?= htmlspecialchars($data['merk']) ?>
                                </div>
                            </div>
                            <div class="spec-item">
                                <i class="fa fa-calendar"></i>
                                <div>
                                    <strong>Tahun:</strong><br>
                                    <?= htmlspecialchars($data['tahun']) ?>
                                </div>
                            </div>
                            <div class="spec-item">
                                <i class="fa fa-palette"></i>
                                <div>
                                    <strong>Warna:</strong><br>
                                    <?= htmlspecialchars($data['warna']) ?>
                                </div>
                            </div>
                            <div class="spec-item">
                                <i class="fa fa-map-marker-alt"></i>
                                <div>
                                    <strong>Lokasi:</strong><br>
                                    <?= htmlspecialchars("{$data['kecamatan']}, {$data['kabupaten']}, {$data['provinsi']}") ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="price-amount">
                        <i class="fa fa-money-bill-wave"></i> 
                        Rp <?= number_format($data['harga_sewa'], 0, ',', '.') ?> / Hari
                    </div>
                </div>

                <!-- Info Kontak -->
                <div class="contact-info">
                    <h5><i class="fa fa-phone text-success"></i> Informasi Kontak</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama Kontak:</strong> 
                                <?= htmlspecialchars($data['nama_kontak'] ?? 'Tidak tersedia') ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Nomor Telepon:</strong> 
                                <?php if (!empty($data['no_telepon'])): ?>
                                    <?php 
                                    $wa_number = formatWhatsAppNumber($data['no_telepon']);
                                    $vehicle_name = htmlspecialchars($data['nama_motor']);
                                    $wa_text = "Halo, saya ingin menanyakan ketersediaan $vehicle_name untuk disewa";
                                    $wa_link = "https://wa.me/$wa_number?text=" . urlencode($wa_text);
                                    ?>
                                    <a href="<?= $wa_link ?>" target="_blank" class="wa-contact-link">
                                        <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($data['no_telepon']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Tidak tersedia</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fasilitas -->
                <?php if (!empty($data['fasilitas'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-list text-info"></i> Fasilitas Kendaraan</h4>
                    <div class="spec-grid">
                        <?php 
                        $fasilitasList = array_filter(array_map('trim', explode(',', $data['fasilitas'])));
                        foreach ($fasilitasList as $fasilitas): ?>
                            <div class="spec-item">
                                <i class="fa fa-check-circle text-success"></i>
                                <div><?= htmlspecialchars($fasilitas) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Kendaraan Serupa -->
                <?php if (!empty($similar_vehicles)): ?>
                <div class="info-container">
                    <h4 class="section-title">
                        <i class="fa <?= $data['jenis_kendaraan'] == 'Motor' ? 'fa-motorcycle' : 'fa-car' ?>"></i>
                        Kendaraan Serupa
                    </h4>
                    <div class="similar-grid">
                        <?php foreach ($similar_vehicles as $similar): ?>
                            <a href="?page=MyApp/detail_sewa&id=<?= $similar['id_motor'] ?>" class="similar-card">
                                <?php 
                                $gambar_similar = '';
                                if (!empty($similar['gambar'])) {
                                    $gambar_array = array_filter(explode(',', $similar['gambar']));
                                    if (!empty($gambar_array)) {
                                        $gambar_similar = trim($gambar_array[0]);
                                    }
                                }
                                ?>
                                <?php if ($gambar_similar): ?>
                                    <?php 
                                    $similar_path = file_exists("uploads/" . $gambar_similar) ? "uploads/" . $gambar_similar : "gambar/" . $gambar_similar;
                                    ?>
                                    <img src="<?= $similar_path ?>" alt="<?= htmlspecialchars($similar['nama_motor']) ?>" class="similar-card-image">
                                <?php else: ?>
                                    <div class="similar-card-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                        <i class="fa fa-image" style="font-size: 2em;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="similar-card-body">
                                    <div class="similar-card-title"><?= htmlspecialchars($similar['nama_motor']) ?></div>
                                    <div class="similar-card-info">
                                        <span class="similar-card-type <?= $similar['jenis_kendaraan'] == 'Motor' ? 'badge-motor' : 'badge-mobil' ?>">
                                            <?= htmlspecialchars($similar['jenis_kendaraan']) ?>
                                        </span>
                                        <span class="similar-card-price">
                                            Rp <?= number_format($similar['harga_sewa'], 0, ',', '.') ?>/hari
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tombol Aksi -->
                <div class="text-center" style="margin-top: 30px;">
                    <?php if (!empty($data['no_telepon'])): ?>
                        <a href="<?= $wa_link ?>" target="_blank" class="btn btn-wa">
                            <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                        </a>
                    <?php endif; ?>
                    <a href="?page=MyApp/data_sewa" class="btn btn-vehicle">
                        <i class="fa fa-arrow-left"></i> Kembali ke Daftar Sewa
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Form Kritik & Saran -->
        <?php if ($show_kritik_form): ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <h4 class="section-title">
                    <i class="fa fa-star"></i>
                    Berikan Feedback
                </h4>
                
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> <?= $success_msg ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> <?= $error_msg ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" />
                            <label for="star5" title="5 stars">★</label>
                            <input type="radio" id="star4" name="rating" value="4" />
                            <label for="star4" title="4 stars">★</label>
                            <input type="radio" id="star3" name="rating" value="3" />
                            <label for="star3" title="3 stars">★</label>
                            <input type="radio" id="star2" name="rating" value="2" />
                            <label for="star2" title="2 stars">★</label>
                            <input type="radio" id="star1" name="rating" value="1" />
                            <label for="star1" title="1 star">★</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="komentar">Komentar:</label>
                        <textarea class="form-control" id="komentar" name="komentar" rows="4" 
                                  placeholder="Bagikan pengalaman Anda dengan kendaraan ini..." required></textarea>
                    </div>
                    
                    <button type="submit" name="btnSubmit" class="btn btn-vehicle">
                        <i class="fa fa-paper-plane"></i> Kirim Feedback
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Daftar Feedback -->
        <?php if ($show_feedback_list && $fb && $fb->num_rows > 0): ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <h4 class="section-title">
                    <i class="fa fa-comments"></i>
                    Feedback Pengguna
                </h4>
                
                <?php while ($feedback = $fb->fetch_assoc()): ?>
                <div class="card-feedback">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div>
                            <strong><?= htmlspecialchars($feedback['nama_pengguna']) ?></strong>
                            <div style="color: #4facfe; font-size: 18px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span style="color: <?= $i <= $feedback['rating'] ? '#4facfe' : '#ddd' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= date('d M Y', strtotime($feedback['tanggal'])) ?>
                        </small>
                    </div>
                    <p><?= htmlspecialchars($feedback['komentar']) ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php elseif ($show_feedback_list): ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <h4 class="section-title">
                    <i class="fa fa-comments"></i>
                    Feedback Pengguna
                </h4>
                <div class="no-data">
                    <i class="fa fa-comment-slash" style="font-size: 3em; color: #ccc; margin-bottom: 10px;"></i>
                    <p>Belum ada feedback untuk kendaraan ini.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

<script src="bootstrap/js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const images = <?= json_encode($gambarList) ?>;
    const mainImage = document.getElementById('mainImage');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const currentImageNum = document.getElementById('currentImageNum');
    
    let currentImageIndex = 0;
    
    function updateImage() {
        if (images.length > 0) {
            const imageName = images[currentImageIndex];
            const uploadPath = 'uploads/' + imageName;
            const gambarPath = 'gambar/' + imageName;
            
            // Cek path mana yang ada
            const img = new Image();
            img.onload = function() {
                mainImage.src = uploadPath;
            };
            img.onerror = function() {
                mainImage.src = gambarPath;
            };
            img.src = uploadPath;
            
            if (currentImageNum) {
                currentImageNum.textContent = currentImageIndex + 1;
            }
        }
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateImage();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateImage();
        });
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (images.length > 1) {
            if (e.key === 'ArrowLeft' && prevBtn) {
                prevBtn.click();
            } else if (e.key === 'ArrowRight' && nextBtn) {
                nextBtn.click();
            }
        }
    });
    
    // Auto-slide (optional)
    if (images.length > 1) {
        setInterval(function() {
            if (nextBtn) {
                nextBtn.click();
            }
        }, 5000); // Auto slide every 5 seconds
    }
});

// SweetAlert untuk konfirmasi
function confirmAction(message, callback) {
    Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4facfe',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}
</script>
</body>
</html>