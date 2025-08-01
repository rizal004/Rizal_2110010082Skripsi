<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
include "inc/koneksi.php";

// Level user: admin/pengguna
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';
$show_kritik_form = ($ses_level === 'pengguna');
$show_feedback_list = ($ses_level !== 'admin' && $ses_level !== 'administrator');

// --- Ambil ID ---
$id_kuliner = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_kuliner) {
    echo "<div style='text-align:center;padding:40px 0;'>Data tidak ditemukan.</div>"; exit;
}

// --- Ambil data kuliner ---
$sql = $koneksi->query("SELECT * FROM tb_kuliner WHERE id_kuliner = '$id_kuliner'");
$data = $sql->fetch_assoc();
if (!$data) {
    echo '<div style="text-align:center;padding:40px 0;">Data tidak ditemukan.</div>'; exit;
}

// --- Galeri gambar ---
$gambarList = array_filter(explode(',', $data['gambar']));

// --- User login ---
$id_user = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;

// --- Ambil kuliner terdekat berdasarkan koordinat ---
$kuliner_terdekat = [];
if (!empty($data['latitude']) && !empty($data['longitude'])) {
    $lat_current = floatval($data['latitude']);
    $lng_current = floatval($data['longitude']);
    
    // Query untuk mencari kuliner terdekat (dalam radius 10km)
    $sql_terdekat = "SELECT id_kuliner, nama_kuliner, latitude, longitude, special_menu, gambar, harga_range,
                     (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                      cos(radians(longitude) - radians($lng_current)) + 
                      sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                     FROM tb_kuliner 
                     WHERE id_kuliner != '$id_kuliner' 
                     AND latitude IS NOT NULL 
                     AND longitude IS NOT NULL
                     AND latitude != '' 
                     AND longitude != ''
                     HAVING jarak <= 10
                     ORDER BY jarak ASC 
                     LIMIT 6";
    
    $result_terdekat = $koneksi->query($sql_terdekat);
    if ($result_terdekat && mysqli_num_rows($result_terdekat) > 0) {
        while ($row = mysqli_fetch_assoc($result_terdekat)) {
            $kuliner_terdekat[] = $row;
        }
    }
}

// --- Proses Kritik & Saran ---
$success_msg = $error_msg = '';

// Tambah saran (hanya untuk pengguna dengan level 'pengguna')
if (isset($_POST['btnSubmit']) && $show_kritik_form) {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';
    $tgl = date('Y-m-d H:i:s');
    
    if ($id_user > 0 && $rating >= 1 && $rating <= 5 && $komentar !== '') {
        $komentar_safe = mysqli_real_escape_string($koneksi, $komentar);
        $insert_query = "INSERT INTO tb_kritik_saran_kuliner (id_kuliner, id_pengguna, rating, komentar, tanggal) VALUES ('$id_kuliner', $id_user, $rating, '$komentar_safe', '$tgl')";
        
        if ($koneksi->query($insert_query)) {
            $success_msg = 'Feedback Anda berhasil disimpan!';
        } else {
            $error_msg = 'Terjadi kesalahan saat menyimpan feedback: ' . $koneksi->error;
        }
    } else {
        $error_msg = 'Silakan isi rating dan komentar dengan benar.';
    }
}

// --- List kritik & saran (hanya untuk pengguna) ---
if ($show_kritik_form) {
    $fb = $koneksi->query(
        "SELECT ks.*, u.nama_pengguna
         FROM tb_kritik_saran_kuliner ks
         JOIN tb_pengguna u ON ks.id_pengguna = u.id_pengguna
         WHERE ks.id_kuliner = '$id_kuliner'
         ORDER BY ks.tanggal DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Kuliner</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
body {
    background: linear-gradient(135deg, #60f6b6 0%, #e6fff5 100%);
    color: #155443;
}
.container { 
    margin-top: 30px; 
    margin-bottom: 38px; 
}
.content-header h1 {
    font-weight: 900;
    color: rgb(11, 15, 14);
    text-align: center;
    margin-bottom: 24px;
}
.box-primary {
    background: rgba(255,255,255,0.99);
    border-radius: 18px;
    padding: 20px 20px 15px 20px;
    box-shadow: 0 6px 22px 0 rgba(58,210,159,0.12);
    max-width: 1500px;
    margin: 0 auto 30px auto;
    border: 2px solid #36ad85;
    border-top: 4px solid #36ad85 !important;
}
.gallery-container { 
    margin-bottom: 30px; 
    text-align: center; 
}
.main-image-container {
    position: relative; 
    border-radius: 15px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(58,210,159,0.11); 
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
    background: rgba(58,210,159,0.14);
    backdrop-filter: blur(5px);
    color: #36ad85;
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
    background: #36ad85;
    color: white;
    opacity: 1;
    transform: scale(1.1);
}
.image-counter {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: #3ad29f;
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
    background: #e6fff5;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(58,210,159,0.09);
    margin-bottom: 25px;
    border: 2px solid #36ad85;
}
.info-container h4 { 
    color: #187761; 
    font-weight: 700; 
    margin-bottom: 15px;
}
.coordinate-display {
    background: #f0fdf7;
    padding: 15px;
    border-radius: 10px;
    margin: 15px 0;
    border-left: 4px solid #3ad29f;
    box-shadow: 0 2px 8px rgba(58,210,159,0.08);
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
    color: #187761;
    border: 1px solid #36ad85;
}
.kuliner-terdekat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 15px;
}
.kuliner-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(58,210,159,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}
.kuliner-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(58,210,159,0.2);
    border-color: #36ad85;
    text-decoration: none;
    color: inherit;
}
.kuliner-card-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
}
.kuliner-card-body {
    padding: 15px;
}
.kuliner-card-title {
    font-weight: bold;
    color: #187761;
    margin-bottom: 8px;
    font-size: 1.1em;
}
.kuliner-card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}
.kuliner-card-distance {
    background: #e6fff5;
    color: #187761;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: bold;
    border: 1px solid #36ad85;
}
.kuliner-card-price {
    background: #f0fff4;
    color: #28a745;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}
.menu-item {
    background: #fff;
    padding: 10px;
    border-radius: 8px;
    border-left: 4px solid #36ad85;
    box-shadow: 0 2px 5px rgba(58,210,159,0.1);
}
.special-menu-highlight {
    background: linear-gradient(135deg, #36ad85, #3ad29f);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin: 15px 0;
    box-shadow: 0 4px 15px rgba(58,210,159,0.3);
}
.price-range {
    font-size: 1.2em;
    font-weight: bold;
    color:rgb(0, 6, 1);
    background: #f0fff4;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    border: 2px solid #28a745;
}
.btn-kuliner {
    border-radius: 20px;
    font-weight: bold;
    padding: 8px 22px;
    background: linear-gradient(90deg, #36ad85 20%, #3ad29f 100%);
    border: none;
    color: white;
    margin: 5px;
    transition: background 0.3s;
}
.btn-kuliner:hover { 
    background: linear-gradient(90deg, #3ad29f 0%, #187761 100%);
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
    color: #b8e7d6; 
    cursor: pointer; 
    margin-right: 4px;
    transition: color 0.18s;
}
.star-rating input:checked ~ label, 
.star-rating label:hover, 
.star-rating label:hover ~ label { 
    color: #3ad29f;
}
.star-rating input:focus ~ label { 
    outline: 2px solid #3ad29f;
}
.card-feedback {
    margin-bottom: 1rem; 
    background: #e6fff5; 
    border-radius: 10px; 
    border: 2px solid #3ad29f;
    box-shadow: 0 2px 8px rgba(58,210,159,0.09);
    padding: 15px;
    transition: box-shadow 0.16s;
}
.card-feedback:hover {
    box-shadow: 0 4px 12px rgba(58,210,159,0.15);
}
.card-feedback strong { 
    color: #187761;
}
.card-feedback small { 
    color: #999; 
}
.panel.panel-default {
    background: #fff; 
    padding: 14px 13px 17px 13px;
    border-radius: 10px; 
    box-shadow: 0 2px 7px rgba(58,210,159,0.07);
    margin-top: 18px;
    margin-bottom: 14px;
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
    border: 2px solid #3ad29f;
}
#map {
    height: 400px;
    width: 100%;
    border-radius: 15px;
    border: 2px solid #36ad85;
}
.no-data {
    text-align: center;
    color: #187761;
    font-style: italic;
    padding: 20px;
}
.alert-success {
    background-color: #e6fff5 !important;
    border-color: #36ad85 !important;
    color: #187761 !important;
}
.alert-danger {
    background-color: #ffeaea !important;
    border-color: #ff6b6b !important;
    color: #d63384 !important;
}
.form-control:focus {
    border-color: #36ad85;
    box-shadow: 0 0 0 0.2rem rgba(58,210,159,0.25);
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
    .kuliner-terdekat-grid {
        grid-template-columns: 1fr;
    }
    .menu-grid {
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
    background-color: #36ad85 !important;
}
.swal2-confirm:hover {
    background-color: #3ad29f !important;
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<div class="container">
    <section class="content-header">
        <h1>Detail Kuliner</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <h2><?= htmlspecialchars($data['nama_kuliner']) ?></h2>
                
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
                                <h4><?= htmlspecialchars($data['nama_kuliner']) ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="no-data">Tidak ada gambar tersedia</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info Kuliner -->
                <div class="info-container">
                    <h4><i class="fa fa-utensils text-primary"></i> Informasi </h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fa fa-map-marker-alt text-danger"></i> <strong>Alamat:</strong> 
                                <?= htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}") ?>
                            </p>
                            <p><i class="fa fa-clock text-warning"></i> <strong>Jam Operasional:</strong> <?= htmlspecialchars($data['jam_operasional']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="price-range">
                                <i class="fa fa-money-bill-wave"></i> 
                                <?php
                                $parts = explode(' - ', $data['harga_range']);
                                if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                    echo 'Rp' . number_format($parts[0], 0, ',', '.') . ' – Rp' . number_format($parts[1], 0, ',', '.');
                                } else {
                                    echo htmlspecialchars($data['harga_range']);
                                }
                                ?>
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

                <!-- Menu Spesial -->
                <?php if (!empty($data['special_menu'])): ?>
                <div class="special-menu-highlight">
                    <h4><i class="fa fa-star"></i> Menu Spesial</h4>
                    <h3><?= htmlspecialchars($data['special_menu']) ?></h3>
                </div>
                <?php endif; ?>

                <!-- Daftar Menu -->
                <?php if (!empty($data['menu'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-list text-info"></i> Daftar Menu</h4>
                    <div class="menu-grid">
                        <?php 
                        $menuList = array_filter(array_map('trim', explode(',', $data['menu'])));
                        foreach ($menuList as $menu): ?>
                            <div class="menu-item">
                                <i class="fa fa-check-circle text-success"></i> <?= htmlspecialchars($menu) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Kuliner Terdekat -->
                <?php if (!empty($kuliner_terdekat)): ?>
                <div class="info-container">
                    <h4><i class="fa fa-map-marked-alt text-primary"></i> Kuliner Terdekat</h4>
                    <div class="kuliner-terdekat-grid">
                        <?php foreach ($kuliner_terdekat as $kuliner): ?>
                            <a href="?page=MyApp/detail_kuliner&id=<?= $kuliner['id_kuliner'] ?>" class="kuliner-card">
                                <?php 
                                $gambar_kuliner = '';
                                if (!empty($kuliner['gambar'])) {
                                    $gambar_list = array_filter(explode(',', $kuliner['gambar']));
                                    if (!empty($gambar_list)) {
                                        $gambar_kuliner = $gambar_list[0];
                                    }
                                }
                                ?>
                                <?php if ($gambar_kuliner): ?>
                                    <img src="uploads/<?= htmlspecialchars($gambar_kuliner) ?>" alt="<?= htmlspecialchars($kuliner['nama_kuliner']) ?>" class="kuliner-card-image">
                                <?php else: ?>
                                    <div class="kuliner-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                        <i class="fa fa-utensils" style="font-size: 3em; color:rgb(0, 5, 10);"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="kuliner-card-body">
                                    <div class="kuliner-card-title"><?= htmlspecialchars($kuliner['nama_kuliner']) ?></div>
                                    <p><small><?= htmlspecialchars($kuliner['special_menu']) ?></small></p>
                                    <div class="kuliner-card-info">
                                        <span class="kuliner-card-price">
                                            <i class="fa fa-money-bill"></i> <?= htmlspecialchars($kuliner['harga_range']) ?>
                                        </span>
                                        <span class="kuliner-card-distance">
                                            <i class="fa fa-route"></i> <?= number_format($kuliner['jarak'], 1) ?> km
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
                    <h4><i class="fa fa-map"></i> Lokasi Kuliner</h4>
                    <div id="map"></div>
                </div>
                <?php endif; ?>

                <div class="info-container">
                    <h4>Deskripsi</h4>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>

                <div style="margin-top: 25px; text-align: center;">
                    <a href="?page=MyApp/data_kuliner" class="btn btn-kuliner"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>

                <!-- Kritik & Saran -->
                <?php if ($show_kritik_form): ?>
<!-- Kritik & Saran -->
<a name="kritik"></a>
<div class="panel panel-default">
    <h3 style="color: #36ad85"><i class="fa fa-comments"></i> Kritik & Saran</h3>
    
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
                      placeholder="Tulis kritik atau saran Anda..." required></textarea>
        </div>
        <button type="submit" name="btnSubmit" class="btn btn-kuliner">
            <i class="fa fa-paper-plane"></i> Kirim
        </button>
    </form>

    <!-- List Kritik & Saran (HANYA untuk user) -->
    <?php if ($show_feedback_list): ?>
    <div style="margin-top: 18px; max-width: 1500px; margin-left: auto; margin-right: auto;">
        <h4 style="color: #ee5a24; font-weight: 600;"><i class="fa fa-user-friends"></i> Masukan dari Pengunjung Lain</h4>
        <?php if ($fb && mysqli_num_rows($fb) > 0): ?>
            <?php while($d = mysqli_fetch_assoc($fb)): ?>
                <div class="card-feedback">
                    <div class="row" style="align-items: center;">
                        <div class="col-sm-8">
                            <strong><?= htmlspecialchars($d['nama_pengguna']) ?></strong>
                            <small style="margin-left: 10px;"><i class="fa fa-clock"></i> <?= date('d M Y, H:i', strtotime($d['tanggal'])) ?></small>
                        </div>
                        <div class="col-sm-4 text-right">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fa fa-star<?= $i <= $d['rating'] ? '' : '-o' ?>" style="color: #ffc107;"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p style="margin-top: 10px;"><?= nl2br(htmlspecialchars($d['komentar'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted"><em>Belum ada kritik atau saran untuk kuliner ini.</em></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Script untuk galeri gambar -->
<script>
    let gambarList = <?= json_encode(array_values($gambarList)) ?>;
    let currentIndex = 0;
    const total = gambarList.length;
    const mainImage = document.getElementById('mainImage');
    const currentNum = document.getElementById('currentImageNum');
    
    function updateImage(idx) {
        if (total <= 1) return;
        if (idx < 0) idx = total - 1;
        if (idx >= total) idx = 0;
        currentIndex = idx;
        mainImage.style.opacity = '0.2';
        setTimeout(() => {
            mainImage.src = 'uploads/' + gambarList[idx];
            if (currentNum) currentNum.innerText = idx + 1;
            mainImage.style.opacity = '1';
        }, 200);
    }
    
    if (total > 1) {
        document.getElementById('prevBtn').onclick = e => { e.stopPropagation(); updateImage(currentIndex - 1); };
        document.getElementById('nextBtn').onclick = e => { e.stopPropagation(); updateImage(currentIndex + 1); };
        mainImage.onclick = () => updateImage(currentIndex + 1);
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft') updateImage(currentIndex - 1);
            if (e.key === 'ArrowRight') updateImage(currentIndex + 1);
        });
        
        // Auto-slide setiap 5 detik (opsional)
        setInterval(() => {
            updateImage(currentIndex + 1);
        }, 5000);
    }
</script>

<!-- Script untuk peta Leaflet -->
<?php if(!empty($data['latitude']) && !empty($data['longitude'])): ?>
<script>
    // Inisialisasi peta
    const lat = <?= $data['latitude'] ?>;
    const lng = <?= $data['longitude'] ?>;
    const map = L.map('map').setView([lat, lng], 14);
    
    // Tambahkan tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Marker untuk lokasi kuliner
    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup(`
    <div style="text-align: center;">
        <h5 style="color: #ee5a24; margin-bottom: 10px;"><?= htmlspecialchars($data['nama_kuliner']) ?></h5>
        <p style="margin: 0;"><i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($data['alamat']) ?></p>
        <p style="margin: 5px 0;"><i class="fa fa-clock"></i> <?= htmlspecialchars($data['jam_operasional']) ?></p>
    </div>
`   );
    
    
    // Tambahkan marker untuk kuliner terdekat
    <?php if (!empty($kuliner_terdekat)): ?>
    <?php foreach ($kuliner_terdekat as $kuliner): ?>
        <?php if (!empty($kuliner['latitude']) && !empty($kuliner['longitude'])): ?>
            L.marker([<?= $kuliner['latitude'] ?>, <?= $kuliner['longitude'] ?>], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #36ad85; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 15px;"><i class="fa fa-utensils"></i></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(map)
            .bindPopup(`
                <div style="text-align: center; min-width: 100px;">
                    <h6 style="color: #36ad85; margin-bottom: 8px; font-weight: bold;">
                        <?= htmlspecialchars($kuliner['nama_kuliner']) ?>
                    </h6>
                    <p style="margin: 5px 0; font-size: 0.85em; color: #36ad85; font-weight: 500;">
                        Jarak: <?= number_format($kuliner['jarak'], 1) ?> km
                    </p>
                    <div style="margin-top: 10px;">
                        <a href="?page=MyApp/detail_kuliner&id=<?= $kuliner['id_kuliner'] ?>" 
                           class="btn btn-sm" 
                           style="background: #36ad85; color: white; border: none; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; display: inline-block; transition: background 0.3s;">
                            <i class="fa fa-eye" style="margin-right: 5px;"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            `);
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</script>
<?php endif; ?>

<!-- Script untuk SweetAlert -->
<?php if (!empty($success_msg)): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $success_msg ?>',
        confirmButtonColor: '#ee5a24',
        timer: 3000,
        timerProgressBar: true
    });
</script>
<?php endif; ?>

<!-- Script untuk validasi form -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kritikForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const rating = document.querySelector('input[name="rating"]:checked');
            const komentar = document.getElementById('komentar').value.trim();
            
            if (!rating) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Rating Diperlukan',
                    text: 'Silakan berikan rating terlebih dahulu!',
                    confirmButtonColor: '#2193b0'
                });
                return false;
            }
            
            if (komentar === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Komentar Diperlukan',
                    text: 'Silakan tulis komentar Anda!',
                    confirmButtonColor: '#2193b0'
                });
                return false;
            }
            
            if (komentar.length < 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Komentar Terlalu Pendek',
                    text: 'Komentar minimal 10 karakter!',
                    confirmButtonColor: '#2193b0'
                });
                return false;
            }
        });
    }
});

