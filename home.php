<?php 
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Filter untuk daftar wisata bawah
$provinsi  = isset($_GET['provinsi'])  ? mysqli_real_escape_string($koneksi, $_GET['provinsi'])  : '';
$kabupaten = isset($_GET['kabupaten']) ? mysqli_real_escape_string($koneksi, $_GET['kabupaten']) : '';
$kategori  = isset($_GET['kategori'])  ? mysqli_real_escape_string($koneksi, $_GET['kategori'])  : '';

$where = [];
if ($provinsi  !== '') $where[] = "provinsi='{$provinsi}'";
if ($kabupaten !== '') $where[] = "kabupaten='{$kabupaten}'";
if ($kategori  !== '') $where[] = "kategori='{$kategori}'";
$whereSql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';

$sql = $koneksi->query("SELECT * FROM tb_wisata" . $whereSql);

// Ambil data gabungan wisata, kuliner, oleh-oleh, dan event untuk slideshow
$slideshowData = [];

// Ambil data wisata
$qWisata = mysqli_query($koneksi, "SELECT nama_wisata as nama, gambar, 'wisata' as tipe, kategori, deskripsi FROM tb_wisata ORDER BY RAND() LIMIT 5");
while ($r = mysqli_fetch_assoc($qWisata)) {
    $img = explode(',', $r['gambar'])[0];
    if (!empty(trim($img))) {
        $slideshowData[] = [
            'nama' => $r['nama'],
            'gambar' => 'uploads/' . trim($img),
            'tipe' => 'wisata',
            'kategori' => $r['kategori'],
            'deskripsi' => substr($r['deskripsi'], 0, 100) . '...'
        ];
    }
}

// Ambil data kuliner
$qKuliner = mysqli_query($koneksi, "SELECT nama_kuliner as nama, gambar, 'kuliner' as tipe, harga_range as kategori, alamat as deskripsi FROM tb_kuliner ORDER BY RAND() LIMIT 5");
while ($r = mysqli_fetch_assoc($qKuliner)) {
    $img = explode(',', $r['gambar'])[0];
    if (!empty(trim($img))) {
        $slideshowData[] = [
            'nama' => $r['nama'],
            'gambar' => 'uploads/' . trim($img),
            'tipe' => 'kuliner',
            'kategori' => $r['kategori'],
            'deskripsi' => substr($r['deskripsi'], 0, 100) . '...'
        ];
    }
}

// Ambil data oleh-oleh
$qOlehOleh = mysqli_query($koneksi, "SELECT nama_toko as nama, gambar, 'oleholeh' as tipe, harga_range as kategori, alamat as deskripsi FROM tb_oleh2 ORDER BY RAND() LIMIT 4");
while ($r = mysqli_fetch_assoc($qOlehOleh)) {
    $img = explode(',', $r['gambar'])[0];
    // Skip jika gambar kosong
    if (!empty(trim($img))) {
        $slideshowData[] = [
            'nama' => $r['nama'],
            'gambar' => 'uploads/' . trim($img),
            'tipe' => 'oleholeh',
            'kategori' => $r['kategori'],
            'deskripsi' => substr($r['deskripsi'], 0, 100) . '...'
        ];
    }
}

