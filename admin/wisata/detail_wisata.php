<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
include "inc/koneksi.php";

// Kritik & saran (khusus user dengan level 'pengguna')
$show_kritik_form = isset($_SESSION['ses_level']) && $_SESSION['ses_level'] === 'pengguna';

// --- Ambil ID ---
$id_wisata = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_wisata) {
    echo "<div style='text-align:center;padding:40px 0;'>Data tidak ditemukan.</div>"; exit;
}

// --- Ambil data wisata ---
$sql = $koneksi->query("SELECT * FROM tb_wisata WHERE id_wisata = '$id_wisata'");
$data = $sql->fetch_assoc();
if (!$data) {
    echo '<div style="text-align:center;padding:40px 0;">Data tidak ditemukan.</div>'; exit;
}

// --- Galeri gambar ---
$gambarList = array_filter(explode(',', $data['gambar']));

// --- User login ---
$id_user = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;

// --- Ambil wisata terdekat berdasarkan koordinat ---
$wisata_terdekat = [];
if (!empty($data['latitude']) && !empty($data['longitude'])) {
    $lat_current = floatval($data['latitude']);
    $lng_current = floatval($data['longitude']);
    
    // Query untuk mencari wisata terdekat (dalam radius 50km)
    $sql_terdekat = "SELECT id_wisata, nama_wisata, latitude, longitude, kategori, gambar,
                     (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                      cos(radians(longitude) - radians($lng_current)) + 
                      sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                     FROM tb_wisata 
                     WHERE id_wisata != '$id_wisata' 
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
            $wisata_terdekat[] = $row;
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
        $insert_query = "INSERT INTO tb_kritik_saran (id_wisata, id_pengguna, rating, komentar, tanggal) VALUES ('$id_wisata', $id_user, $rating, '$komentar_safe', '$tgl')";
        
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
         FROM tb_kritik_saran ks
         JOIN tb_pengguna u ON ks.id_pengguna = u.id_pengguna
         WHERE ks.id_wisata = '$id_wisata'
         ORDER BY ks.tanggal DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Wisata</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
body {
    background: linear-gradient(135deg, #6dd5ed 0%, #2193b0 100%);
    color: #2c3e50;
}
.container { margin-top: 30px; margin-bottom: 38px; }
.content-header h1 {
    font-weight: 900;
    color: #2c3e50;
    text-align: center;
    margin-bottom: 24px;
}
.box-primary {
    background: rgba(255,255,255,0.99);
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 6px 22px 0 rgba(33,147,176,0.14);
    max-width: 1200px;
    margin: 0 auto 30px auto;
    border: 2px solid #6dd5ed;
}
.gallery-container { 
    margin-bottom: 30px; 
    text-align: center; 
}
.main-image-container {
    position: relative; 
    border-radius: 15px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(33,147,176,0.2); 
    margin-bottom: 10px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
.main-image-container img {
    max-width: 100%; 
    width: 100%; 
    height: 400px; 
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
    background: rgba(109, 213, 237, 0.7);
    backdrop-filter: blur(5px);
    color: #2193b0;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    cursor: pointer;
    font-size: 22px;
    opacity: 0.9;
    box-shadow: 0 0 10px rgba(0,0,0,0.12);
    transition: all 0.3s ease;
}
.nav-arrows button:hover {
    background: #2193b0;
    color: white;
    opacity: 1;
    transform: scale(1.1);
}
.image-counter {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: #2193b0;
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
    background: #f0faff;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    border: 2px solid #6dd5ed;
}
.info-container h4 { 
    color: #2193b0; 
    font-weight: 700; 
    margin-bottom: 15px;
}
.coordinate-display {
    background: #e3f2fd;
    padding: 15px;
    border-radius: 10px;
    margin: 15px 0;
    border-left: 4px solid #2193b0;
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
    color: #2193b0;
}
.wisata-terdekat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 15px;
}
.wisata-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}
.wisata-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(33,147,176,0.2);
    border-color: #6dd5ed;
    text-decoration: none;
    color: inherit;
}
.wisata-card-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
}
.wisata-card-body {
    padding: 15px;
}
.wisata-card-title {
    font-weight: bold;
    color: #2193b0;
    margin-bottom: 8px;
    font-size: 1.1em;
}
.wisata-card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}
.wisata-card-distance {
    background: #e3f2fd;
    color: #2193b0;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: bold;
}
.wisata-card-category {
    background: #f0f9ff;
    color: #0369a1;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}
.btn-wisata {
    border-radius: 20px;
    font-weight: bold;
    padding: 8px 22px;
    background: linear-gradient(90deg, #6dd5ed 20%, #2193b0 100%);
    border: none;
    color: white;
    margin: 5px;
}
.btn-wisata:hover { 
    background: linear-gradient(90deg, #2193b0 10%, #6dd5ed 100%);
    color: white;
}
.btn-transaksi {
    border-radius: 20px;
    font-weight: bold;
    padding: 8px 22px;
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: white;
    margin: 5px;
}
.btn-transaksi:hover { 
    background: linear-gradient(135deg, #20c997, #28a745);
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
    color: #c3e6fa; 
    cursor: pointer; 
    margin-right: 4px;
    transition: color 0.3s ease;
}
.star-rating input:checked ~ label, 
.star-rating label:hover, 
.star-rating label:hover ~ label { 
    color: #ffc107;
}
.card-feedback {
    margin-bottom: 1rem; 
    background: #f0faff; 
    border-radius: 10px; 
    border: 2px solid #b2f0fc;
    padding: 15px;
}
.card-feedback strong { 
    color: #2193b0;
}
.panel.panel-default {
    background: #fff; 
    padding: 20px;
    border-radius: 10px; 
    margin-top: 20px;
    border: 2px solid #6dd5ed;
}
#map {
    height: 400px;
    width: 100%;
    border-radius: 15px;
    border: 2px solid #6dd5ed;
}
.no-data {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 20px;
}
@media (max-width: 768px) {
    .main-image-container img { 
        height: 250px; 
    }
    .box-primary { 
        padding: 15px;
    }
    #map { height: 300px; }
    .wisata-terdekat-grid {
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<div class="container">
    <section class="content-header">
        <h1>Detail Wisata</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <h2><?= htmlspecialchars($data['nama_wisata']) ?></h2>
                
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
                                <h4><?= htmlspecialchars($data['nama_wisata']) ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="no-data">Tidak ada gambar tersedia</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info Wisata -->
                <div class="info-container">
                    <h4><i class="fa fa-info-circle text-primary"></i> Informasi Dasar</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fa fa-tags text-info"></i> <strong>Kategori:</strong> <?= htmlspecialchars($data['kategori']) ?></p>
                            <p><i class="fa fa-ticket-alt text-success"></i> <strong>Harga Tiket:</strong> 
                                <?= is_numeric($data['harga_tiket']) && $data['harga_tiket'] > 0 
                                    ? 'Rp' . number_format($data['harga_tiket'], 0, ',', '.') . ' per orang'
                                    : htmlspecialchars($data['harga_tiket']) ?>
                            </p>
                            <p><i class="fa fa-clock text-warning"></i> <strong>Jam Operasional:</strong> <?= htmlspecialchars($data['jam_operasional']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="fa fa-map-marker-alt text-danger"></i> <strong>Alamat:</strong> <?= htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}, {$data['provinsi']}") ?></p>
                            <p><i class="fa fa-cogs text-secondary"></i> <strong>Fasilitas:</strong> <?= htmlspecialchars($data['fasilitas']) ?></p>
                            <p><i class="fa fa-road text-primary"></i> <strong>Kondisi Jalan:</strong> <?= htmlspecialchars($data['kondisi_jalan']) ?></p>
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

                <!-- Informasi Biaya -->
                <?php if (!empty($data['harga_makanan_min']) || !empty($data['harga_minuman_min']) || !empty($data['biaya_sewa']) || !empty($data['estimasi_biaya'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-money-bill-wave text-success"></i> Informasi Biaya</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if (!empty($data['harga_makanan_min']) && !empty($data['harga_makanan_max'])): ?>
                                <p><i class="fa fa-hamburger text-warning"></i> <strong>Harga Makanan:</strong> Rp<?= number_format($data['harga_makanan_min'],0,',','.') ?> - Rp<?= number_format($data['harga_makanan_max'],0,',','.') ?></p>
                            <?php endif; ?>
                            <?php if (!empty($data['harga_minuman_min']) && !empty($data['harga_minuman_max'])): ?>
                                <p><i class="fa fa-coffee text-info"></i> <strong>Harga Minuman:</strong> Rp<?= number_format($data['harga_minuman_min'],0,',','.') ?> - Rp<?= number_format($data['harga_minuman_max'],0,',','.') ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($data['biaya_sewa'])): ?>
                                <p><i class="fa fa-key text-primary"></i> <strong>Biaya Sewa:</strong> <?= htmlspecialchars($data['biaya_sewa']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($data['estimasi_biaya'])): ?>
                                <p><i class="fa fa-calculator text-success"></i> <strong>Estimasi Biaya Total:</strong> <?= htmlspecialchars($data['estimasi_biaya']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Wisata Terdekat -->
                <?php if (!empty($wisata_terdekat)): ?>
                <div class="info-container">
                    <h4><i class="fa fa-map-marked-alt text-primary"></i> Wisata Terdekat</h4>
                    <div class="wisata-terdekat-grid">
                        <?php foreach ($wisata_terdekat as $wisata): ?>
                            <a href="?page=MyApp/detail_wisata&id=<?= $wisata['id_wisata'] ?>" class="wisata-card">
                                <?php 
                                $gambar_wisata = '';
                                if (!empty($wisata['gambar'])) {
                                    $gambar_list = array_filter(explode(',', $wisata['gambar']));
                                    if (!empty($gambar_list)) {
                                        $gambar_wisata = $gambar_list[0];
                                    }
                                }
                                ?>
                                <?php if ($gambar_wisata): ?>
                                    <img src="uploads/<?= htmlspecialchars($gambar_wisata) ?>" alt="<?= htmlspecialchars($wisata['nama_wisata']) ?>" class="wisata-card-image">
                                <?php else: ?>
                                    <div class="wisata-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                        <i class="fa fa-image" style="font-size: 3em; color: #dee2e6;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="wisata-card-body">
                                    <div class="wisata-card-title"><?= htmlspecialchars($wisata['nama_wisata']) ?></div>
                                    <div class="wisata-card-info">
                                        <span class="wisata-card-category">
                                            <i class="fa fa-tag"></i> <?= htmlspecialchars($wisata['kategori']) ?>
                                        </span>
                                        <span class="wisata-card-distance">
                                            <i class="fa fa-route"></i> <?= number_format($wisata['jarak'], 1) ?> km
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
                    <h4><i class="fa fa-map"></i> Lokasi Wisata</h4>
                    <div id="map"></div>
                </div>
                <?php endif; ?>

                <div class="info-container">
                    <h4>Deskripsi</h4>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>

                <div style="margin-top: 25px; text-align: center;">
                    <a href="?page=MyApp/data_wisata" class="btn btn-wisata"><i class="fa fa-arrow-left"></i> Kembali</a>
                    <?php if(strtolower($data['harga_tiket']) !== 'gratis' && $data['harga_tiket'] > 0): ?>
                        <a href="?page=MyApp/add_transaksi&id=<?= $id_wisata ?>" class="btn btn-transaksi"><i class="fa fa-ticket"></i> Pesan Tiket</a>
                    <?php endif; ?>
                </div>

                <!-- Kritik & Saran -->
                <?php if ($show_kritik_form): ?>
<div class="panel panel-default">
    <h3 class="text-primary"><i class="fa fa-comments"></i> Kritik & Saran</h3>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    
    <form method="post" id="kritikForm">
        <div class="form-group">
            <label><i class="fa fa-star text-warning"></i> <strong>Rating Anda:</strong></label><br>
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="star5">
                <label for="star5">★</label>
                <input type="radio" name="rating" value="4" id="star4">
                <label for="star4">★</label>
                <input type="radio" name="rating" value="3" id="star3">
                <label for="star3">★</label>
                <input type="radio" name="rating" value="2" id="star2">
                <label for="star2">★</label>
                <input type="radio" name="rating" value="1" id="star1">
                <label for="star1">★</label>
            </div>
        </div>
        <div class="form-group">
            <label for="komentar">
                <i class="fa fa-edit text-info"></i> <strong>Komentar:</strong>
            </label>
            <textarea name="komentar" id="komentar" rows="3" class="form-control" 
                      placeholder="📝 Tulis kritik atau saran Anda..." required></textarea>
        </div>
        <button type="submit" name="btnSubmit" class="btn btn-wisata">
            <i class="fa fa-paper-plane"></i> Kirim
        </button>
    </form>
    
    <!-- List Kritik & Saran -->
    <div style="margin-top: 20px;">
        <h4 style="color: #2193b0;"><i class="fa fa-user-friends"></i> Masukan dari Pengguna Lain</h4>
        <?php if ($fb && mysqli_num_rows($fb) > 0): ?>
            <?php while($d = mysqli_fetch_assoc($fb)): ?>
                <div class="card-feedback">
                    <div class="row">
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
            <p class="text-muted"><em>Belum ada kritik atau saran untuk wisata ini.</em></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Script untuk galeri gambar -->
<script>
    const images = <?= json_encode($gambarList) ?>;
    let currentIndex = 0;
    const total = images.length;
    const mainImage = document.getElementById('mainImage');
    const currentNum = document.getElementById('currentImageNum');
    
    function updateImage(idx) {
        if (total <= 1) return;
        
        if (idx < 0) idx = total - 1;
        if (idx >= total) idx = 0;
        
        currentIndex = idx;
        
        if (mainImage) {
            mainImage.style.opacity = '0.2';
            setTimeout(() => {
                mainImage.src = 'uploads/' + images[idx];
                mainImage.style.opacity = '1';
                if (currentNum) currentNum.innerText = idx + 1;
            }, 200);
        }
    }
    
    // Set up navigation buttons
    // Set up navigation buttons
if (total > 1) {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (prevBtn) {
        prevBtn.onclick = (e) => {
            e.preventDefault();
            updateImage(currentIndex - 1);
        };
    }
    
    if (nextBtn) {
        nextBtn.onclick = (e) => {
            e.preventDefault();
            updateImage(currentIndex + 1);
        };
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            updateImage(currentIndex - 1);
        } else if (e.key === 'ArrowRight') {
            updateImage(currentIndex + 1);
        }
    });
    
    // Auto-slide functionality (optional)
    let autoSlideInterval;
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            updateImage(currentIndex + 1);
        }, 5000); // Change image every 5 seconds
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // Start auto-slide
    startAutoSlide();
    
    // Pause auto-slide on hover
    const imageContainer = document.querySelector('.main-image-container');
    if (imageContainer) {
        imageContainer.addEventListener('mouseenter', stopAutoSlide);
        imageContainer.addEventListener('mouseleave', startAutoSlide);
    }
}

// Map functionality
<?php if(!empty($data['latitude']) && !empty($data['longitude'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const lat = <?= floatval($data['latitude']) ?>;
    const lng = <?= floatval($data['longitude']) ?>;
    const nama = "<?= htmlspecialchars($data['nama_wisata'], ENT_QUOTES) ?>";
    
    // Initialize map
    const map = L.map('map').setView([lat, lng], 15);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add marker for main location
    const mainMarker = L.marker([lat, lng])
        .addTo(map)
        .bindPopup(`<strong>${nama}</strong><br>Lokasi Wisata`)
        .openPopup();
    
    // Add markers for nearby attractions
    <?php if (!empty($wisata_terdekat)): ?>
    const nearbyMarkers = [];
    <?php if (!empty($wisata_terdekat)): ?>
    <?php foreach ($wisata_terdekat as $wisata): ?>
        <?php if (!empty($wisata['latitude']) && !empty($wisata['longitude'])): ?>
            L.marker([<?= $wisata['latitude'] ?>, <?= $wisata['longitude'] ?>], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #2193b0; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 15px;"><i class="fa fa-map-marked-alt"></i></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(map)
            .bindPopup(`
                <div style="text-align: center; min-width: 100px;">
                    <h6 style="color: #2193b0; margin-bottom: 8px; font-weight: bold;">
                        <?= htmlspecialchars($wisata['nama_wisata']) ?>
                    </h6>
                    <?php if (!empty($wisata['alamat'])): ?>
                        <p style="margin: 5px 0; font-size: 0.8em; color: #888;">
                            <i class="fa fa-map-marker-alt" style="margin-right: 4px;"></i>
                            <?= htmlspecialchars($wisata['alamat']) ?>
                        </p>
                    <?php endif; ?>
                    <p style="margin: 5px 0; font-size: 0.85em; color: #2193b0; font-weight: 500;">
                        Jarak: <?= number_format($wisata['jarak'], 1) ?> km
                    </p>
                    <div style="margin-top: 10px;">
                        <a href="?page=MyApp/detail_wisata&id=<?= $wisata['id_wisata'] ?>" 
                           class="btn btn-sm" 
                           style="background: #2193b0; color: white; border: none; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; display: inline-block; transition: background 0.3s;">
                            <i class="fa fa-eye" style="margin-right: 4px;"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            `);
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
    
    
    // Fit map to show all markers
    if (nearbyMarkers.length > 0) {
        const group = new L.featureGroup([mainMarker, ...nearbyMarkers]);
        map.fitBounds(group.getBounds().pad(0.1));
    }
    <?php endif; ?>
});
<?php endif; ?>

// Form validation
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

// Touch/swipe support for mobile
if (total > 1) {
    let startX = 0;
    let endX = 0;
    
    const imageContainer = document.querySelector('.main-image-container');
    if (imageContainer) {
        imageContainer.addEventListener('touchstart', function(e) {
            startX = e.changedTouches[0].screenX;
        });
        
        imageContainer.addEventListener('touchend', function(e) {
            endX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const threshold = 50; // Minimum swipe distance
            const diff = startX - endX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    // Swipe left - next image
                    updateImage(currentIndex + 1);
                } else {
                    // Swipe right - previous image
                    updateImage(currentIndex - 1);
                }
            }
        }
    }
}
</script>

<!-- Bootstrap & jQuery -->
<script src="bootstrap/js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

</body>
</html>