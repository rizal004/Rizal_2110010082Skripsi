<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
include "inc/koneksi.php";

// Level user: admin/pengguna
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';
$show_kritik_form = ($ses_level === 'pengguna');
$show_feedback_list = ($ses_level !== 'admin' && $ses_level !== 'administrator');

// --- Ambil ID ---
$id_hotel = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_hotel) {
    echo "<div style='text-align:center;padding:40px 0;'>Data tidak ditemukan.</div>"; exit;
}

// --- Ambil data hotel ---
$sql = $koneksi->query("SELECT * FROM tb_hotel WHERE id_hotel = '$id_hotel'");
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
    
    // Query untuk mencari wisata terdekat (dalam radius 15km)
    $sql_wisata = "SELECT id_wisata, nama_wisata, latitude, longitude, harga_tiket, gambar, deskripsi,
                   (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians($lng_current)) + 
                    sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                   FROM tb_wisata 
                   WHERE latitude IS NOT NULL 
                   AND longitude IS NOT NULL
                   AND latitude != '' 
                   AND longitude != ''
                   HAVING jarak <= 15
                   ORDER BY jarak ASC 
                   LIMIT 6";
    
    $result_wisata = $koneksi->query($sql_wisata);
    if ($result_wisata && mysqli_num_rows($result_wisata) > 0) {
        while ($row = mysqli_fetch_assoc($result_wisata)) {
            $wisata_terdekat[] = $row;
        }
    }
}

