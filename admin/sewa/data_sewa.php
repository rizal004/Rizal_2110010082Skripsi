<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$kabupaten = isset($_GET['kabupaten']) ? mysqli_real_escape_string($koneksi, $_GET['kabupaten']) : '';
$jenis_kendaraan = isset($_GET['jenis_kendaraan']) ? mysqli_real_escape_string($koneksi, $_GET['jenis_kendaraan']) : '';
$harga_filter = isset($_GET['harga_filter']) ? mysqli_real_escape_string($koneksi, $_GET['harga_filter']) : '';
$rating = isset($_GET['rating']) ? mysqli_real_escape_string($koneksi, $_GET['rating']) : '';

$where = [];
$having = [];

$where[] = "tm.provinsi='Kalimantan Tengah'";
if ($kabupaten !== '') $where[] = "tm.kabupaten='$kabupaten'";
if ($jenis_kendaraan !== '') $where[] = "tm.jenis_kendaraan='$jenis_kendaraan'";
if ($rating !== '') $having[] = "ROUND(AVG(tks.rating)) = '$rating'";

// Filter harga
if ($harga_filter !== '') {
    if ($harga_filter === 'termurah') {
        $orderBy = " ORDER BY CAST(tm.harga_sewa AS UNSIGNED) ASC";
    } elseif ($harga_filter === 'termahal') {
        $orderBy = " ORDER BY CAST(tm.harga_sewa AS UNSIGNED) DESC";
    } else {
        $orderBy = " ORDER BY avg_rating DESC, tm.tanggal_upload DESC";
    }
} else {
    $orderBy = " ORDER BY avg_rating DESC, tm.tanggal_upload DESC";
}

$whereSql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
$havingSql = count($having) ? ' HAVING ' . implode(' AND ', $having) : '';

// Modified query to include rating calculation
$queryString = "
    SELECT tm.*, 
           ROUND(AVG(tks.rating), 1) as avg_rating,
           COUNT(tks.rating) as total_reviews
    FROM tb_motor tm 
    LEFT JOIN tb_feedback_sewa tks ON tm.id_motor = tks.id_motor
    {$whereSql}
    GROUP BY tm.id_motor
    {$havingSql}
    {$orderBy}
";

$sql = $koneksi->query($queryString);

// Check for query errors
if (!$sql) {
    die("Error dalam query: " . $koneksi->error . "<br>Query: " . $queryString);
}

// Static slideshow images - menggunakan hanya 34.jpg dan 35.jpg
$slideshowImages = [
    [
        'src' => 'uploads/35.jpg',
        'title' => 'Sewa Kendaraan Terpercaya',
        'location' => 'Kalimantan Tengah',
        'harga' => 'Harga Terjangkau',
        'jenis' => 'Motor & Mobil',
        'rating' => 0
    ],
    [
        'src' => 'uploads/34.jpg',
        'title' => 'Kendaraan Berkualitas',
        'location' => 'Tersedia di Seluruh Kalteng',
        'harga' => 'Pelayanan Terbaik',
        'jenis' => 'Rental Terpercaya',
        'rating' => 0
    ]
];

$jenisColor = [
    "Motor" => "#50b08f",
    "Mobil" => "#4d91ff"
];

// Function to generate star rating HTML
function generateStarRating($rating, $totalReviews = 0) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $html = '<div class="star-rating">';
    
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    
    // Half star
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    
    $html .= '<span class="rating-text">(' . number_format($rating, 1) . ')';
    if ($totalReviews > 0) {
        $html .= ' - ' . $totalReviews . ' ulasan';
    }
    $html .= '</span></div>';
    
    return $html;
}

