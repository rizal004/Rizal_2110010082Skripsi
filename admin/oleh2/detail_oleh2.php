<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
include "inc/koneksi.php";

// Level user: admin/pengguna
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';
$show_kritik_form = ($ses_level === 'pengguna');
$show_feedback_list = ($ses_level !== 'admin' && $ses_level !== 'administrator');

// --- Ambil ID ---
$id_oleh2 = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_oleh2) {
    echo "<div style='text-align:center;padding:40px 0;'>Data tidak ditemukan.</div>"; exit;
}

// --- Ambil data oleh-oleh ---
$sql = $koneksi->query("SELECT * FROM tb_oleh2 WHERE id_oleh2 = '$id_oleh2'");
$data = $sql->fetch_assoc();
if (!$data) {
    echo '<div style="text-align:center;padding:40px 0;">Data tidak ditemukan.</div>'; exit;
}

// --- Galeri gambar ---
$gambarList = array_filter(explode(',', $data['gambar']));

// --- User login ---
$id_user = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;

// --- Ambil oleh-oleh terdekat berdasarkan koordinat ---
$oleh2_terdekat = [];
if (!empty($data['latitude']) && !empty($data['longitude'])) {
    $lat_current = floatval($data['latitude']);
    $lng_current = floatval($data['longitude']);
    
    // Query untuk mencari oleh-oleh terdekat (dalam radius 10km)
    $sql_terdekat = "SELECT id_oleh2, nama_toko, latitude, longitude, barang_dijual, gambar, harga_range,
                     (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                      cos(radians(longitude) - radians($lng_current)) + 
                      sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                     FROM tb_oleh2 
                     WHERE id_oleh2 != '$id_oleh2' 
                     AND latitude IS NOT NULL 
                     AND longitude IS NOT NULL
                     AND latitude != '' 
                     AND longitude != ''
                     HAVING jarak <= 10
                     ORDER BY jarak ASC 
                     LIMIT 10";
    
    $result_terdekat = $koneksi->query($sql_terdekat);
    if ($result_terdekat && mysqli_num_rows($result_terdekat) > 0) {
        while ($row = mysqli_fetch_assoc($result_terdekat)) {
            $oleh2_terdekat[] = $row;
        }
    }
}

// --- Proses Kritik & Saran ---
$success_msg = $error_msg = '';

if (isset($_POST['btnSubmit']) && $show_kritik_form) {
    $rating   = isset($_POST['rating'])   ? intval($_POST['rating']) : 0;
    $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';
    $tgl      = date('Y-m-d H:i:s');
    if ($id_user > 0 && $rating >= 1 && $rating <= 5 && $komentar !== '') {
        $komentar_safe = mysqli_real_escape_string($koneksi, $komentar);
        $koneksi->query("INSERT INTO tb_kritik_saran_oleh2
            (id_oleh2, id_pengguna, rating, komentar, tanggal)
            VALUES ('$id_oleh2', $id_user, $rating, '$komentar_safe', '$tgl')");
        $_SESSION['feedback_success_oleh2'] = true;
        $current_file = basename($_SERVER['SCRIPT_NAME']);
        echo "<script>window.location.href = '$current_file?page=MyApp/detail_oleh2&id=$id_oleh2#kritik';</script>";
        exit;
    } else {
        $error_msg = 'Silakan isi rating dan komentar dengan benar.';
    }
}

// --- List kritik & saran (user saja, bukan admin) ---
$fb = null;
if ($show_feedback_list) {
    $fb = $koneksi->query(
        "SELECT ks.*, u.nama_pengguna
         FROM tb_kritik_saran_oleh2 ks
         JOIN tb_pengguna u ON ks.id_pengguna = u.id_pengguna
         WHERE ks.id_oleh2 = '$id_oleh2'
         ORDER BY ks.tanggal DESC"
    );
}

// --- Pesan sukses ---
if (isset($_SESSION['feedback_success_oleh2']) && $_SESSION['feedback_success_oleh2'] === true) {
    $success_msg = 'Feedback Anda berhasil disimpan!';
    unset($_SESSION['feedback_success_oleh2']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Oleh-oleh</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
body {
    background: linear-gradient(135deg, #ffb347 0%, #fff5e6 100%);
    color: #8b4513;
}
.container { 
    margin-top: 30px; 
    margin-bottom: 38px; 
}
.content-header h1 {
    font-weight: 900;
    color: #5d2e0a;
    text-align: center;
    margin-bottom: 24px;
}
.box-primary {
    background: rgba(255,255,255,0.99);
    border-radius: 18px;
    padding: 20px 20px 15px 20px;
    box-shadow: 0 6px 22px 0 rgba(255,179,71,0.12);
    max-width: 1500px;
    margin: 0 auto 30px auto;
    border: 2px solid #ff8c42;
    border-top: 4px solid #ff6b35 !important;
}
.gallery-container { 
    margin-bottom: 30px; 
    text-align: center; 
}
.main-image-container {
    position: relative; 
    border-radius: 15px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(255,179,71,0.11); 
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
    background: rgba(255,179,71,0.14);
    backdrop-filter: blur(5px);
    color: #ff6b35;
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
    background: #ff6b35;
    color: white;
    opacity: 1;
    transform: scale(1.1);
}
.image-counter {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: #ff6b35;
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
    background: #fff5e6;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(255,179,71,0.09);
    margin-bottom: 25px;
    border: 2px solid #ff8c42;
}
.info-container h4 { 
    color: #d2691e; 
    font-weight: 700; 
    margin-bottom: 15px;
}
.coordinate-display {
    background: #fef7f0;
    padding: 15px;
    border-radius: 10px;
    margin: 15px 0;
    border-left: 4px solid #ff6b35;
    box-shadow: 0 2px 8px rgba(255,179,71,0.08);
}
.coordinate-display .coord-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.coordinate-display .coord-item:last-child {
    margin-bottom: 0;
}
.coordinate-display .coord-value {
    font-family: 'Courier New', monospace;
    background: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
    color: #d2691e;
    border: 1px solid #ff8c42;
}
.oleh2-terdekat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 15px;
}
.oleh2-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(255,179,71,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}
.oleh2-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(255,179,71,0.2);
    border-color: #ff8c42;
    text-decoration: none;
    color: inherit;
}
.oleh2-card-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
}
.oleh2-card-body {
    padding: 15px;
}
.oleh2-card-title {
    font-weight: bold;
    color: #d2691e;
    margin-bottom: 8px;
    font-size: 1.1em;
}
.oleh2-card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}
.oleh2-card-distance {
    background: #fff5e6;
    color: #d2691e;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: bold;
    border: 1px solid #ff8c42;
}
.oleh2-card-price {
    background: #fff0f0;
    color: #dc3545;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}
.barang-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}
.barang-item {
    background: #fff;
    padding: 10px;
    border-radius: 8px;
    border-left: 4px solid #ff8c42;
    box-shadow: 0 2px 5px rgba(255,179,71,0.1);
}
.special-highlight {
    background: linear-gradient(135deg, #ff8c42, #ff6b35);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin: 15px 0;
    box-shadow: 0 4px 15px rgba(255,179,71,0.3);
}
.price-range {
    font-size: 1.2em;
    font-weight: bold;
    color: #5d2e0a;
    background: #fff0f0;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    border: 2px solid #dc3545;
}
.btn-oleh2 {
    border-radius: 20px;
    font-weight: bold;
    padding: 8px 22px;
    background: linear-gradient(90deg, #ff8c42 20%, #ff6b35 100%);
    border: none;
    color: white;
    margin: 5px;
    transition: background 0.3s;
}
.btn-oleh2:hover { 
    background: linear-gradient(90deg, #ff6b35 0%, #d2691e 100%);
    color: white;
}
.star-rating { 
    display: flex; 
    flex-direction: row-reverse; 
    justify-content: flex-start; 
    font-size: 30px;
}
.star-rating input { display: none; }
.star-rating label { 
    color: #ffd6b3; 
    cursor: pointer; 
    margin-right: 4px;
    transition: color 0.18s;
}
.star-rating input:checked ~ label, 
.star-rating label:hover, 
.star-rating label:hover ~ label { 
    color: #ff6b35;
}
.star-rating input:focus ~ label { 
    outline: 2px solid #ff6b35;
}
.card-feedback {
    margin-bottom: 1rem; 
    background: #fff5e6; 
    border-radius: 10px; 
    border: 2px solid #ff8c42;
    box-shadow: 0 2px 8px rgba(255,179,71,0.09);
    padding: 15px;
    transition: box-shadow 0.16s;
}
.card-feedback:hover {
    box-shadow: 0 4px 12px rgba(255,179,71,0.15);
}
.card-feedback strong { 
    color: #d2691e;
}
.card-feedback small { 
    color: #999; 
}
.panel.panel-default {
    background: #fff; 
    padding: 14px 13px 17px 13px;
    border-radius: 10px; 
    box-shadow: 0 2px 7px rgba(255,179,71,0.07);
    margin-top: 18px;
    margin-bottom: 14px;
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
    border: 2px solid #ff8c42;
}
#map {
    height: 400px;
    width: 100%;
    border-radius: 15px;
    border: 2px solid #ff8c42;
}
.no-data {
    text-align: center;
    color: #d2691e;
    font-style: italic;
    padding: 20px;
}
.alert-success {
    background-color: #fff5e6 !important;
    border-color: #ff8c42 !important;
    color: #d2691e !important;
}
.alert-danger {
    background-color: #ffeaea !important;
    border-color: #ff6b6b !important;
    color: #d63384 !important;
}
.form-control:focus {
    border-color: #ff8c42;
    box-shadow: 0 0 0 0.2rem rgba(255,179,71,0.25);
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
    #map { height: 300px; }
    .oleh2-terdekat-grid {
        grid-template-columns: 1fr;
    }
    .barang-grid {
        grid-template-columns: 1fr;
    }
    .coordinate-display .coord-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .coordinate-display .coord-value {
        margin-top: 5px;
        width: 100%;
        text-align: center;
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
        height: 250px; 
    } 
    .box-primary { 
        padding: 3vw 2vw; 
    }
}
.swal2-popup { 
    font-size: 1.18rem !important; 
}
.swal2-confirm {
    background-color: #ff6b35 !important;
}
.swal2-confirm:hover {
    background-color: #ff8c42 !important;
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<div class="container">
    <section class="content-header">
        <h1>Detail Oleh-oleh</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <h2><?= htmlspecialchars($data['nama_toko']) ?></h2>
                
                <!-- Galeri Gambar -->
                <div class="gallery-container">
                    <div class="main-image-container">
                        <?php if (!empty($gambarList)): ?>
                            <img id="mainImage" src="uploads/<?= htmlspecialchars($gambarList[0]) ?>">
                            
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
                                <h4><?= htmlspecialchars($data['nama_toko']) ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="no-data">Tidak ada gambar tersedia</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info Toko -->
                <div class="info-container">
                    <h4><i class="fa fa-store text-primary"></i> Informasi Toko</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fa fa-map-marker-alt text-danger"></i> <strong>Alamat:</strong> 
                                <?= htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}, {$data['provinsi']}") ?>
                            </p>
                            <p><i class="fa fa-clock text-warning"></i> <strong>Jam Operasional:</strong> <?= htmlspecialchars($data['jam_operasional']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="price-range">
                                <i class="fa fa-money-bill-wave"></i> 
                                <?= htmlspecialchars($data['harga_range']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Koordinat -->
                    <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                    <div class="coordinate-display">
                        <h5><i class="fa fa-crosshairs"></i> Koordinat Lokasi</h5>
                        <div class="coord-item">
                            <span><i class="fa fa-location-arrow"></i> <strong>Latitude:</strong></span>
                            <span class="coord-value"><?= htmlspecialchars($data['latitude']) ?></span>
                        </div>
                        <div class="coord-item">
                            <span><i class="fa fa-location-arrow"></i> <strong>Longitude:</strong></span>
                            <span class="coord-value"><?= htmlspecialchars($data['longitude']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Barang Dijual -->
                <?php if (!empty($data['barang_dijual'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-gift text-info"></i> Barang yang Dijual</h4>
                    <div class="barang-grid">
                        <?php 
                        $barangList = array_filter(array_map('trim', explode(',', $data['barang_dijual'])));
                        foreach ($barangList as $barang): ?>
                            <div class="barang-item">
                                <i class="fa fa-check-circle text-success"></i> <?= htmlspecialchars($barang) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Oleh-oleh Terdekat -->
                <?php if (!empty($oleh2_terdekat)): ?>
                <div class="info-container">
                    <h4><i class="fa fa-gift text-primary"></i> Toko Oleh-oleh Terdekat</h4>
                    <div class="oleh2-terdekat-grid">
                        <?php foreach ($oleh2_terdekat as $oleh2): ?>
                            <a href="?page=MyApp/detail_oleh2&id=<?= $oleh2['id_oleh2'] ?>" class="oleh2-card">
                                <?php 
                                $gambar_oleh2 = '';
                                if (!empty($oleh2['gambar'])) {
                                    $gambar_list = array_filter(explode(',', $oleh2['gambar']));
                                    if (!empty($gambar_list)) {
                                        $gambar_oleh2 = $gambar_list[0];
                                    }
                                }
                                ?>
                                <?php if ($gambar_oleh2): ?>
                                    <img src="uploads/<?= htmlspecialchars($gambar_oleh2) ?>" alt="<?= htmlspecialchars($oleh2['nama_toko']) ?>" class="oleh2-card-image">
                                <?php else: ?>
                                    <div class="oleh2-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                        <i class="fa fa-store" style="font-size: 3em; color: #d2691e;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="oleh2-card-body">
                                    <div class="oleh2-card-title"><?= htmlspecialchars($oleh2['nama_toko']) ?></div>
                                    <p><small><?= htmlspecialchars(substr($oleh2['barang_dijual'], 0, 50)) ?>...</small></p>
                                    <div class="oleh2-card-info">
                                        <span class="oleh2-card-price">
                                            <i class="fa fa-money-bill"></i> <?= htmlspecialchars($oleh2['harga_range']) ?>
                                        </span>
                                        <span class="oleh2-card-distance">
                                            <i class="fa fa-route"></i> <?= number_format($oleh2['jarak'], 1) ?> km
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Peta Lokasi -->
                <?php if(!empty($data['latitude']) && !empty($data['longitude'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-map"></i> Lokasi Toko</h4>
                    <div id="map"></div>
                </div>
                <?php endif; ?>

                <div class="info-container">
                    <h4>Deskripsi</h4>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>

                <div style="margin-top: 25px; text-align: center;">
                    <a href="?page=MyApp/data_oleh2" class="btn btn-oleh2"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>

                <!-- Kritik & Saran -->
                <?php if ($show_kritik_form): ?>
<!-- Saran & Rating -->
<a name="kritik"></a>
<div class="panel panel-default">
    <h3 style="color: #ff6b35"><i class="fa fa-comments"></i> Saran & Rating</h3>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>
    
    <form method="post" id="kritikForm">
        <div class="form-group">
            <label><i class="fa fa-star text-warning"></i> <strong>Rating Anda:</strong></label><br>
            <div class="star-rating" style="margin-top: -5px;">
                <?php for($i=5; $i>=1; $i--): ?>
                    <input id="star<?= $i ?>" type="radio" name="rating" value="<?= $i ?>" required>
                    <label for="star<?= $i ?>" class="fa fa-star"></label>
                <?php endfor; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="komentar">
                <i class="fa fa-edit text-info"></i> <strong>Komentar:</strong>
            </label>
            <textarea name="komentar" id="komentar" rows="3" class="form-control" 
                      placeholder="Tulis saran atau kritik Anda..." required></textarea>
        </div>
        <button type="submit" name="btnSubmit" class="btn btn-oleh2">
            <i class="fa fa-paper-plane"></i> Kirim Saran
        </button>
    </form>

    <!-- List Kritik & Saran (HANYA untuk user) -->
    <?php if ($show_feedback_list): ?>
    <div style="margin-top: 18px; max-width: 1500px; margin-left: auto; margin-right: auto;">
        <h4 style="color: #ff6b35; font-weight: 600;"><i class="fa fa-user-friends"></i> Masukan dari Pengunjung Lain</h4>
        <?php if ($fb && mysqli_num_rows($fb) > 0): ?>
            <?php while($d = mysqli_fetch_assoc($fb)): ?>
                <div class="card-feedback">
                    <div class="row" style="align-items: center;">
                        <div class="col-sm-8">
                            <strong><?= htmlspecialchars($d['nama_pengguna']) ?></strong>
                            <small style="margin-left: 10px;"><i class="fa fa-clock"></i> <?= date('d M Y, H:i', strtotime($d['tanggal'])) ?></small>
                        </div>
                        <div class="col-sm-4 text-right">
                            <div style="color: #ff6b35; font-size: 1.1em;">
                                <?php 
                                for($i=1; $i<=5; $i++) {
                                    if($i <= $d['rating']) {
                                        echo '<i class="fa fa-star"></i>';
                                    } else {
                                        echo '<i class="fa fa-star-o"></i>';
                                    }
                                }
                                ?>
                                <span style="margin-left: 8px; font-weight: bold;"><?= $d['rating'] ?>/5</span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <p style="margin-bottom: 0;"><?= nl2br(htmlspecialchars($d['komentar'])) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fa fa-comments fa-3x" style="color: #ddd; margin-bottom: 10px;"></i><br>
                Belum ada masukan dari pengunjung lain.
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
// Galeri gambar
<?php if (!empty($gambarList) && count($gambarList) > 1): ?>
const images = <?= json_encode($gambarList) ?>;
let currentIndex = 0;

function updateImage() {
    document.getElementById('mainImage').src = 'uploads/' + images[currentIndex];
    document.getElementById('currentImageNum').textContent = currentIndex + 1;
}

document.getElementById('nextBtn').addEventListener('click', function() {
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
});

document.getElementById('prevBtn').addEventListener('click', function() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateImage();
});

// Auto slide setiap 5 detik
setInterval(function() {
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
}, 5000);
<?php endif; ?>

// Peta Leaflet
// Map functionality
<?php if(!empty($data['latitude']) && !empty($data['longitude'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const lat = <?= floatval($data['latitude']) ?>;
    const lng = <?= floatval($data['longitude']) ?>;
    const nama = "<?= htmlspecialchars($data['nama_toko'], ENT_QUOTES) ?>";
    
    // Initialize map
    const map = L.map('map').setView([lat, lng], 14);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add marker for main location
    const mainMarker = L.marker([lat, lng])
        .addTo(map)
        .bindPopup(`<strong>${nama}</strong><br>Toko Oleh-oleh`)
        .openPopup();
    
    // Add markers for nearby oleh-oleh
    <?php if (!empty($oleh2_terdekat)): ?>
    const nearbyMarkers = [];
    <?php foreach ($oleh2_terdekat as $oleh2): ?>
        <?php if (!empty($oleh2['latitude']) && !empty($oleh2['longitude'])): ?>
            const marker<?= $oleh2['id_oleh2'] ?> = L.marker([<?= $oleh2['latitude'] ?>, <?= $oleh2['longitude'] ?>], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #ffc107; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 15px;"><i class="fa fa-gift"></i></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(map)
            .bindPopup(`
                <div style="text-align: center; min-width: 150px;">
                    <h6 style="color: #ff6b35; margin-bottom: 8px; font-weight: bold;">
                        <?= htmlspecialchars($oleh2['nama_toko']) ?>
                    </h6>
                    <p style="margin: 5px 0; font-size: 0.8em; color: #666;">
                        <i class="fa fa-gift" style="margin-right: 4px;"></i>
                        <?= htmlspecialchars(substr($oleh2['barang_dijual'], 0, 30)) ?><?= strlen($oleh2['barang_dijual']) > 30 ? '...' : '' ?>
                    </p>
                    <p style="margin: 5px 0; font-size: 0.85em; color: #ff6b35; font-weight: 500;">
                        <i class="fa fa-route" style="margin-right: 4px;"></i>
                        Jarak: <?= number_format($oleh2['jarak'], 1) ?> km
                    </p>
                    <p style="margin: 5px 0; font-size: 0.8em; color: #dc3545; font-weight: 500;">
                        <i class="fa fa-money-bill" style="margin-right: 4px;"></i>
                        <?= htmlspecialchars($oleh2['harga_range']) ?>
                    </p>
                    <div style="margin-top: 10px;">
                        <a href="?page=MyApp/detail_oleh2&id=<?= $oleh2['id_oleh2'] ?>" 
                           class="btn btn-sm" 
                           style="background: #ff6b35; color: white; border: none; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; display: inline-block; transition: background 0.3s;">
                            <i class="fa fa-eye" style="margin-right: 4px;"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            `);
            
            nearbyMarkers.push(marker<?= $oleh2['id_oleh2'] ?>);
        <?php endif; ?>
    <?php endforeach; ?>
    
    <?php endif; ?>
    
    
});
<?php endif; ?>

// SweetAlert untuk pesan sukses
<?php if (!empty($success_msg)): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= $success_msg ?>',
    confirmButtonColor: '#ff6b35'
});
<?php endif; ?>

// Form validation
document.getElementById('kritikForm')?.addEventListener('submit', function(e) {
    const rating = document.querySelector('input[name="rating"]:checked');
    const komentar = document.getElementById('komentar').value.trim();
    
    if (!rating) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Silakan pilih rating terlebih dahulu.',
            confirmButtonColor: '#ff6b35'
        });
        return;
    }
    
    if (komentar === '') {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: 'Silakan isi komentar terlebih dahulu.',
            confirmButtonColor: '#ff6b35'
        });
        return;
    }
});

// Smooth scroll untuk anchor kritik
if (window.location.hash === '#kritik') {
    setTimeout(function() {
        document.querySelector('a[name="kritik"]').scrollIntoView({
            behavior: 'smooth'
        });
    }, 100);
}
</script>

</body>
</html>