// --- Ambil kuliner terdekat berdasarkan koordinat ---
$kuliner_terdekat = [];
if (!empty($data['latitude']) && !empty($data['longitude'])) {
    $lat_current = floatval($data['latitude']);
    $lng_current = floatval($data['longitude']);
    
    // Query untuk mencari kuliner terdekat (dalam radius 10km)
    $sql_kuliner = "SELECT id_kuliner, nama_kuliner, latitude, longitude, special_menu, gambar, harga_range,
                    (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                     cos(radians(longitude) - radians($lng_current)) + 
                     sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                    FROM tb_kuliner 
                    WHERE latitude IS NOT NULL 
                    AND longitude IS NOT NULL
                    AND latitude != '' 
                    AND longitude != ''
                    HAVING jarak <= 10
                    ORDER BY jarak ASC 
                    LIMIT 6";
    
    $result_kuliner = $koneksi->query($sql_kuliner);
    if ($result_kuliner && mysqli_num_rows($result_kuliner) > 0) {
        while ($row = mysqli_fetch_assoc($result_kuliner)) {
            $kuliner_terdekat[] = $row;
        }
    }
}

// --- Ambil oleh-oleh terdekat berdasarkan koordinat ---
$oleh2_terdekat = [];
if (!empty($data['latitude']) && !empty($data['longitude'])) {
    $lat_current = floatval($data['latitude']);
    $lng_current = floatval($data['longitude']);
    
    // Query untuk mencari oleh-oleh terdekat (dalam radius 12km)
    $sql_oleh2 = "SELECT id_oleh2, nama_toko, latitude, longitude, harga_range, gambar, deskripsi,
                  (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                   cos(radians(longitude) - radians($lng_current)) + 
                   sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                  FROM tb_oleh2 
                  WHERE latitude IS NOT NULL 
                  AND longitude IS NOT NULL
                  AND latitude != '' 
                  AND longitude != ''
                  HAVING jarak <= 12
                  ORDER BY jarak ASC 
                  LIMIT 6";
    
    $result_oleh2 = $koneksi->query($sql_oleh2);
    if ($result_oleh2 && mysqli_num_rows($result_oleh2) > 0) {
        while ($row = mysqli_fetch_assoc($result_oleh2)) {
            $oleh2_terdekat[] = $row;
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
        $insert_query = "INSERT INTO tb_kritik_saran_hotel (id_hotel, id_pengguna, rating, komentar, tanggal) VALUES ('$id_hotel', $id_user, $rating, '$komentar_safe', '$tgl')";
                 
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
         FROM tb_kritik_saran_hotel ks 
         JOIN tb_pengguna u ON ks.id_pengguna = u.id_pengguna 
         WHERE ks.id_hotel = '$id_hotel' 
         ORDER BY ks.tanggal DESC"
    );
}
function formatWhatsAppNumber($number) {
    // Hilangkan semua karakter non-digit
    $number = preg_replace('/[^0-9]/', '', $number);
    
    // Jika dimulai dengan 0, ganti dengan 62
    if (substr($number, 0, 1) === '0') {
        $number = '62' . substr($number, 1);
    }
    // Jika dimulai dengan +62, hilangkan +
    elseif (substr($number, 0, 3) === '+62') {
        $number = substr($number, 1);
    }
    // Jika tidak dimulai dengan 62, tambahkan 62
    elseif (substr($number, 0, 2) !== '62') {
        $number = '62' . $number;
    }
    
    return $number;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Hotel</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #2c3e50;
}
.container { 
    margin-top: 30px; 
    margin-bottom: 38px; 
}
.content-header h1 {
    font-weight: 900;
    color: #fff;
    text-align: center;
    margin-bottom: 24px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}
.box-primary {
    background: rgba(255,255,255,0.99);
    border-radius: 18px;
    padding: 20px 20px 15px 20px;
    box-shadow: 0 6px 22px 0 rgba(102,126,234,0.12);
    max-width: 1500px;
    margin: 0 auto 30px auto;
    border: 2px solid #667eea;
    border-top: 4px solid #667eea !important;
}
.gallery-container { 
    margin-bottom: 30px; 
    text-align: center; 
}
.main-image-container {
    position: relative; 
    border-radius: 15px; 
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(102,126,234,0.11); 
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
    background: rgba(102,126,234,0.2);
    backdrop-filter: blur(5px);
    color: #667eea;
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
    background: #667eea;
    color: white;
    opacity: 1;
    transform: scale(1.1);
}
.image-counter {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background: #667eea;
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
    box-shadow: 0 5px 15px rgba(102,126,234,0.09);
    margin-bottom: 25px;
    border: 2px solid #667eea;
}
.info-container h4 { 
    color: #4a5568; 
    font-weight: 700; 
    margin-bottom: 15px;
}
.coordinate-display {
    background: #e6f3ff;
    padding: 15px;
    border-radius: 10px;
    margin: 15px 0;
    border-left: 4px solid #667eea;
    box-shadow: 0 2px 8px rgba(102,126,234,0.08);
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
    color: #4a5568;
    border: 1px solid #667eea;
}
.nearby-slider-container {
    position: relative;
    margin-top: 15px;
    overflow: hidden;
}

.nearby-slider {
    display: flex;
    transition: transform 0.3s ease;
    gap: 20px;
}

.nearby-card {
    flex: 0 0 280px; /* Fixed width untuk setiap card */
    min-width: 280px;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(102,126,234,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}

.nearby-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(102,126,234,0.2);
    border-color: #667eea;
    text-decoration: none;
    color: inherit;
}

.nearby-card-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
}

.nearby-card-body {
    padding: 15px;
}

.nearby-card-title {
    font-weight: bold;
    color: #4a5568;
    margin-bottom: 8px;
    font-size: 1.1em;
}

.nearby-card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.nearby-card-distance {
    background: #e6f3ff;
    color: #4a5568;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: bold;
    border: 1px solid #667eea;
}

.nearby-card-price {
    background: #f0fff4;
    color: #28a745;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 500;
}

/* Slider Navigation Buttons */
.slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(102,126,234,0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    font-size: 18px;
    z-index: 10;
    transition: all 0.3s ease;
}
.slider-nav:hover {
    background: #667eea;
    transform: translateY(-50%) scale(1.1);
}

.slider-nav.prev {
    left: -15px;
}

.slider-nav.next {
    right: -15px;
}

/* Slider Dots */
.slider-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 15px;
}

.slider-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #cbd5e0;
    cursor: pointer;
    transition: background 0.3s ease;
}

.slider-dot.active {
    background: #667eea;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .nearby-card {
        flex: 0 0 250px;
        min-width: 250px;
    }
    
    .slider-nav {
        width: 35px;
        height: 35px;
        font-size: 16px;
    }
    
    .slider-nav.prev {
        left: -10px;
    }
    
    .slider-nav.next {
        right: -10px;
    }
}