// Fungsi untuk mendapatkan gambar yang valid
function getValidImage($gambarString) {
    if (empty($gambarString)) {
        return 'uploads/35.jpg';
    }
    
    $gambarList = explode(',', $gambarString);
    foreach ($gambarList as $gambar) {
        $gambar = trim($gambar);
        if (!empty($gambar)) {
            $imgPath = "uploads/" . $gambar;
            if (file_exists($imgPath)) {
                return $imgPath;
            }
        }
    }
    
    return 'uploads/35.jpg';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Motor & Mobil Kalimantan Tengah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; background: #f6f7fa; }
        .content-header h1 { text-align: center; margin-bottom: 25px; font-size: 2rem; }
        
        /* Star Rating Styles */
        .star-rating {
            display: flex;
            align-items: center;
            gap: 2px;
            margin: 8px 0;
        }
        
        .star-rating i {
            color: #ffd700;
            font-size: 1rem;
        }
        
        .star-rating .far {
            color: #ddd;
        }
        
        .rating-text {
            margin-left: 8px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .slide-rating {
            margin-top: 8px;
        }
        
        .slide-rating .star-rating {
            justify-content: center;
        }
        
        .slide-rating .star-rating i {
            color: #fff;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        
        .slide-rating .rating-text {
            color: rgba(255,255,255,0.9);
        }
        
        /* Photo Slideshow Styles */
        .slideshow-container {
            position: relative;
            width: 100%;
            height: 520px;
            margin: 0 0 24px 0;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(30,144,255,0.11);
            overflow: hidden;
            background: #e6f6ff;
            border: 2.5px solid rgb(247, 249, 248);
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
            transition: transform 0.9s ease;
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

        .slide-info {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .slide-jenis {
            display: inline-block;
            background: rgba(52,144,220,0.8);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .slide-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
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
            background: rgba(0,0,0,0.8);
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
            background: white;
            transform: scale(1.2);
        }

        .slide-counter {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
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
            color: #6c757d;
            font-size: 1.2rem;
            text-align: center;
        }
        
        .tambah-btn-wrap {
            display: flex;
            justify-content: flex-end;
            margin: 10px 0 15px 0;
            max-width: 100%;
        }
        .btn-tambah-data {
            background: linear-gradient(90deg,#23bb92 10%,#1f89e3 100%);
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 22px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(30,144,255,0.08);
            transition: background 0.18s, box-shadow 0.18s;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-tambah-data:hover {
            background: linear-gradient(90deg,#1f89e3 10%,#23bb92 100%);
            color: #fff;
            box-shadow: 0 4px 18px rgba(31, 137, 227, 0.15);
        }
        
        .motor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .motor-item {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .motor-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        .motor-image {
            position: relative;
        }
        .motor-image img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .rating-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .rating-badge i {
            color: #ffd700;
        }
        
        .motor-content {
            padding: 15px;
        }
        .motor-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .gallery-category {
            display: inline-block;
            margin-bottom: 9px;
            padding: 0.28em 0.92em;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.98em;
            letter-spacing: .2px;
        }
        .motor-content p {
            margin: 5px 0;
            color: #555;
            font-size: 0.95rem;
        }
        .motor-price {
            font-size: 1.1rem;
            font-weight: bold;
            color:rgb(14, 13, 13);
            margin: 10px 0;
        }
        .motor-actions {
            margin-top: 15px;
            text-align: center;
        }
        .motor-actions .btn {
            border-radius: 20px;
            padding: 6px 15px;
            font-weight: 600;
            text-decoration: none;
            transition: .2s;
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .filter-form button {
            background-color: #17a2b8;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .filter-form button:hover {
            background-color: #138496;
        }
        .filter-form select:focus, .filter-form button:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(23,162,184,0.25);
        }
        
        /* No rating indicator */
        .no-rating {
            color: #999;
            font-size: 0.9rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <section class="content-header">
        <h1>🚗 Sewa Motor & Mobil</h1>
    </section>
    
    <!-- Photo Slideshow -->
    <div class="slideshow-container" id="slideshow">
        <div class="slide-counter">
            <span id="current-slide">1</span> / <span id="total-slides"><?= count($slideshowImages) ?></span>
        </div>
        
        <?php foreach ($slideshowImages as $index => $image): ?>
            <div class="slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                <img src="<?= htmlspecialchars($image['src']) ?>" 
                     alt="<?= htmlspecialchars($image['title']) ?>"
                     onerror="this.src='uploads/34.jpg'">
                <div class="slide-overlay">
                    <div class="slide-title"><?= htmlspecialchars($image['title']) ?></div>
                    <div class="slide-info">
                        <i class="fas fa-money-bill-wave"></i> <?= htmlspecialchars($image['harga']) ?>
                    </div>
                    <div class="slide-info">
                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($image['location']) ?>
                    </div>
                    <span class="slide-jenis"><?= htmlspecialchars($image['jenis']) ?></span>
                    <?php if ($image['rating'] > 0): ?>
                        <div class="slide-rating">
                            <?= generateStarRating($image['rating']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <button class="slide-nav prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slide-nav next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="slide-indicators">
            <?php foreach ($slideshowImages as $index => $image): ?>
                <div class="indicator <?= $index === 0 ? 'active' : '' ?>" onclick="currentSlide(<?= $index + 1 ?>)"></div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    let currentSlideIndex = 0;
    let slideInterval;
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        if (slides[index]) {
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            document.getElementById('current-slide').textContent = index + 1;
        }
    }

    function changeSlide(direction) {
        currentSlideIndex += direction;
        
        if (currentSlideIndex >= totalSlides) {
            currentSlideIndex = 0;
        } else if (currentSlideIndex < 0) {
            currentSlideIndex = totalSlides - 1;
        }
        
        showSlide(currentSlideIndex);
        resetAutoSlide();
    }

    function currentSlide(index) {
        currentSlideIndex = index - 1;
        showSlide(currentSlideIndex);
        resetAutoSlide();
    }

    function nextSlide() {
        currentSlideIndex++;
        if (currentSlideIndex >= totalSlides) {
            currentSlideIndex = 0;
        }
        showSlide(currentSlideIndex);
    }

    function startAutoSlide() {
        if (totalSlides > 1) {
            slideInterval = setInterval(nextSlide, 4000);
        }
    }

    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (totalSlides > 0) {
            showSlide(0);
            startAutoSlide();
            
            const slideshowContainer = document.getElementById('slideshow');
            slideshowContainer.addEventListener('mouseenter', () => clearInterval(slideInterval));
            slideshowContainer.addEventListener('mouseleave', startAutoSlide);
        }
    });
    </script>
    
    <?php if (isset($_SESSION['ses_level']) && $_SESSION['ses_level'] == "Administrator"): ?>
        <div class="tambah-btn-wrap" style="max-width:100%;">
            <a href="index.php?page=MyApp/tabel_sewa" class="btn-tambah-data">
                <i class="fa fa-plus"></i> Tambah Kendaraan
            </a>
        </div>
    <?php endif; ?>

    <!-- Enhanced Filter Form with Rating -->
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="page" value="MyApp/data_sewa">
        
        <select name="kabupaten">
            <option value="">🗺️ Semua Kabupaten</option>
            <?php 
            $kabupaten_list = [
                "Barito Selatan" => "📍",
                "Barito Timur" => "📍",
                "Barito Utara" => "📍",
                "Gunung Mas" => "📍",
                "Kapuas" => "📍",
                "Katingan" => "📍",
                "Kotawaringin Barat" => "📍",
                "Kotawaringin Timur" => "📍",
                "Lamandau" => "📍",
                "Murung Raya" => "📍",
                "Pulang Pisau" => "📍",
                "Seruyan" => "📍",
                "Sukamara" => "📍",
                "Kota Palangka Raya" => "📍"
            ];
            
            foreach ($kabupaten_list as $kab => $icon): ?>
                <option value="<?= $kab ?>" <?= $kabupaten===$kab?'selected':'' ?>><?= $icon ?> <?= $kab ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="jenis_kendaraan">
            <option value="">🚗 🏍️ Jenis Kendaraan</option>
            <?php 
            $jenis_kendaraan_list = [
                "Motor" => "🏍️",
                "Mobil" => "🚗"
            ];
            
            foreach ($jenis_kendaraan_list as $jenis => $icon): ?>
                <option value="<?= $jenis ?>" <?= $jenis_kendaraan===$jenis?'selected':'' ?>><?= $icon ?> <?= $jenis ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="harga_filter">
            <option value="">📊 Urutkan Harga</option>
            <?php 
            $harga_filter_list = [
                "termurah" => "💰",
                "termahal" => "💎"
            ];
            
            foreach ($harga_filter_list as $filter => $icon): ?>
                <option value="<?= $filter ?>" <?= $harga_filter===$filter?'selected':'' ?>><?= $icon ?> Harga <?= ucfirst($filter) ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="rating">
            <option value="">🌟 Semua Rating</option>
            <?php 
            $rating_list = [
                "5" => "⭐⭐⭐⭐⭐",
                "4" => "⭐⭐⭐⭐",
                "3" => "⭐⭐⭐",
                "2" => "⭐⭐",
                "1" => "⭐"
            ];
            
            foreach ($rating_list as $rate => $stars): ?>
                <option value="<?= $rate ?>" <?= $rating===$rate?'selected':'' ?>><?= $stars ?> (<?= $rate ?> bintang)</option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit"><i class="fa fa-search"></i> Filter</button>
    </form>

    <!-- Motor Grid -->
    <div class="motor-grid">
        <?php
        // Reset pointer only if we have valid result
        if ($sql && $sql->num_rows > 0) {
            $sql->data_seek(0);
        }
        
        if ($sql && $sql->num_rows): ?>
            <?php while ($data = $sql->fetch_assoc()):
                $imgPath = getValidImage($data['gambar']);
                $warna = $jenisColor[$data['jenis_kendaraan']] ?? "#764ba2";
                $avgRating = $data['avg_rating'] ? $data['avg_rating'] : 0;
                $totalReviews = $data['total_reviews'] ? $data['total_reviews'] : 0;
            ?>
                <div class="motor-item">
                    <div class="motor-image">
                        <img src="<?= htmlspecialchars($imgPath) ?>" 
                             alt="<?= htmlspecialchars($data['nama_motor']) ?>"
                             onerror="this.src='uploads/34.jpg'">
                        <?php if ($avgRating > 0): ?>
                            <div class="rating-badge">
                                <i class="fas fa-star"></i>
                                <?= number_format($avgRating, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="motor-content">
                        <span class="gallery-category" style="background:<?= $warna ?>1a;color:<?= $warna ?>;">
                            <?= htmlspecialchars($data['jenis_kendaraan']) ?>
                        </span>
                        <h3 class="motor-title"><?= htmlspecialchars($data['nama_motor']) ?></h3>
                        
                        <!-- Rating Display -->
                        <?php if ($avgRating > 0): ?>
                            <?= generateStarRating($avgRating, $totalReviews) ?>
                        <?php else: ?>
                            <div class="no-rating">Belum ada rating</div>
                        <?php endif; ?>
                        
                        <p><i class="fa fa-tag"></i> <?= htmlspecialchars($data['merk']) ?> - <?= htmlspecialchars($data['tahun']) ?></p>
                        <p><i class="fa fa-palette"></i> <?= htmlspecialchars($data['warna']) ?></p>
                        <p><i class="fa fa-location-dot"></i> <?= htmlspecialchars($data['kabupaten']) ?></p>
                        <div class="motor-price">
                            <i class="fa fa-money-bill-wave"></i> Rp <?= number_format($data['harga_sewa'], 0, ',', '.') ?>/hari
                        </div>
                        <?php if (!empty($data['fasilitas'])): ?>
                            <p><i class="fa fa-star"></i> <?= htmlspecialchars($data['fasilitas']) ?></p>
                        <?php endif; ?>
                        
                        <div class="motor-actions">
                            <a href="index.php?page=MyApp/detail_sewa&id=<?= htmlspecialchars($data['id_motor']) ?>" 
                               class="btn btn-sm"
                               style="background:linear-gradient(90deg,<?= $warna ?>, #667eea);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;">
                                <i class="fa fa-info-circle"></i> Detail & Sewa
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;width:100%;grid-column:1/-1;padding:2rem;color:#666;">
                <i class="fas fa-search" style="font-size:2rem;margin-bottom:1rem;opacity:0.5;"></i><br>
                Tidak ada data kendaraan yang sesuai dengan filter yang dipilih.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>