// Success/Error message handling
<?php if (!empty($success_msg)): ?>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $success_msg ?>',
        confirmButtonColor: '#2193b0'
    });
});
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '<?= $error_msg ?>',
        confirmButtonColor: '#2193b0'
    });
});
<?php endif; ?>
</script>

<!-- Script untuk smooth scroll -->
<script>
    // Smooth scroll untuk anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>

<!-- Script untuk lazy loading gambar -->
<script>
    // Lazy loading untuk gambar kuliner terdekat
    const images = document.querySelectorAll('.kuliner-card-image');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.remove('loading');
                    observer.unobserve(img);
                }
            }
        });
    });
    
    images.forEach(img => {
        imageObserver.observe(img);
    });
</script>

<!-- Script untuk responsive behavior -->
<script>
    // Responsif behavior untuk mobile
    function handleResize() {
        const isMobile = window.innerWidth <= 768;
        const mapContainer = document.getElementById('map');
        
        if (mapContainer && typeof map !== 'undefined') {
            setTimeout(() => {
                map.invalidateSize();
            }, 250);
        }
        
        // Adjust gallery height untuk mobile
        const mainImageContainer = document.querySelector('.main-image-container img');
        if (mainImageContainer && isMobile) {
            mainImageContainer.style.height = '250px';
        } else if (mainImageContainer) {
            mainImageContainer.style.height = '400px';
        }
    }
    
    window.addEventListener('resize', handleResize);
    window.addEventListener('orientationchange', handleResize);
</script>

</body>
</html>

<?php ob_end_flush(); ?>