@media (max-width: 500px) {
    .nearby-card {
        flex: 0 0 220px;
        min-width: 220px;
    }
}
.facility-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}
.facility-item {
    background: #fff;
    padding: 10px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    box-shadow: 0 2px 5px rgba(102,126,234,0.1);
}
.hotel-highlight {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin: 15px 0;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
}
.price-range {
    font-size: 1.2em;
    font-weight: bold;
    color: #2c3e50;
    background: #f0fff4;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    border: 2px solid #28a745;
}
.btn-hotel {
    border-radius: 20px;
    font-weight: bold;
    padding: 8px 22px;
    background: linear-gradient(90deg, #667eea 20%, #764ba2 100%);
    border: none;
    color: white;
    margin: 5px;
    transition: background 0.3s;
}
.btn-hotel:hover { 
    background: linear-gradient(90deg, #764ba2 0%, #667eea 100%);
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
    color: #cbd5e0; 
    cursor: pointer; 
    margin-right: 4px;
    transition: color 0.18s;
}
.star-rating input:checked ~ label, 
.star-rating label:hover, 
.star-rating label:hover ~ label { 
    color: #667eea;
}
.star-rating input:focus ~ label { 
    outline: 2px solid #667eea;
}
.card-feedback {
    margin-bottom: 1rem; 
    background: #f8f9ff; 
    border-radius: 10px; 
    border: 2px solid #667eea;
    box-shadow: 0 2px 8px rgba(102,126,234,0.09);
    padding: 15px;
    transition: box-shadow 0.16s;
}
.card-feedback:hover {
    box-shadow: 0 4px 12px rgba(102,126,234,0.15);
}
.card-feedback strong { 
    color: #4a5568;
}
.card-feedback small { 
    color: #999; 
}
.panel.panel-default {
    background: #fff; 
    padding: 14px 13px 17px 13px;
    border-radius: 10px; 
    box-shadow: 0 2px 7px rgba(102,126,234,0.07);
    margin-top: 18px;
    margin-bottom: 14px;
    max-width: 1500px;
    margin-left: auto;
    margin-right: auto;
    border: 2px solid #667eea;
}
#map {
    height: 400px;
    width: 100%;
    border-radius: 15px;
    border: 2px solid #667eea;
}
.no-data {
    text-align: center;
    color: #4a5568;
    font-style: italic;
    padding: 20px;
}
.alert-success {
    background-color: #f8f9ff !important;
    border-color: #667eea !important;
    color: #4a5568 !important;
}
.alert-danger {
    background-color: #ffeaea !important;
    border-color: #ff6b6b !important;
    color: #d63384 !important;
}
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
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
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 8px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
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
    .nearby-grid {
        grid-template-columns: 1fr;
    }
    .facility-grid {
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
    background-color: #667eea !important;
}
.swal2-confirm:hover {
    background-color: #764ba2 !important;
}
.wa-contact-link {
    color: #25D366;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
    padding: 5px 10px;
    border-radius: 20px;
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
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
    font-size: 1.2em;
    
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<div class="container">
    <section class="content-header">
        <h1>Detail Hotel</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <h2><?= htmlspecialchars($data['nama_hotel']) ?></h2>
                
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
                                <h4><?= htmlspecialchars($data['nama_hotel']) ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="no-data">Tidak ada gambar tersedia</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info Hotel -->
                <div class="info-container">
                    <h4><i class="fa fa-hotel text-primary"></i> Informasi Hotel</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fa fa-map-marker-alt text-danger"></i> <strong>Alamat:</strong> 
                                <?= htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}") ?>
                            </p>
                            <p></i> <strong>Kontak:</strong> 
                        <?php if (!empty($data['kontak'])): ?>
                                <?php 
                                $wa_number = formatWhatsAppNumber($data['kontak']);
                                $hotel_name = htmlspecialchars($data['nama_hotel']);
                                $wa_text = "Halo, saya ingin bertanya tentang hotel $hotel_name";
                                $wa_link = "https://wa.me/$wa_number?text=" . urlencode($wa_text);
                                ?>
                                <a href="<?= $wa_link ?>" target="_blank" class="wa-contact-link">
                                    <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($data['kontak']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Tidak tersedia</span>
                            <?php endif; ?>
                        </p>
                        </div>
                        <div class="col-md-6">
                            <div class="price-range">
                                <i class="fa fa-money-bill-wave"></i> 
                                <?= htmlspecialchars($data['harga_hotel']) ?>
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

                <!-- Fasilitas -->
                <?php if (!empty($data['fasilitas'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-list text-info"></i> Fasilitas Hotel</h4>
                    <div class="facility-grid">
                        <?php 
                        $fasilitasList = array_filter(array_map('trim', explode(',', $data['fasilitas'])));
                        foreach ($fasilitasList as $fasilitas): ?>
                            <div class="facility-item">
                                <i class="fa fa-check-circle text-success"></i> <?= htmlspecialchars($fasilitas) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Wisata Terdekat -->
                <!-- Ganti struktur HTML untuk Wisata Terdekat -->
<?php if (!empty($wisata_terdekat)): ?>
<div class="info-container">
    <h4 class="section-title">
        <i class="fa fa-map-marked-alt"></i>
        Wisata Terdekat
    </h4>
    <div class="nearby-slider-container">
        <div class="nearby-slider" id="wisata-slider">
            <?php foreach ($wisata_terdekat as $wisata): ?>
                <a href="?page=MyApp/detail_wisata&id=<?= $wisata['id_wisata'] ?>" class="nearby-card">
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
                        <img src="uploads/<?= htmlspecialchars($gambar_wisata) ?>" alt="<?= htmlspecialchars($wisata['nama_wisata']) ?>" class="nearby-card-image">
                    <?php else: ?>
                        <div class="nearby-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                            <i class="fa fa-mountain" style="font-size: 3em; color: #667eea;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="nearby-card-body">
                        <div class="nearby-card-title"><?= htmlspecialchars($wisata['nama_wisata']) ?></div>
                        <p><small><?= htmlspecialchars(substr($wisata['deskripsi'], 0, 100)) ?>...</small></p>
                        <div class="nearby-card-info">
                            <span class="nearby-card-price">
                                <i class="fa fa-ticket-alt"></i> <?= htmlspecialchars($wisata['harga_tiket']) ?>
                            </span>
                            <span class="nearby-card-distance">
                                <i class="fa fa-route"></i> <?= number_format($wisata['jarak'], 1) ?> km
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($wisata_terdekat) > 1): ?>
            <button class="slider-nav prev" onclick="slideWisata(-1)">
                <i class="fa fa-chevron-left"></i>
            </button>
            <button class="slider-nav next" onclick="slideWisata(1)">
                <i class="fa fa-chevron-right"></i>
            </button>
            
            <div class="slider-dots" id="wisata-dots">
                <?php for ($i = 0; $i < ceil(count($wisata_terdekat) / 2); $i++): ?>
                    <div class="slider-dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToSlideWisata(<?= $i ?>)"></div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Ganti struktur HTML untuk Kuliner Terdekat -->
<?php if (!empty($kuliner_terdekat)): ?>
<div class="info-container">
    <h4 class="section-title">
        <i class="fa fa-utensils"></i>
        Kuliner Terdekat
    </h4>
    <div class="nearby-slider-container">
        <div class="nearby-slider" id="kuliner-slider">
            <?php foreach ($kuliner_terdekat as $kuliner): ?>
                <a href="?page=MyApp/detail_kuliner&id=<?= $kuliner['id_kuliner'] ?>" class="nearby-card">
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
                        <img src="uploads/<?= htmlspecialchars($gambar_kuliner) ?>" alt="<?= htmlspecialchars($kuliner['nama_kuliner']) ?>" class="nearby-card-image">
                    <?php else: ?>
                        <div class="nearby-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                            <i class="fa fa-utensils" style="font-size: 3em; color: #667eea;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="nearby-card-body">
                        <div class="nearby-card-title"><?= htmlspecialchars($kuliner['nama_kuliner']) ?></div>
                        <p><small>Menu Spesial: <?= htmlspecialchars($kuliner['special_menu']) ?></small></p>
                        <div class="nearby-card-info">
                            <span class="nearby-card-price">
                                <i class="fa fa-money-bill"></i> <?= htmlspecialchars($kuliner['harga_range']) ?>
                            </span>
                            <span class="nearby-card-distance">
                                <i class="fa fa-route"></i> <?= number_format($kuliner['jarak'], 1) ?> km
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($kuliner_terdekat) > 1): ?>
            <button class="slider-nav prev" onclick="slideKuliner(-1)">
                <i class="fa fa-chevron-left"></i>
            </button>
            <button class="slider-nav next" onclick="slideKuliner(1)">
                <i class="fa fa-chevron-right"></i>
            </button>
            
            <div class="slider-dots" id="kuliner-dots">
                <?php for ($i = 0; $i < ceil(count($kuliner_terdekat) / 2); $i++): ?>
                    <div class="slider-dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToSlideKuliner(<?= $i ?>)"></div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Ganti struktur HTML untuk Oleh-oleh Terdekat -->
<?php if (!empty($oleh2_terdekat)): ?>
<div class="info-container">
    <h4 class="section-title">
        <i class="fa fa-gift"></i>
        Oleh-oleh Terdekat
    </h4>
    <div class="nearby-slider-container">
        <div class="nearby-slider" id="oleh2-slider">
            <?php foreach ($oleh2_terdekat as $oleh2): ?>
                <a href="?page=MyApp/detail_oleh2&id=<?= $oleh2['id_oleh2'] ?>" class="nearby-card">
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
                        <img src="uploads/<?= htmlspecialchars($gambar_oleh2) ?>" alt="<?= htmlspecialchars($oleh2['nama_toko']) ?>" class="nearby-card-image">
                    <?php else: ?>
                        <div class="nearby-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                            <i class="fa fa-gift" style="font-size: 3em; color: #667eea;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="nearby-card-body">
                        <div class="nearby-card-title"><?= htmlspecialchars($oleh2['nama_toko']) ?></div>
                        <p><small><?= htmlspecialchars(substr($oleh2['deskripsi'], 0, 100)) ?>...</small></p>
                        <div class="nearby-card-info">
                            <span class="nearby-card-price">
                                <i class="fa fa-tag"></i> <?= htmlspecialchars($oleh2['harga_range']) ?>
                            </span>
                            <span class="nearby-card-distance">
                                <i class="fa fa-route"></i> <?= number_format($oleh2['jarak'], 1) ?> km
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($oleh2_terdekat) > 1): ?>
            <button class="slider-nav prev" onclick="slideOleh2(-1)">
                <i class="fa fa-chevron-left"></i>
            </button>
            <button class="slider-nav next" onclick="slideOleh2(1)">
                <i class="fa fa-chevron-right"></i>
            </button>
            
            <div class="slider-dots" id="oleh2-dots">
                <?php for ($i = 0; $i < ceil(count($oleh2_terdekat) / 2); $i++): ?>
                    <div class="slider-dot <?= $i === 0 ? 'active' : '' ?>" onclick="goToSlideOleh2(<?= $i ?>)"></div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

                <!-- Deskripsi -->
                <?php if (!empty($data['deskripsi'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-info-circle text-info"></i> Deskripsi</h4>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Peta -->
                <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                <div class="info-container">
                    <h4><i class="fa fa-map text-warning"></i> Lokasi Peta</h4>
                    <div id="map"></div>
                </div>
                <?php endif; ?>

                <!-- Tombol Aksi -->
                <div class="text-center" style="margin-top: 20px;">
    <button onclick="history.back()" class="btn btn-hotel">
        <i class="fa fa-arrow-left"></i> Kembali
    </button>
    
    <?php if (!empty($data['kontak'])): ?>
        <?php 
        $wa_number = formatWhatsAppNumber($data['kontak']);
        $hotel_name = htmlspecialchars($data['nama_hotel']);
        $wa_text = "Halo, saya tertarik dengan hotel $hotel_name. Apakah ada kamar yang tersedia?";
        $wa_link = "https://wa.me/$wa_number?text=" . urlencode($wa_text);
        ?>
        <a href="<?= $wa_link ?>" target="_blank" class="btn btn-wa">
            <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
        </a>
    <?php endif; ?>
</div>
            </div>
        </div>

        <!-- Form Kritik & Saran (Hanya untuk pengguna) -->
        <?php if ($show_kritik_form): ?>
        <div class="panel panel-default" id="kritik">
            <h4><i class="fa fa-comment text-primary"></i> Berikan Kritik & Saran</h4>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?= $success_msg ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?= $error_msg ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="kritikForm">
                <div class="form-group">
                    <label><i class="fa fa-star text-warning"></i> <strong>Rating Hotel:</strong></label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" title="5 bintang"><i class="fa fa-star"></i></label>
                        <input type="radio" id="star4" name="rating" value="4" required>
                        <label for="star4" title="4 bintang"><i class="fa fa-star"></i></label>
                        <input type="radio" id="star3" name="rating" value="3" required>
                        <label for="star3" title="3 bintang"><i class="fa fa-star"></i></label>
                        <input type="radio" id="star2" name="rating" value="2" required>
                        <label for="star2" title="2 bintang"><i class="fa fa-star"></i></label>
                        <input type="radio" id="star1" name="rating" value="1" required>
                        <label for="star1" title="1 bintang"><i class="fa fa-star"></i></label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="komentar">
                        <i class="fa fa-edit text-info"></i> <strong>Komentar:</strong>
                    </label>
                    <textarea name="komentar" id="komentar" class="form-control" rows="4" 
                              placeholder="Bagikan pengalaman Anda tentang hotel ini..." required></textarea>
                </div>
                
                <button type="submit" name="btnSubmit" class="btn btn-hotel">
                    <i class="fa fa-paper-plane"></i> Kirim Feedback
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Daftar Kritik & Saran (Tidak untuk admin) -->
        <?php if ($show_feedback_list && $fb && mysqli_num_rows($fb) > 0): ?>
        <div class="panel panel-default">
            <h4><i class="fa fa-comments text-success"></i> Kritik & Saran Pengunjung</h4>
            <?php while ($row = mysqli_fetch_assoc($fb)): ?>
                <div class="card-feedback">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= htmlspecialchars($row['nama_pengguna']) ?></strong>
                            <div class="mt-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa fa-star <?= $i <= $row['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-2">(<?= $row['rating'] ?>/5)</span>
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= date('d M Y, H:i', strtotime($row['tanggal'])) ?>
                        </small>
                    </div>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($row['komentar'])) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
        <?php elseif ($show_feedback_list): ?>
        <div class="panel panel-default">
            <h4><i class="fa fa-comments text-muted"></i> Kritik & Saran Pengunjung</h4>
            <div class="no-data">Belum ada feedback dari pengunjung.</div>
        </div>
        <?php endif; ?>>
    </section>
</div>

<script>
// Gallery Navigation
<?php if (!empty($gambarList) && count($gambarList) > 1): ?>
const images = <?= json_encode($gambarList) ?>;
let currentIndex = 0;

function updateImage() {
    const mainImage = document.getElementById('mainImage');
    const counter = document.getElementById('currentImageNum');
    
    mainImage.src = 'uploads/' + images[currentIndex];
    counter.textContent = currentIndex + 1;
}

document.getElementById('nextBtn').addEventListener('click', function() {
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
});

document.getElementById('prevBtn').addEventListener('click', function() {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    updateImage();
});

// Auto slide every 5 seconds
setInterval(function() {
    currentIndex = (currentIndex + 1) % images.length;
    updateImage();
}, 5000);
<?php endif; ?>

// Map initialization
<?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
const map = L.map('map').setView([<?= $data['latitude'] ?>, <?= $data['longitude'] ?>], 14);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Hotel marker
// Hotel marker
L.marker([<?= $data['latitude'] ?>, <?= $data['longitude'] ?>], {
    icon: L.divIcon({
        className: 'custom-div-icon',
        html: '<div style="background-color: #667eea; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fa fa-bed"></i></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    })
}).addTo(map)
.bindPopup('<b><?= htmlspecialchars($data['nama_hotel']) ?></b><br><?= htmlspecialchars($data['alamat']) ?>');

// Add nearby attractions markers
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

// Add nearby culinary markers
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

// Add nearby souvenir markers
<?php if (!empty($oleh2_terdekat)): ?>
    <?php foreach ($oleh2_terdekat as $oleh2): ?>
        <?php if (!empty($oleh2['latitude']) && !empty($oleh2['longitude'])): ?>
            L.marker([<?= $oleh2['latitude'] ?>, <?= $oleh2['longitude'] ?>], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #ffc107; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 15px;"><i class="fa fa-gift"></i></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(map)
            .bindPopup(`
                <div style="text-align: center; min-width: 100px;">
                    <h6 style="color: #ff6b35; margin-bottom: 8px; font-weight: bold;">
                        <?= htmlspecialchars($oleh2['nama_toko']) ?>
                    </h6>
        
                    <?php if (!empty($oleh2['alamat'])): ?>
                        <p style="margin: 5px 0; font-size: 0.8em; color: #ff6b35;">
                            <i class="fa fa-map-marker-alt" style="margin-right: 4px;"></i>
                            <?= htmlspecialchars($oleh2['alamat']) ?>
                        </p>
                    <?php endif; ?>
                    <p style="margin: 5px 0; font-size: 0.85em; color: #ff6b35; font-weight: 500;">
                        Jarak: <?= number_format($oleh2['jarak'], 1) ?> km
                    </p>
                    <div style="margin-top: 10px;">
                        <a href="?page=MyApp/detail_oleh2&id=<?= $oleh2['id_oleh2'] ?>" 
                           class="btn btn-sm" 
                           style="background: #ff6b35; color: black; border: none; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; display: inline-block; transition: background 0.3s;">
                            <i class="fa fa-eye" style="margin-right: 4px;"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            `);
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
                            <?php endif; ?>

// Form validation
document.querySelector('form')?.addEventListener('submit', function(e) {
    const rating = document.querySelector('input[name="rating"]:checked');
    const komentar = document.querySelector('textarea[name="komentar"]').value.trim();
    
    if (!rating || !komentar) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan berikan rating dan komentar Anda!'
        });
    }
});

// Success message
<?php if ($success_msg): ?>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '<?= $success_msg ?>',
    timer: 3000,
    showConfirmButton: false
});
<?php endif; ?>
// JavaScript untuk slider horizontal
let wisataCurrentIndex = 0;
let kulinerCurrentIndex = 0;
let oleh2CurrentIndex = 0;

// Fungsi untuk Wisata Slider
function slideWisata(direction) {
    const slider = document.getElementById('wisata-slider');
    const cards = slider.querySelectorAll('.nearby-card');
    const totalCards = cards.length;
    const cardsToShow = window.innerWidth <= 768 ? 1 : 2;
    const maxIndex = Math.ceil(totalCards / cardsToShow) - 1;
    
    wisataCurrentIndex += direction;
    
    if (wisataCurrentIndex < 0) wisataCurrentIndex = maxIndex;
    if (wisataCurrentIndex > maxIndex) wisataCurrentIndex = 0;
    
    const cardWidth = cards[0].offsetWidth + 20; // width + gap
    const offset = wisataCurrentIndex * cardWidth * cardsToShow;
    
    slider.style.transform = `translateX(-${offset}px)`;
    updateDots('wisata-dots', wisataCurrentIndex);
}

function goToSlideWisata(index) {
    const slider = document.getElementById('wisata-slider');
    const cards = slider.querySelectorAll('.nearby-card');
    const cardsToShow = window.innerWidth <= 768 ? 1 : 2;
    
    wisataCurrentIndex = index;
    
    const cardWidth = cards[0].offsetWidth + 20;
    const offset = wisataCurrentIndex * cardWidth * cardsToShow;
    
    slider.style.transform = `translateX(-${offset}px)`;
    updateDots('wisata-dots', wisataCurrentIndex);
}

// Fungsi untuk Kuliner Slider
function slideKuliner(direction) {
    const slider = document.getElementById('kuliner-slider');
    const cards = slider.querySelectorAll('.nearby-card');
    const totalCards = cards.length;
    const cardsToShow = window.innerWidth <= 768 ? 1 : 2;
    const maxIndex = Math.ceil(totalCards / cardsToShow) - 1;
    
    kulinerCurrentIndex += direction;
    
    if (kulinerCurrentIndex < 0) kulinerCurrentIndex = maxIndex;
    if (kulinerCurrentIndex > maxIndex) kulinerCurrentIndex = 0;
    
    const cardWidth = cards[0].offsetWidth + 20;
    const offset = kulinerCurrentIndex * cardWidth * cardsToShow;
    
    slider.style.transform = `translateX(-${offset}px)`;
    updateDots('kuliner-dots', kulinerCurrentIndex);
}

function goToSlideKuliner(index) {
    const slider = document.getElementById('kuliner-slider');
    const cards = slider.querySelectorAll('.nearby-card');
    const cardsToShow = window.innerWidth <= 768 ? 1 : 2;
    
    kulinerCurrentIndex = index;
    
    const cardWidth = cards[0].offsetWidth + 20;
    const offset = kulinerCurrentIndex * cardWidth * cardsToShow;
    
    slider.style.transform = `translateX(-${offset}px)`;
    updateDots('kuliner-dots', kulinerCurrentIndex);
}

// Fungsi untuk Oleh-oleh Slider
function slideOleh2(direction) {
    const slider = document.getElementById('oleh2-slider');
    const cards = slider.querySelectorAll('.nearby-card');
    const totalCards = cards.length;
    const cardsToShow = window.innerWidth <= 768 ? 1 : 2;
    const maxIndex = Math.ceil(totalCards / cardsToShow) - 1;
    
    oleh2CurrentIndex += direction;
    
    if (oleh2CurrentIndex < 0) oleh2CurrentIndex = maxIndex;
    if (oleh2CurrentIndex > maxIndex) oleh2CurrentIndex = 0;
    
    const cardWidth = cards[0].offsetWidth + 20;
    const offset = oleh2CurrentIndex * cardWidth * cardsToShow;
    
    slider.style.transform = `translateX(-${offset}px)`;
    updateDots('oleh2-dots', oleh2CurrentIndex);
}

function goToSlideOleh2(index) {
    const slider = document.getElementById('oleh2-slider');
    const cards = slider.querySelectorAll('.nearby-card');
    const cardsToShow = window.innerWidth <= 768 ? 1 : 2;
    
    oleh2CurrentIndex = index;
    
    const cardWidth = cards[0].offsetWidth + 20;
    const offset = oleh2CurrentIndex * cardWidth * cardsToShow;
    
    slider.style.transform = `translateX(-${offset}px)`;
    updateDots('oleh2-dots', oleh2CurrentIndex);
}

// Update dots indicator
function updateDots(dotsId, currentIndex) {
    const dots = document.querySelectorAll(`#${dotsId} .slider-dot`);
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentIndex);
    });
}

