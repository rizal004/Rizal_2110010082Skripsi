<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ob_start();
include "inc/koneksi.php";

// Level user: admin/pengguna
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';
$show_kritik_form = ($ses_level === 'pengguna');
$show_feedback_list = ($ses_level !== 'admin' && $ses_level !== 'administrator');

// --- Ambil ID ---
$id_event = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_event) {
    echo "<div style='text-align:center;padding:40px 0;'>Data tidak ditemukan.</div>"; exit;
}

// --- Ambil data event ---
$sql = $koneksi->query("SELECT * FROM tb_event WHERE id_event = '$id_event'");
$data = $sql->fetch_assoc();
if (!$data) {
    echo '<div style="text-align:center;padding:40px 0;">Data tidak ditemukan.</div>'; exit;
}

// --- Galeri gambar ---
$gambarList = array_filter(explode(',', $data['gambar']));

// --- User login ---
$id_user = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;

// --- Ambil event terdekat berdasarkan koordinat ---
$event_terdekat = [];
if (!empty($data['latitude']) && !empty($data['longitude'])) {
    $lat_current = floatval($data['latitude']);
    $lng_current = floatval($data['longitude']);
    
    // Query untuk mencari event terdekat (dalam radius 10km)
    $sql_terdekat = "SELECT id_event, nama_event, latitude, longitude, kategori, gambar, harga_tiket,
                     tanggal_mulai, tanggal_selesai,
                     (6371 * acos(cos(radians($lat_current)) * cos(radians(latitude)) * 
                      cos(radians(longitude) - radians($lng_current)) + 
                      sin(radians($lat_current)) * sin(radians(latitude)))) AS jarak
                     FROM tb_event 
                     WHERE id_event != '$id_event' 
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
            $event_terdekat[] = $row;
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
        $insert_query = "INSERT INTO tb_kritik_saran_event (id_event, id_pengguna, rating, komentar, tanggal) 
                         VALUES ('$id_event', $id_user, $rating, '$komentar_safe', '$tgl')";
        
        if ($koneksi->query($insert_query)) {
            $success_msg = 'Feedback Anda berhasil disimpan!';
            
            // Optional: Redirect untuk menghindari resubmit saat refresh
            // echo "<script>
            //     setTimeout(function() {
            //         window.location.href = window.location.href;
            //     }, 1500);
            // </script>";
        } else {
            $error_msg = 'Terjadi kesalahan saat menyimpan feedback: ' . $koneksi->error;
        }
    } else {
        if ($id_user <= 0) {
            $error_msg = 'Anda harus login untuk memberikan feedback.';
        } elseif ($rating < 1 || $rating > 5) {
            $error_msg = 'Silakan pilih rating antara 1-5.';
        } elseif ($komentar === '') {
            $error_msg = 'Silakan isi komentar Anda.';
        } else {
            $error_msg = 'Silakan isi rating dan komentar dengan benar.';
        }
    }
}

// --- List kritik & saran (hanya untuk pengguna) ---
$fb = null;
if ($show_kritik_form) {
    $fb = $koneksi->query(
        "SELECT ks.*, u.nama_pengguna 
         FROM tb_kritik_saran_event ks 
         JOIN tb_pengguna u ON ks.id_pengguna = u.id_pengguna 
         WHERE ks.id_event = '$id_event' 
         ORDER BY ks.tanggal DESC"
    );
}

// --- Tampilkan pesan ---
if ($success_msg) {
    echo "<div class='alert alert-success'>$success_msg</div>";
}
if ($error_msg) {
    echo "<div class='alert alert-danger'>$error_msg</div>";
}