// Ambil data event
$qEvent = mysqli_query($koneksi, "SELECT * FROM tb_event WHERE tanggal_selesai >= CURDATE() ORDER BY RAND() LIMIT 4");
while ($r = mysqli_fetch_assoc($qEvent)) {
    $img = explode(',', $r['gambar'])[0];
    // Skip jika gambar kosong
    if (!empty(trim($img))) {
        $slideshowData[] = [
            'nama' => $r['nama_event'],  // Changed from 'nama' to 'nama_event'
            'gambar' => 'uploads/' . trim($img),
            'tipe' => 'event',
            'kategori' => $r['kategori'],
            'deskripsi' => $r['alamat'] . ' | ' . date('d M Y', strtotime($r['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($r['tanggal_selesai']))  // Changed from lokasi and periode to alamat and formatted dates
        ];
    }
}

// Ambil data oleh-oleh
$qhotel = mysqli_query($koneksi, "SELECT nama_hotel as nama, gambar, 'hotel' as tipe, harga_hotel as kategori, alamat as deskripsi FROM tb_hotel ORDER BY RAND() LIMIT 4");
while ($r = mysqli_fetch_assoc($qhotel)) {
    $img = explode(',', $r['gambar'])[0];
    // Skip jika gambar kosong
    if (!empty(trim($img))) {
        $slideshowData[] = [
            'nama' => $r['nama'],
            'gambar' => 'uploads/' . trim($img),
            'tipe' => 'hotel',
            'kategori' => $r['kategori'],
            'deskripsi' => substr($r['deskripsi'], 0, 100) . '...'
        ];
    }
}

// Shuffle array untuk mencampur wisata dan kuliner
shuffle($slideshowData);
// Ambil maksimal 8 item
$slideshowData = array_slice($slideshowData, 0, 8);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata Kalimantan Tengah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .custom-marker {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .marker-icon {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            border: 3px solid white;
            box-shadow: 0 3px 15px rgba(0,0,0,0.3);
        }
        
        .wisata { background: linear-gradient(45deg, #667eea); }
        .kuliner { background: linear-gradient(45deg, #43e97b); }
        .oleh2 { background: linear-gradient(45deg, #ffc107); }
        .hotel { background: linear-gradient(45deg, #764ba2); }
        .event { background: linear-gradient(45deg, #a0522d); }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; background: #f6f7fa;}
        .hero-map {
            position: relative;
            width: 100vw;
            height: 95vh;
            overflow: hidden;
        }
        #map {
            width: 100vw;
            height: 100vh;
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
        }
        .overlay-hero {
            position: absolute;
            top:0; left:0; right:0; bottom:0;
            background: linear-gradient(180deg,rgba(102,126,234,0.12) 30%,rgba(60,22,100,0.28) 100%);
            z-index: 1;
        }
        .hero-content {
            position: absolute;
            top: 48%; left: 8vw; right: 8vw;
            transform: translateY(-55%);
            z-index: 2;
            color: white;
            max-width: 800px;
        }
        .hero-title {
            font-size: 3.1rem;
            font-weight: 900;
            margin-bottom: 1.1rem;
            line-height: 1.1;
            text-shadow: 2px 2px 8px rgba(4, 0, 6, 0.95);
        }
        .hero-desc {
            font-size: 1.35rem;
            font-weight: 400;
            margin-bottom: 2.3rem;
            text-shadow: 1px 1px 5px rgb(11, 11, 12);
            color:rgb(254, 253, 252);
        }
        .scroll-down {
            position: absolute;
            bottom: 2.3rem; left: 50%; transform: translateX(-50%);
            z-index: 10;
            background: rgba(255,255,255,0.93);
            width: 44px; height:44px;
            border-radius: 50%;
            display:flex;align-items:center;justify-content:center;
            box-shadow: 0 6px 22px rgba(80,40,140,0.12);
            cursor:pointer;
            border: none;
            font-size: 2rem;
            color: #764ba2;
            transition: background .16s;
        }
        .scroll-down:hover { background: #f7eefd;}
        .header {
            position: absolute;
            top:0;left:0;right:0;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            z-index: 100;
            padding: 1.1rem 3vw 1.1rem 2vw;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
        }
        .header-content { display:flex; justify-content:space-between; align-items:center; max-width:1300px; margin:0 auto;}
        .logo { display:flex; align-items:center; gap:0.5rem;}
        .logo i { font-size:2rem; color:#667eea;}
        .logo h1 { font-size:1.7rem; color:#2d3748; font-weight:700;}
        .nav-menu { display:flex; gap:2rem; list-style:none;}
        .nav-menu a { text-decoration:none; color:#4a5568; font-weight:500; transition:.3s; padding:0.5rem 1rem; border-radius:8px;}
        .nav-menu a:hover { color:#667eea; background:rgba(102,126,234,0.13);}

        /* Area Slideshow Overlay */
        .area-slideshow-overlay {
            position: absolute;
            bottom: 10px;
            left: 120px;
            width: 380px;
            height: 250px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            z-index: 50;
            backdrop-filter: blur(15px);
            border: 2px solid rgba(102, 126, 234, 0.3);
        }

        .slideshow-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slide {
            display: none;
            width: 100%;
            height: 100%;
            position: relative;
            animation: fadeIn 0.5s ease-in-out;
        }

        .slide.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .slide img {
            width: 100%;
            height: 65%;
            object-fit: cover;
        }

        .slide-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(102, 126, 234, 0.9), rgba(102, 126, 234, 0.7));
            color: white;
            padding: 12px 15px;
            height: 35%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Background berbeda untuk kuliner */
        .slide[data-type="kuliner"] .slide-info {
            background: linear-gradient(to top, rgba(40, 167, 69, 0.9), rgba(34, 193, 195, 0.7));
        }

        /* Background berbeda untuk oleh-oleh */
        .slide[data-type="oleholeh"] .slide-info {
            background: linear-gradient(to top, rgba(255, 183, 77, 0.9), rgba(255, 112, 67, 0.7));
        }

        /* Background berbeda untuk event */
        .slide[data-type="event"] .slide-info {
            background: linear-gradient(to top, rgba(139, 69, 19, 0.9), rgba(160, 82, 45, 0.7));
        }

        .slide-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .slide-category {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .slide-desc {
            font-size: 0.75rem;
            opacity: 0.8;
            line-height: 1.3;
        }

        .slideshow-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .control-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1);
        }

        .play-pause-btn {
            background: rgba(102, 126, 234, 0.9);
            color: white;
        }

        .play-pause-btn:hover {
            background: rgba(102, 126, 234, 1);
        }

        .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.1s linear;
            z-index: 11;
        }

        .slideshow-indicator {
            position: absolute;
            top: 10px;
            left: 15px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .type-icon {
            font-size: 0.8rem;
            margin-right: 3px;
        }

        /* --- Section Gallery Horizontal --- */
        .section-gallery { 
            background:#fff; 
            margin-top:-2vh; 
            border-radius: 30px 30px 0 0; 
            box-shadow: 0 -6px 32px rgba(118,75,162,0.07); 
            padding:2rem 0; 
            position: relative;
        }
        
        .section-gallery:not(:first-of-type) {
            margin-top: 1rem;
            border-radius: 30px;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
        }

        .gallery-header {
            padding: 0 3vw;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .gallery-title { 
            font-size:2.2rem; 
            margin-bottom:0.3rem; 
            color:#2d3748; 
            font-weight: 700;
        }
        
        .gallery-subtitle { 
            font-size:1.1rem; 
            margin-bottom:1rem; 
            color:#718096;
        }

        /* Horizontal Scroll Container */
        .gallery-scroll-container {
            position: relative;
            overflow: hidden;
            padding: 0 3vw;
        }

        .gallery-list {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            padding: 1rem 0 2rem 0;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        .gallery-list::-webkit-scrollbar {
            display: none; /* Chrome/Safari */
        }
        
        .gallery-card {
            background:#fff;
            border-radius:20px;
            box-shadow:0 4px 32px 0 rgba(118,75,162,0.06);
            overflow:hidden;
            min-width:260px;
            width: 260px;
            text-align:left;
            transition:transform .19s,box-shadow .19s;
            position:relative;
            flex-shrink: 0;
        }
        .gallery-card:hover {
            transform:translateY(-9px) scale(1.025);
            box-shadow:0 10px 32px rgba(102,126,234,0.18);
        }
        .gallery-card img {
            border-radius: 18px 18px 0 0;
            transition:transform .3s;
            width:100%; height:170px; object-fit:cover;
        }
        .gallery-card:hover img {
            transform:scale(1.06);
        }
        .gallery-info { padding:1.2rem 1.2rem 1rem 1.2rem;}
        .gallery-info h3 { font-size:1.09rem; color:#29384c; font-weight:700; margin:0 0 .4rem 0;}
        .gallery-category { font-size:1em; margin-bottom:8px; display:inline-block; font-weight:600; padding:0.3em 0.8em; border-radius:12px;}

        /* Navigation Arrows for Horizontal Scroll */
        .scroll-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            z-index: 10;
        }

        .scroll-nav:hover {
            background: #667eea;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .scroll-nav.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .scroll-nav.disabled:hover {
            background: rgba(255, 255, 255, 0.95);
            color: #667eea;
            transform: translateY(-50%);
        }

        .scroll-left {
            left: 10px;
        }

        .scroll-right {
            right: 10px;
        }

        /* View All Button */
        .view-all-btn {
            display: inline-block;
            background: linear-gradient(135deg,#667eea,#764ba2);
            color: #fff;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            margin-top: 1rem;
            transition: all 0.3s;
            box-shadow: 0 2px 12px rgba(102,126,234,0.16);
        }

        .view-all-btn:hover {
            background: linear-gradient(135deg,#764ba2,#667eea);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(102,126,234,0.25);
        }

        /* Styling khusus untuk kuliner */
        .culinary-gallery .gallery-card:hover {
            box-shadow:0 10px 32px rgba(40,167,69,0.18);
        }
        
        .culinary-gallery .scroll-nav:hover {
            background: #28a745;
        }

        .culinary-gallery .view-all-btn {
            background: linear-gradient(135deg,#28a745,#20c997);
        }

        .culinary-gallery .view-all-btn:hover {
            background: linear-gradient(135deg,#20c997,#28a745);
        }
        
        /* Styling khusus untuk oleh-oleh */
        .souvenir-gallery .gallery-card:hover {
            box-shadow:0 10px 32px rgba(255,183,77,0.18);
        }

        .souvenir-gallery .scroll-nav:hover {
            background: #ff7043;
        }

        .souvenir-gallery .view-all-btn {
            background: linear-gradient(135deg,#ff7043,#ffb74d);
        }

        .souvenir-gallery .view-all-btn:hover {
            background: linear-gradient(135deg,#ffb74d,#ff7043);
        }
        
        /* Styling khusus untuk event */
        .event-gallery .gallery-card:hover {
            box-shadow:0 10px 32px rgba(139,69,19,0.18);
        }

        .event-gallery .scroll-nav:hover {
            background: #8b4513;
        }

        .event-gallery .view-all-btn {
            background: linear-gradient(135deg,#8b4513,#a0522d);
        }

        .event-gallery .view-all-btn:hover {
            background: linear-gradient(135deg,#a0522d,#8b4513);
        }

        /* Responsive Design */
        @media (max-width:900px){
            .gallery-scroll-container {
                padding: 0 2vw;
            }
            .area-slideshow-overlay {
                width: 300px;
                height: 200px;
                bottom: 60px;
                left: 20px;
            }
            .gallery-card {
                min-width: 240px;
                width: 240px;
            }
        }
        @media (max-width:650px){
            .gallery-scroll-container {
                padding: 0 1rem;
            }
            .hero-content { left:1.2rem; right:1.2rem; }
            .hero-title {font-size:2.1rem;}
            .area-slideshow-overlay {
                width: 280px;
                height: 180px;
                bottom: 50px;
                left: 15px;
            }
            .gallery-card {
                min-width: 220px;
                width: 220px;
            }
            .scroll-nav {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="hero-map" id="hero">
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-map-marked-alt"></i>
                <h1>Wisata Kalteng</h1>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="registrasi.php"><i class="fas fa-user-plus"></i> Daftar Pengguna</a></li>
                </ul>
            </nav>
        </div>
    </div>
    <div id="map"></div>
    <div class="overlay-hero"></div>
    <div class="hero-content">
        <h1 class="hero-title">Jelajahi Keindahan<br>Kalimantan Tengah</h1>
        <div class="hero-desc">
            Ayo jelajahi Kalimantan Tengah! Nikmati beragam destinasi wisata keren, event menarik, pilihan hotel yang nyaman, serta kuliner dan oleh-oleh  yang pasti bikin pengalaman liburanmu makin seru.
        </div>
    </div>
    <button class="scroll-down" onclick="document.getElementById('gallery').scrollIntoView({behavior:'smooth'})">
        <i class="fas fa-angle-down"></i>
    </button>

    <!-- Area Slideshow Overlay -->
    <div class="area-slideshow-overlay">
        <div class="slideshow-container">
            <div class="slideshow-indicator">
                <i class="fas fa-images"></i> <span id="currentType">Destinasi Populer</span>
            </div>
            <div class="slideshow-controls">
                <button class="control-btn play-pause-btn" onclick="toggleSlideshow()">
                    <i class="fas fa-pause" id="playPauseIcon"></i>
                </button>
                <button class="control-btn" onclick="previousSlide()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="control-btn" onclick="nextSlide()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <?php foreach($slideshowData as $index => $item): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-type="<?php echo $item['tipe']; ?>">
                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>" onerror="this.src='https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=300&fit=crop'">
                <div class="slide-info">
                    <div class="slide-title"><?php echo htmlspecialchars($item['nama']); ?></div>
                    <div class="slide-category">
                        <?php if($item['tipe'] == 'wisata'): ?>
                            <i class="fas fa-map-marker-alt type-icon"></i>
                        <?php elseif($item['tipe'] == 'kuliner'): ?>
                            <i class="fas fa-utensils type-icon"></i>
                        <?php elseif($item['tipe'] == 'oleholeh'): ?>
                            <i class="fas fa-gift type-icon"></i>
                        <?php else: ?>
                            <i class="fas fa-calendar-alt type-icon"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($item['kategori']); ?>
                    </div>
                    <div class="slide-desc"><?php echo htmlspecialchars($item['deskripsi']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="progress-bar" id="progressBar"></div>
    </div>
</div>

<!-- Galeri Destinasi Wisata -->
<div class="section-gallery" id="gallery">
    <div class="gallery-header">
        <h2 class="gallery-title">🌴 Destinasi Wisata</h2>
        <p class="gallery-subtitle">Temukan pilihan destinasi terbaik untuk liburanmu!</p>
        
    </div>
    <div class="gallery-scroll-container">
        <button class="scroll-nav scroll-left" onclick="scrollGallery('wisata', 'left')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="gallery-list" id="wisata-gallery">
            <?php
            $catColor = [
                "Wisata Alam" => "#50b08f",
                "Wisata Edukasi" => "#4d91ff",
                "Wisata Islami" => "#de71b6",
                "Wisata Buatan" => "#ffb74d",
                "Wisata Sejarah" => "#c96851"
            ];
            $q = mysqli_query($koneksi, "SELECT * FROM tb_wisata ORDER BY id_wisata DESC LIMIT 12");
            while ($r = mysqli_fetch_assoc($q)) {
                $img = explode(',', $r['gambar'])[0];
                $warna = $catColor[$r['kategori']] ?? "#764ba2";
                ?>
                <div class="gallery-card">
                    <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($r['nama_wisata']); ?>">
                    <div class="gallery-info">
                        <span class="gallery-category" style="background:<?= $warna ?>1a;color:<?= $warna ?>;"><?php echo htmlspecialchars($r['kategori']); ?></span>
                        <h3><?php echo htmlspecialchars($r['nama_wisata']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($r['deskripsi'], 0, 80)) . '...'; ?></p>
                        <div style="margin-top:1.1rem;">
                            <a href="index.php?page=MyApp/detail_wisata&id=<?= htmlspecialchars($r['id_wisata']) ?>" class="btn btn-sm" style="background:linear-gradient(90deg,#667eea,#764ba2);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;text-decoration:none;transition:.2s;">Detail</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <button class="scroll-nav scroll-right" onclick="scrollGallery('wisata', 'right')">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Galeri Kuliner -->
<div class="section-gallery culinary-gallery">
    <div class="gallery-header">
        <h2 class="gallery-title">🍽️ Kuliner</h2>
        <p class="gallery-subtitle">Nikmati cita rasa kuliner autentik Kalimantan Tengah!</p>
    </div>
    <div class="gallery-scroll-container">
        <button class="scroll-nav scroll-left" onclick="scrollGallery('kuliner', 'left')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="gallery-list" id="kuliner-gallery">
            <?php
            $priceColor = [
                "Murah" => "#28a745",
                "Sedang" => "#ffc107", 
                "Mahal" => "#dc3545"
            ];
            $qKuliner = mysqli_query($koneksi, "SELECT * FROM tb_kuliner ORDER BY id_kuliner DESC LIMIT 12");
            while ($rKuliner = mysqli_fetch_assoc($qKuliner)) {
                $imgKuliner = explode(',', $rKuliner['gambar'])[0];
                $warnaHarga = $priceColor[$rKuliner['harga_range']] ?? "#28a745";
                ?>
                <div class="gallery-card">
                    <img src="uploads/<?php echo htmlspecialchars($imgKuliner); ?>" alt="<?php echo htmlspecialchars($rKuliner['nama_kuliner']); ?>" onerror="this.src='https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400&h=300&fit=crop'">
                    <div class="gallery-info">
                        <span class="gallery-category" style="background:<?= $warnaHarga ?>1a;color:<?= $warnaHarga ?>;"><?php echo htmlspecialchars($rKuliner['harga_range']); ?></span>
                        <h3><?php echo htmlspecialchars($rKuliner['nama_kuliner']); ?></h3>
                        <p><i class="fas fa-map-marker-alt" style="color:#6c757d;margin-right:5px;"></i><?php echo htmlspecialchars(substr($rKuliner['alamat'], 0, 50)) . '...'; ?></p>
                        <div style="margin-top:1.1rem;">
                            <a href="index.php?page=MyApp/detail_kuliner&id=<?= htmlspecialchars($rKuliner['id_kuliner']) ?>" class="btn btn-sm" style="background:linear-gradient(90deg,#28a745,#20c997);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;text-decoration:none;transition:.2s;">Detail</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <button class="scroll-nav scroll-right" onclick="scrollGallery('kuliner', 'right')">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Galeri Oleh-oleh -->
<div class="section-gallery souvenir-gallery">
    <div class="gallery-header">
        <h2 class="gallery-title">🎁 Oleh-oleh </h2>
        <p class="gallery-subtitle">Bawa pulang kenangan istimewa dari Kalimantan Tengah!</p>
    </div>
    <div class="gallery-scroll-container">
        <button class="scroll-nav scroll-left" onclick="scrollGallery('souvenir', 'left')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="gallery-list" id="souvenir-gallery">
            <?php
            $qOlehOleh = mysqli_query($koneksi, "SELECT * FROM tb_oleh2 ORDER BY id_oleh2 DESC LIMIT 12");
            while ($rOlehOleh = mysqli_fetch_assoc($qOlehOleh)) {
                $imgOlehOleh = explode(',', $rOlehOleh['gambar'])[0];
                $warnaHargaOleh = $priceColor[$rOlehOleh['harga_range']] ?? "#ff7043";
                ?>
                <div class="gallery-card">
                    <img src="uploads/<?php echo htmlspecialchars($imgOlehOleh); ?>" alt="<?php echo htmlspecialchars($rOlehOleh['nama_toko']); ?>" onerror="this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&h=300&fit=crop'">
                    <div class="gallery-info">
                        <span class="gallery-category" style="background:<?= $warnaHargaOleh ?>1a;color:<?= $warnaHargaOleh ?>;"><?php echo htmlspecialchars($rOlehOleh['harga_range']); ?></span>
                        <h3><?php echo htmlspecialchars($rOlehOleh['nama_toko']); ?></h3>
                        <p><i class="fas fa-map-marker-alt" style="color:#6c757d;margin-right:5px;"></i><?php echo htmlspecialchars(substr($rOlehOleh['alamat'], 0, 50)) . '...'; ?></p>
                        <div style="margin-top:1.1rem;">
                            <a href="index.php?page=MyApp/detail_oleholeh&id=<?= htmlspecialchars($rOlehOleh['id_oleh2']) ?>" class="btn btn-sm" style="background:linear-gradient(90deg,#ff7043,#ffb74d);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;text-decoration:none;transition:.2s;">Detail</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <button class="scroll-nav scroll-right" onclick="scrollGallery('souvenir', 'right')">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Galeri Event -->
<div class="section-gallery event-gallery">
    <div class="gallery-header">
        <h2 class="gallery-title">🎉 Event & Acara</h2>
        <p class="gallery-subtitle">Jangan lewatkan acara menarik di Kalimantan Tengah!</p>
    </div>
    <div class="gallery-scroll-container">
        <button class="scroll-nav scroll-left" onclick="scrollGallery('event', 'left')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="gallery-list" id="event-gallery">
            <?php
            $eventCatColor = [
                "Festival" => "#8b4513",
                "Budaya" => "#dc3545",
                "Olahraga" => "#007bff",
                "Seni" => "#6f42c1",
                "Edukasi" => "#28a745"
            ];
            // Tambahkan kondisi WHERE untuk event yang belum lewat
            $qEvent = mysqli_query($koneksi, "SELECT * FROM tb_event WHERE tanggal_selesai >= CURDATE() ORDER BY tanggal_mulai ASC LIMIT 12");
            while ($rEvent = mysqli_fetch_assoc($qEvent)) {
                $imgEvent = explode(',', $rEvent['gambar'])[0];
                $warnaEvent = $eventCatColor[$rEvent['kategori']] ?? "#8b4513";
                $tanggalMulai = date('d M Y', strtotime($rEvent['tanggal_mulai']));
                $tanggalSelesai = date('d M Y', strtotime($rEvent['tanggal_selesai']));
                ?>
                <div class="gallery-card">
                    <img src="uploads/<?php echo htmlspecialchars($imgEvent); ?>" alt="<?php echo htmlspecialchars($rEvent['nama_event']); ?>" onerror="this.src='https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=400&h=300&fit=crop'">
                    <div class="gallery-info">
                        <span class="gallery-category" style="background:<?= $warnaEvent ?>1a;color:<?= $warnaEvent ?>;"><?php echo htmlspecialchars($rEvent['kategori']); ?></span>
                        <h3><?php echo htmlspecialchars($rEvent['nama_event']); ?></h3>
                        <p><i class="fas fa-calendar" style="color:#6c757d;margin-right:5px;"></i><?php echo $tanggalMulai . ' - ' . $tanggalSelesai; ?></p>
                        <p><i class="fas fa-map-marker-alt" style="color:#6c757d;margin-right:5px;"></i><?php echo htmlspecialchars($rEvent['kabupaten']); ?></p>
                        <div style="margin-top:1.1rem;">
                            <a href="index.php?page=MyApp/detail_event&id=<?= htmlspecialchars($rEvent['id_event']) ?>" class="btn btn-sm" style="background:linear-gradient(90deg,#8b4513,#a0522d);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;text-decoration:none;transition:.2s;">Detail</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <button class="scroll-nav scroll-right" onclick="scrollGallery('event', 'right')">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Galeri hotel -->
<div class="section-gallery" id="gallery">
    <div class="gallery-header">
        <h2 class="gallery-title">🏨 Hotel</h2>
        <p class="gallery-subtitle">Temukan pilihan hotel terbaik untuk menginap!</p>
    </div>
    <div class="gallery-scroll-container">
        <button class="scroll-nav scroll-left" onclick="scrollGallery('hotel', 'left')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="gallery-list" id="hotel-gallery">
            <?php
            $q = mysqli_query($koneksi, "SELECT * FROM tb_hotel ORDER BY id_hotel DESC LIMIT 12");
            while ($r = mysqli_fetch_assoc($q)) {
                $img = explode(',', $r['gambar'])[0];
                ?>
                <div class="gallery-card">
                    <img src="uploads/<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($r['nama_hotel']); ?>">
                    <div class="gallery-info">
                        <h3><?php echo htmlspecialchars($r['nama_hotel']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($r['deskripsi'], 0, 80)) . '...'; ?></p>
                        <div style="margin-top:1.1rem;">
                            <a href="index.php?page=MyApp/detail_hotel&id=<?= htmlspecialchars($r['id_hotel']) ?>" class="btn btn-sm" style="background:linear-gradient(90deg,#667eea,#764ba2);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;text-decoration:none;transition:.2s;">Detail</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <button class="scroll-nav scroll-right" onclick="scrollGallery('hotel', 'right')">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>


<!-- Footer -->
<footer style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 3rem 0 2rem 0; text-align: center; margin-top: 3rem;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h3 style="margin-bottom: 1rem; font-size: 1.5rem;"><i class="fas fa-map-marked-alt"></i> Wisata Kalteng</h3>
                <p style="opacity: 0.9; line-height: 1.6;">Jelajahi keindahan Kalimantan Tengah dengan panduan wisata terlengkap yang mencakup destinasi menawan, kuliner, tempat oleh-oleh, rekomendasi hotel nyaman, serta beragam event menarik yang sayang untuk dilewatkan.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 1rem;">Kategori</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><a href="index.php?page=MyApp/wisata" style="color: rgba(255,255,255,0.8); text-decoration: none;">Destinasi Wisata</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="index.php?page=MyApp/kuliner" style="color: rgba(255,255,255,0.8); text-decoration: none;">Kuliner </a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="index.php?page=MyApp/oleholeh" style="color: rgba(255,255,255,0.8); text-decoration: none;">Oleh-oleh</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="index.php?page=MyApp/event" style="color: rgba(255,255,255,0.8); text-decoration: none;">Event & Acara</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="index.php?page=MyApp/hotel" style="color: rgba(255,255,255,0.8); text-decoration: none;">Hotel</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 1rem;">Kontak</h4>
                <p style="opacity: 0.9; margin-bottom: 0.5rem;"><i class="fas fa-envelope"></i> info@wisatakalteng.com</p>
                <p style="opacity: 0.9; margin-bottom: 0.5rem;"><i class="fas fa-phone"></i> +62 812-3456-7890</p>
                
            </div>
        </div>
        <hr style="border: none; height: 1px; background: rgba(255,255,255,0.2); margin: 2rem 0;">
        <p style="opacity: 0.8; margin: 0;">&copy; <?php echo date('Y'); ?> Wisata Kalteng. Semua hak dilindungi.</p>
    </div>
</footer>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
// Inisialisasi Peta
var map = L.map('map', {
            zoomControl: false,
            scrollWheelZoom: false,
            dragging: true,
            touchZoom: true,
            doubleClickZoom: false
        }).setView([-1.68, 113.38], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Data lokasi dengan kategori yang berbeda
        var locations = [
            // Wisata
            {name: "Taman Nasional Tanjung Puting", lat: -2.7441, lng: 111.6837, category: "wisata"},
            {name: "Danau Sentarum", lat: -0.7893, lng: 112.0789, category: "wisata"},
            {name: "Bukit Batu", lat: -1.6278, lng: 113.9867, category: "wisata"},
            
            // Kuliner
            {name: "Warung Soto Banjar Bu Yanti", lat: -2.2, lng: 113.9, category: "kuliner"},
            {name: "Restoran Patin Bakar", lat: -1.7, lng: 113.4, category: "kuliner"},
            
            // Oleh-oleh
            {name: "Toko Oleh-oleh Khas Dayak", lat: -2.7542848, lng: 114.2593913, category: "oleh2"},

            
            
            // Hotel
            {name: "Hotel Dafam", lat: -2.5411512, lng: 112.9462881, category: "hotel"},
            
            
            
            // Event
            {name: "Festival Erau", lat: -1.3596999, lng: 113.3505022, category: "event"},
            
        ];

        // Fungsi untuk membuat icon berdasarkan kategori
        function createCategoryIcon(category) {
            var iconClass, bgClass;
            
            switch(category) {
                case 'wisata':
                    iconClass = 'fa-map-marked-alt';
                    bgClass = 'wisata';
                    break;
                case 'kuliner':
                    iconClass = 'fa-utensils';
                    bgClass = 'kuliner';
                    break;
                case 'oleh2':
                    iconClass = 'fa-gift';
                    bgClass = 'oleh2';
                    break;
                case 'hotel':
                    iconClass = 'fa-bed';
                    bgClass = 'hotel';
                    break;
                case 'event':
                    iconClass = 'fa-calendar';
                    bgClass = 'event';
                    break;
                default:
                    iconClass = 'fa-map-marker';
                    bgClass = 'wisata';
            }
            
            return L.divIcon({
                className: 'custom-marker',
                html: '<div class="marker-icon ' + bgClass + '"><i class="fas ' + iconClass + '"></i></div>',
                iconSize: [35, 35],
                iconAnchor: [17, 17]
            });
        }

        // Menambahkan marker untuk setiap lokasi
        locations.forEach(function(location) {
            var customIcon = createCategoryIcon(location.category);
            
            L.marker([location.lat, location.lng], {icon: customIcon})
                .addTo(map)
                .bindPopup('<b>' + location.name + '</b><br><small>Kategori: ' + 
                          location.category.charAt(0).toUpperCase() + location.category.slice(1) + '</small>');
        });

// Slideshow functionality
let currentSlide = 0;
let isPlaying = true;
let slideInterval;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    if (slides[index]) {
        slides[index].classList.add('active');
        updateTypeIndicator(slides[index].dataset.type);
    }
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    showSlide(currentSlide);
    resetProgress();
}

function previousSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    showSlide(currentSlide);
    resetProgress();
}

function updateTypeIndicator(type) {
    const typeMap = {
        'wisata': 'Destinasi Wisata',
        'kuliner': 'Kuliner Khas',
        'oleholeh': 'Oleh-oleh',
        'event': 'Event & Acara',
        'hotel': 'Hotel'
    };
    document.getElementById('currentType').textContent = typeMap[type] || 'Destinasi Populer';
}

function toggleSlideshow() {
    const playPauseIcon = document.getElementById('playPauseIcon');
    if (isPlaying) {
        clearInterval(slideInterval);
        playPauseIcon.className = 'fas fa-play';
        isPlaying = false;
    } else {
        startSlideshow();
        playPauseIcon.className = 'fas fa-pause';
        isPlaying = true;
    }
}

function startSlideshow() {
    slideInterval = setInterval(() => {
        nextSlide();
    }, 4000);
}

function resetProgress() {
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = '0%';
    setTimeout(() => {
        progressBar.style.transition = 'width 4s linear';
        progressBar.style.width = '100%';
    }, 50);
}

// Initialize slideshow
if (totalSlides > 0) {
    startSlideshow();
    resetProgress();
    updateTypeIndicator(slides[0].dataset.type);
}

// Gallery horizontal scroll functionality
function scrollGallery(galleryType, direction) {
    const gallery = document.getElementById(galleryType + '-gallery');
    const scrollAmount = 280; // card width + gap
    
    if (direction === 'left') {
        gallery.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        gallery.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
    
    // Update scroll buttons state
    setTimeout(() => updateScrollButtons(galleryType), 300);
}

function updateScrollButtons(galleryType) {
    const gallery = document.getElementById(galleryType + '-gallery');
    const container = gallery.parentElement;
    const leftBtn = container.querySelector('.scroll-left');
    const rightBtn = container.querySelector('.scroll-right');
    
    // Check if at the beginning
    if (gallery.scrollLeft <= 0) {
        leftBtn.classList.add('disabled');
    } else {
        leftBtn.classList.remove('disabled');
    }
    
    // Check if at the end
    if (gallery.scrollLeft >= gallery.scrollWidth - gallery.clientWidth - 10) {
        rightBtn.classList.add('disabled');
    } else {
        rightBtn.classList.remove('disabled');
    }
}

// Initialize scroll buttons state for all galleries
document.addEventListener('DOMContentLoaded', function() {
    ['wisata', 'kuliner', 'souvenir', 'event', 'hotel'].forEach(type => {
        updateScrollButtons(type);
        
        // Add scroll event listener to update buttons
        const gallery = document.getElementById(type + '-gallery');
        if (gallery) {
            gallery.addEventListener('scroll', () => updateScrollButtons(type));
        }
    });
});

// Smooth scroll for hero button
document.querySelector('.scroll-down').addEventListener('click', function() {
    document.getElementById('gallery').scrollIntoView({
        behavior: 'smooth'
    });
});
</script>

</body>
</html>