// Auto slide functionality (optional)
function autoSlide() {
    slideWisata(1);
    slideKuliner(1);
    slideOleh2(1);
}

// Uncomment below to enable auto slide every 5 seconds
// setInterval(autoSlide, 5000);

// Touch/swipe support for mobile
let startX = 0;
let currentX = 0;
let isDragging = false;

document.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    isDragging = true;
});

document.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    currentX = e.touches[0].clientX;
});

document.addEventListener('touchend', (e) => {
    if (!isDragging) return;
    isDragging = false;
    
    const diffX = startX - currentX;
    const threshold = 50;
    
    if (Math.abs(diffX) > threshold) {
        const target = e.target.closest('.nearby-slider-container');
        if (target) {
            const sliderId = target.querySelector('.nearby-slider').id;
            
            if (sliderId === 'wisata-slider') {
                slideWisata(diffX > 0 ? 1 : -1);
            } else if (sliderId === 'kuliner-slider') {
                slideKuliner(diffX > 0 ? 1 : -1);
            } else if (sliderId === 'oleh2-slider') {
                slideOleh2(diffX > 0 ? 1 : -1);
            }
        }
    }
});

// Responsive adjustment on window resize
window.addEventListener('resize', () => {
    // Reset positions on resize
    wisataCurrentIndex = 0;
    kulinerCurrentIndex = 0;
    oleh2CurrentIndex = 0;
    
    const sliders = ['wisata-slider', 'kuliner-slider', 'oleh2-slider'];
    sliders.forEach(sliderId => {
        const slider = document.getElementById(sliderId);
        if (slider) {
            slider.style.transform = 'translateX(0)';
        }
    });
    
    // Update dots
    updateDots('wisata-dots', 0);
    updateDots('kuliner-dots', 0);
    updateDots('oleh2-dots', 0);
});

</script>

<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>