// --- Fungsi untuk format tanggal Indonesia ---
function formatTanggalIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// --- Status event ---
$today = date('Y-m-d');
$status_event = '';
$status_class = '';
if ($data['tanggal_mulai'] > $today) {
    $status_event = 'Akan Datang';
    $status_class = 'status-upcoming';
} elseif ($data['tanggal_selesai'] < $today) {
    $status_event = 'Sudah Berakhir';
    $status_class = 'status-ended';
} else {
    $status_event = 'Sedang Berlangsung';
    $status_class = 'status-ongoing';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Detail Event</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
   <style>
        body {
            background: linear-gradient(135deg, #8b4513 0%, #a0522d 100%);
            color: #2c3e50;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container { 
            margin-top: 30px; 
            margin-bottom: 38px; 
            padding: 0 15px;
        }
        
        .content-header h1 {
            font-weight: 900;
            color: white;
            text-align: center;
            margin-bottom: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-size: 2rem;
            letter-spacing: 1.2px;
        }
        
        .box-primary {
            background: rgba(255,255,255,0.99);
            border-radius: 18px;
            padding: 20px 20px 15px 20px;
            box-shadow: 0 6px 22px 0 rgba(139,69,19,0.12);
            max-width: 1500px;
            margin: 0 auto 30px auto;
            border: 2px solid #8b4513;
            border-top: 4px solid #8b4513 !important;
        }
        
        /* Photo Slideshow Styles */
        .slideshow-container {
            position: relative;
            width: 100%;
            height: 520px;
            margin: 0 0 24px 0;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(139,69,19,0.13);
            overflow: hidden;
            background: #f4f0ec;
            border: 2.5px solid #8b4513;
            z-index: 1;
        }

        .slide {
            display: none;
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slide.active {
            display: block;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .slide-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 30px 20px 20px;
            text-align: center;
        }

        .slide-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .slide-category {
            font-size: 1rem;
            color: #d2b48c;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .slide-location {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .slide-date {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .slide-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(139,69,19,0.7);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            z-index: 10;
        }

        .slide-nav:hover {
            background: rgba(139,69,19,0.9);
            transform: translateY(-50%) scale(1.1);
        }

        .slide-nav.prev {
            left: 20px;
        }

        .slide-nav.next {
            right: 20px;
        }

        .slide-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .indicator.active {
            background: #d2b48c;
            transform: scale(1.2);
        }

        .slide-counter {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(139,69,19,0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            z-index: 10;
        }

        .no-images {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #8b4513;
            font-size: 1.2rem;
            text-align: center;
        }
        
        .gallery-container { 
            margin-bottom: 30px; 
            text-align: center; 
        }
        
        .main-image-container {
            position: relative; 
            border-radius: 15px; 
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(139,69,19,0.11); 
            margin-bottom: 10px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .main-image-container img {
            max-width: 100%; 
            width: 100%; 
            height: 1000px; 
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
            background: rgba(139,69,19,0.7);
            backdrop-filter: blur(5px);
            color: white;
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
            background: #8b4513;
            color: white;
            opacity: 1;
            transform: scale(1.1);
        }
        
        .image-counter {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #8b4513;
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
        
        .tambah-btn-wrap {
            display: flex;
            justify-content: flex-end;
            margin: 10px 0 15px 0;
            max-width: 100%;
        }
        
        .btn-tambah-data {
            background: linear-gradient(90deg,#8b4513 10%,#a0522d 100%);
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 22px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(139,69,19,0.10);
            transition: background 0.18s, box-shadow 0.18s;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        
        .btn-tambah-data:hover {
            background: linear-gradient(90deg,#a0522d 10%,#8b4513 100%);
            color: #fff;
            box-shadow: 0 4px 18px rgba(160,82,45,0.13);
        }
        
        .info-container {
            background: #f9f6f2;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(139,69,19,0.09);
            margin-bottom: 25px;
            border: 2px solid #8b4513;
        }
        
        .info-container h4 { 
            color: #8b4513; 
            font-weight: 700; 
            margin-bottom: 15px;
        }
        
        .event-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .status-upcoming {
            background: #e8f4f8;
            color: #2c5aa0;
            border: 2px solid #5dade2;
        }
        
        .status-ongoing {
            background: #e8f5e8;
            color: #a0522d;
            border: 2px solid #a0522d;
        }
        
        .status-ended {
            background: #fce4ec;
            color: #c2185b;
            border: 2px solid #e91e63;
        }
        
        .event-date-range {
            background: linear-gradient(135deg, #8b4513, #a0522d);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        
        .event-date-range h5 {
            margin: 0;
            font-weight: bold;
        }
        
        .coordinate-display {
            background: #f9f6f2;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #8b4513;
            box-shadow: 0 2px 8px rgba(139,69,19,0.08);
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
            color: #8b4513;
            border: 1px solid #8b4513;
        }
        
        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .event-item {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(139,69,19,0.08);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1.5px solid #e8ddd6;
        }
        
        .event-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(160,82,45,0.12);
            border: 1.5px solid #8b4513;
        }
        
        .event-image img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .event-content {
            padding: 15px;
        }
        
        .event-title {
            font-size: 1.32rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #8b4513;
            letter-spacing: 0.5px;
        }
        
        .event-content p {
            margin: 5px 0;
            color: #3e4c4b;
            font-size: 0.97rem;
        }
        
        .event-actions {
            margin-top: 15px;
            text-align: center;
        }
        
        .event-actions .btn {
            border-radius: 20px;
            padding: 6px 15px;
            font-weight: 600;
            background: linear-gradient(90deg,#8b4513,#a0522d);
            color: #fff;
            text-decoration: none;
            border: none;
            transition: .18s;
            margin: 0 3px;
            cursor: pointer;
        }
        
        .event-actions .btn:hover {
            background: linear-gradient(90deg,#a0522d,#8b4513);
        }
        
        .event-terdekat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .event-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(139,69,19,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(139,69,19,0.2);
            border-color: #8b4513;
            text-decoration: none;
            color: inherit;
        }
        
        .event-card-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
        }
        
        .event-card-body {
            padding: 15px;
        }
        
        .event-card-title {
            font-weight: bold;
            color: #8b4513;
            margin-bottom: 8px;
            font-size: 1.1em;
        }
        
        .event-card-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        
        .event-card-distance {
            background: #f9f6f2;
            color: #8b4513;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
            border: 1px solid #8b4513;
        }
        
        .event-card-price {
            background: #e8f5e8;
            color: #a0522d;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .kategori-badge {
            display: inline-block;
            background: linear-gradient(45deg, #8b4513, #a0522d);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .price-info {
            font-size: 1.2em;
            font-weight: bold;
            color: #a0522d;
            background: #e8f5e8;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #a0522d;
        }
        
        .filter-form {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .filter-form select, .filter-form button {
            padding: 8px 12px;
            border-radius: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        }
        
        .filter-form button {
            background: linear-gradient(90deg,#8b4513 60%,#a0522d 100%);
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .filter-form button:hover {
            background: linear-gradient(90deg,#a0522d 60%,#8b4513 100%);
        }
        
        .btn-event {
            border-radius: 20px;
            font-weight: bold;
            padding: 8px 22px;
            background: linear-gradient(90deg, #8b4513 20%, #a0522d 100%);
            border: none;
            color: white;
            margin: 5px;
            transition: background 0.3s;
            cursor: pointer;
        }
        
        .btn-event:hover { 
            background: linear-gradient(90deg, #a0522d 0%, #8b4513 100%);
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
            color: #e8ddd6; 
            cursor: pointer; 
            margin-right: 4px;
            transition: color 0.18s;
        }
        
        .star-rating input:checked ~ label, 
        .star-rating label:hover, 
        .star-rating label:hover ~ label { 
            color: #8b4513;
        }
        
        .star-rating input:focus ~ label { 
            outline: 2px solid #8b4513;
        }
        
        .card-feedback {
            margin-bottom: 1rem; 
            background: #f9f6f2; 
            border-radius: 10px; 
            border: 2px solid #8b4513;
            box-shadow: 0 2px 8px rgba(139,69,19,0.09);
            padding: 15px;
            transition: box-shadow 0.16s;
        }
        
        .card-feedback:hover {
            box-shadow: 0 4px 12px rgba(139,69,19,0.15);
        }
        
        .card-feedback strong { 
            color: #8b4513;
        }
        
        .card-feedback small { 
            color: #999; 
        }
        
        .panel.panel-default {
            background: #fff; 
            padding: 14px 13px 17px 13px;
            border-radius: 10px; 
            box-shadow: 0 2px 7px rgba(139,69,19,0.07);
            margin-top: 18px;
            margin-bottom: 14px;
            max-width: 1500px;
            margin-left: auto;
            margin-right: auto;
            border: 2px solid #8b4513;
        }
        
        #map {
            height: 400px;
            width: 100%;
            border-radius: 15px;
            border: 2px solid #8b4513;
        }
        
        .no-data {
            text-align: center;
            color: #8b4513;
            font-style: italic;
            padding: 20px;
        }
        
        .alert-success {
            background-color: #f9f6f2 !important;
            border-color: #8b4513 !important;
            color: #8b4513 !important;
        }
        
        .alert-danger {
            background-color: #ffeaea !important;
            border-color: #ff6b6b !important;
            color: #d63384 !important;
        }
        
        .form-control:focus {
            border-color: #8b4513;
            box-shadow: 0 0 0 0.2rem rgba(139,69,19,0.25);
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
            .event-terdekat-grid {
                grid-template-columns: 1fr;
            }
            .event-grid {
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
            background-color: #8b4513 !important;
        }
        
        .swal2-confirm:hover {
            background-color: #a0522d !important;
        }
        .whatsapp-link {
    color: #25D366;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 20px;
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
}

.whatsapp-link:hover {
    color: #128C7E;
    background: rgba(37, 211, 102, 0.2);
    border-color: rgba(37, 211, 102, 0.5);
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
}

.whatsapp-link i {
    font-size: 1.2em;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .whatsapp-link {
        display: flex;
        justify-content: center;
        margin: 10px 0;
        padding: 10px 15px;
        font-size: 1.1em;
    }
}
.btn-whatsapp {
    background: linear-gradient(90deg, #25D366 0%, #128C7E 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    margin: 0 5px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
}

.btn-whatsapp:hover {
    background: linear-gradient(90deg, #128C7E 0%, #25D366 100%);
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
}

.btn-whatsapp i {
    font-size: 1.2em;
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<div class="container">
    <section class="content-header">
        <h1>Detail Event Wisata</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <h2><?= htmlspecialchars($data['nama_event']) ?></h2>
                
                <!-- Status Event -->
                <span class="event-status <?= $status_class ?>">
                    <i class="fa fa-calendar-check"></i> <?= $status_event ?>
                </span>
                
                <!-- Kategori -->
                <div class="kategori-badge">
                    <i class="fa fa-tag"></i> <?= htmlspecialchars($data['kategori']) ?>
                </div>
                
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
                                <h4><?= htmlspecialchars($data['nama_event']) ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="no-data">Tidak ada gambar tersedia</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info Event -->
                <div class="info-container">
                    <h4><i class="fa fa-calendar-alt text-primary"></i> Informasi Event</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fa fa-map-marker-alt text-danger"></i> <strong>Alamat:</strong> 
                                <?= htmlspecialchars("{$data['alamat']}, {$data['kecamatan']}, {$data['kabupaten']}") ?>
                            </p>
                            <?php if (!empty($data['jam_operasional'])): ?>
                            <p><i class="fa fa-clock text-warning"></i> <strong>Jam Operasional:</strong> <?= htmlspecialchars($data['jam_operasional']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($data['no_hp'])): ?>
                            <p><i class="fa fa-phone text-success"></i> <strong>Kontak:</strong> 
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $data['no_hp']) ?>" 
                                target="_blank" 
                                class="whatsapp-link">
                                    <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($data['no_hp']) ?>
                                </a>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="price-info">
                                <i class="fa fa-ticket-alt"></i> 
                                <?= htmlspecialchars($data['harga_tiket']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tanggal Event -->
                    <div class="event-date-range">
                        <h5><i class="fa fa-calendar"></i> Jadwal Event</h5>
                        <p style="margin: 5px 0;">
                            <?= formatTanggalIndonesia($data['tanggal_mulai']) ?> 
                            <?php if ($data['tanggal_mulai'] != $data['tanggal_selesai']): ?>
                                - <?= formatTanggalIndonesia($data['tanggal_selesai']) ?>
                            <?php endif; ?>
                        </p>
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

                <!-- Event Terdekat -->
                <?php if (!empty($event_terdekat)): ?>
                <div class="info-container">
                    <h4><i class="fa fa-map-marked-alt text-primary"></i> Event Terdekat</h4>
                    <div class="event-terdekat-grid">
                        <?php foreach ($event_terdekat as $event): ?>
                            <a href="?page=MyApp/detail_event&id=<?= $event['id_event'] ?>" class="event-card">
                                <?php 
                                $gambar_event = '';
                                if (!empty($event['gambar'])) {
                                    $gambar_list = array_filter(explode(',', $event['gambar']));
                                    if (!empty($gambar_list)) {
                                        $gambar_event = $gambar_list[0];
                                    }
                                }
                                ?>
                                <?php if ($gambar_event): ?>
                                    <img src="uploads/<?= htmlspecialchars($gambar_event) ?>" alt="<?= htmlspecialchars($event['nama_event']) ?>" class="event-card-image">
                                <?php else: ?>
                                    <div class="event-card-image" style="display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                        <i class="fa fa-calendar-alt" style="font-size: 3em; color: #667eea;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="event-card-body">
                                    <div class="event-card-title"><?= htmlspecialchars($event['nama_event']) ?></div>
                                    <p><small><i class="fa fa-tag"></i> <?= htmlspecialchars($event['kategori']) ?></small></p>
                                    <p><small><i class="fa fa-calendar"></i> <?= formatTanggalIndonesia($event['tanggal_mulai']) ?></small></p>
                                    <div class="event-card-info">
                                        <span class="event-card-price">
                                            <i class="fa fa-ticket-alt"></i> <?= htmlspecialchars($event['harga_tiket']) ?>
                                        </span>
                                        <span class="event-card-distance">
                                            <i class="fa fa-route"></i> <?= number_format($event['jarak'], 1) ?> km
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
                    <h4><i class="fa fa-map"></i> Lokasi Event</h4>
                    <div id="map"></div>
                </div>
                <?php endif; ?>

                <!-- Deskripsi -->
                <div class="info-container">
                    <h4><i class="fa fa-info-circle"></i> Deskripsi</h4>
                    <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
                </div>

                <div style="margin-top: 25px; text-align: center;">
                    <a href="?page=MyApp/data_event" class="btn btn-event"><i class="fa fa-arrow-left"></i> Kembali</a>

                    <?php if (!empty($data['no_hp'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $data['no_hp']) ?>?text=Halo, saya tertarik dengan event <?= urlencode($data['nama_event']) ?>" 
                    target="_blank" 
                    class="btn btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Kritik & Saran -->
                <?php if ($show_kritik_form): ?>
<a name="kritik"></a>
<div class="panel panel-default">
    <h3 style="color: #a0522d"><i class="fa fa-comments"></i> Kritik & Saran</h3>
    
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
            <textarea name="komentar" id="komentar" class="form-control" rows="4" 
                placeholder="Berikan kritik atau saran Anda tentang event ini..." 
                required></textarea>
        </div>
        <button type="submit" name="btnSubmit" class="btn btn-event">
            <i class="fa fa-paper-plane"></i> Kirim Feedback
        </button>
    </form>

    <!-- List kritik & saran -->
    <?php if ($show_feedback_list): ?>
    <div style="margin-top: 30px;">
        <h4><i class="fa fa-list"></i> Feedback Pengguna</h4>
        <?php if ($fb && mysqli_num_rows($fb) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($fb)): ?>
                <div class="card-feedback">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <strong><i class="fa fa-user-circle"></i> <?= htmlspecialchars($row['nama_pengguna']) ?></strong>
                        <div>
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fa fa-star <?= $i <= $row['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                            <small class="text-muted">(<?= $row['rating'] ?>/5)</small>
                        </div>
                    </div>
                    <p style="margin-bottom: 5px;"><?= nl2br(htmlspecialchars($row['komentar'])) ?></p>
                    <small class="text-muted">
                        <i class="fa fa-clock"></i> <?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?>
                    </small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="fa fa-comments"></i><br>
                Belum ada feedback untuk event ini
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
// Gallery navigation
<?php if (!empty($gambarList) && count($gambarList) > 1): ?>
let currentImageIndex = 0;
const images = <?= json_encode($gambarList) ?>;
const totalImages = images.length;

function updateImage() {
    document.getElementById('mainImage').src = 'uploads/' + images[currentImageIndex];
    document.getElementById('currentImageNum').textContent = currentImageIndex + 1;
}

document.getElementById('nextBtn').addEventListener('click', function() {
    currentImageIndex = (currentImageIndex + 1) % totalImages;
    updateImage();
});

document.getElementById('prevBtn').addEventListener('click', function() {
    currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
    updateImage();
});

// Auto slide (optional)
setInterval(function() {
    currentImageIndex = (currentImageIndex + 1) % totalImages;
    updateImage();
}, 5000);
<?php endif; ?>

// Map initialization
<?php if(!empty($data['latitude']) && !empty($data['longitude'])): ?>
const map = L.map('map').setView([<?= $data['latitude'] ?>, <?= $data['longitude'] ?>], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Custom icons
const mainEventIcon = L.divIcon({
    html: '<i class="fa fa-calendar-check" style="color: #667eea; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);"></i>',
    iconSize: [30, 30],
    iconAnchor: [15, 15],
    popupAnchor: [0, -15],
    className: 'custom-marker-icon'
});

const nearbyEventIcon = L.divIcon({
    html: '<i class="fa fa-map-marker-alt" style="color: #764ba2; font-size: 20px; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);"></i>',
    iconSize: [25, 25],
    iconAnchor: [12, 12],
    popupAnchor: [0, -12],
    className: 'custom-marker-icon'
});

// Main event marker
const mainMarker = L.marker([<?= $data['latitude'] ?>, <?= $data['longitude'] ?>], {icon: mainEventIcon})
    .addTo(map)
    .bindPopup(`
        <div style="text-align: center;">
            <h5 style="margin: 5px 0; color: #667eea;"><?= htmlspecialchars($data['nama_event']) ?></h5>
            <p style="margin: 3px 0; font-size: 0.9em;"><?= htmlspecialchars($data['alamat']) ?></p>
            <p style="margin: 3px 0; font-size: 0.85em; color: #666;">
                <i class="fa fa-calendar"></i> <?= formatTanggalIndonesia($data['tanggal_mulai']) ?>
            </p>
        </div>
    `)
    .openPopup();

// Nearby events markers
<?php if (!empty($event_terdekat)): ?>
<?php foreach ($event_terdekat as $event): ?>
L.marker([<?= $event['latitude'] ?>, <?= $event['longitude'] ?>], {icon: nearbyEventIcon})
    .addTo(map)
    .bindPopup(`
        <div style="text-align: center;">
            <h6 style="margin: 5px 0; color: #764ba2;"><?= htmlspecialchars($event['nama_event']) ?></h6>
            <p style="margin: 3px 0; font-size: 0.8em; color: #666;">
                <i class="fa fa-route"></i> <?= number_format($event['jarak'], 1) ?> km
            </p>
            <a href="?page=MyApp/detail_event&id=<?= $event['id_event'] ?>" 
               style="color: #667eea; text-decoration: none; font-size: 0.85em;">
                <i class="fa fa-eye"></i> Lihat Detail
            </a>
        </div>
    `);
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

// Success message
<?php if (!empty($success_msg)): ?>
Swal.fire({
    title: 'Berhasil!',
    text: '<?= $success_msg ?>',
    icon: 'success',
    confirmButtonText: 'OK',
    confirmButtonColor: '#667eea'
});
<?php endif; ?>

// Form validation enhancement
<?php if ($show_kritik_form): ?>
document.getElementById('kritikForm').addEventListener('submit', function(e) {
    const rating = document.querySelector('input[name="rating"]:checked');
    const komentar = document.getElementById('komentar').value.trim();
    
    if (!rating) {
        e.preventDefault();
        Swal.fire({
            title: 'Peringatan!',
            text: 'Silakan pilih rating terlebih dahulu.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
        return;
    }
    
    if (komentar === '') {
        e.preventDefault();
        Swal.fire({
            title: 'Peringatan!',
            text: 'Silakan isi komentar Anda.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
        return;
    }
    
    if (komentar.length < 10) {
        e.preventDefault();
        Swal.fire({
            title: 'Peringatan!',
            text: 'Komentar minimal 10 karakter.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
        return;
    }
});
<?php endif; ?>

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#kritik') {
        document.querySelector('a[name="kritik"]').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
});

// Image loading error handling
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDMwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0xNTAgMTAwTDEyNSA3NUwxNzUgNzVMMTUwIDEwMFoiIGZpbGw9IiNEREREREQiLz4KPHA+CjwhLS0gSW1hZ2UgTm90IEZvdW5kIC0tPgo8L3A+CjwvcmVjdD4KPC9zdmc+';
            this.alt = 'Gambar tidak tersedia';
        });
    });
});

// Event card click animation
document.addEventListener('DOMContentLoaded', function() {
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Add loading effect
            this.style.opacity = '0.7';
            this.style.transform = 'scale(0.98)';
            
            setTimeout(() => {
                this.style.opacity = '1';
                this.style.transform = 'scale(1)';
            }, 200);
        });
    });
});

// Rating stars hover effect enhancement
<?php if ($show_kritik_form): ?>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating label');
    const starContainer = document.querySelector('.star-rating');
    
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const rating = this.getAttribute('for').replace('star', '');
            updateStarDisplay(rating);
        });
    });
    
    starContainer.addEventListener('mouseleave', function() {
        const checkedStar = document.querySelector('.star-rating input:checked');
        if (checkedStar) {
            const rating = checkedStar.value;
            updateStarDisplay(rating);
        } else {
            resetStarDisplay();
        }
    });
    
    function updateStarDisplay(rating) {
        stars.forEach((star, index) => {
            const starNumber = 5 - index;
            if (starNumber <= rating) {
                star.style.color = '#667eea';
            } else {
                star.style.color = '#e1e5f2';
            }
        });
    }
    
    function resetStarDisplay() {
        stars.forEach(star => {
            star.style.color = '#e1e5f2';
        });
    }
});
<?php endif; ?>
function cleanPhoneNumber(phoneNumber) {
    // Hapus semua karakter non-digit
    let cleaned = phoneNumber.replace(/[^0-9]/g, '');
    
    // Jika dimulai dengan 0, ganti dengan 62
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }
    
    // Jika tidak dimulai dengan 62, tambahkan 62
    if (!cleaned.startsWith('62')) {
        cleaned = '62' + cleaned;
    }
    
    return cleaned;
}

// Event listener untuk link WhatsApp
document.addEventListener('DOMContentLoaded', function() {
    const whatsappLinks = document.querySelectorAll('.whatsapp-link, .btn-whatsapp');
    
    whatsappLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Tambahkan efek loading
            this.style.opacity = '0.7';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 300);
        });
    });
});
</script>

</body>
</html>

<?php ob_end_flush(); ?>