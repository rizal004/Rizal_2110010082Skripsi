<?php
include "inc/koneksi.php";

$provinsi  = isset($_GET['provinsi'])  ? mysqli_real_escape_string($koneksi, $_GET['provinsi'])  : '';
$kabupaten = isset($_GET['kabupaten']) ? mysqli_real_escape_string($koneksi, $_GET['kabupaten']) : '';
$kategori  = isset($_GET['kategori'])  ? mysqli_real_escape_string($koneksi, $_GET['kategori'])  : '';
$rating    = isset($_GET['rating'])    ? mysqli_real_escape_string($koneksi, $_GET['rating'])    : '';

// Build WHERE conditions
$where = [];
$having = [];

if ($provinsi  !== '') $where[] = "tw.provinsi='{$provinsi}'";
if ($kabupaten !== '') $where[] = "tw.kabupaten='{$kabupaten}'";
if ($kategori  !== '') $where[] = "tw.kategori='{$kategori}'";
if ($rating    !== '') $having[] = "ROUND(AVG(tks.rating)) = '{$rating}'";

$whereSql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
$havingSql = count($having) ? ' HAVING ' . implode(' AND ', $having) : '';

// Modified query to include rating calculation with proper error handling
$queryString = "
    SELECT tw.*, 
           ROUND(AVG(tks.rating), 1) as avg_rating,
           COUNT(tks.rating) as total_reviews
    FROM tb_wisata tw 
    LEFT JOIN tb_kritik_saran tks ON tw.id_wisata = tks.id_wisata
    {$whereSql}
    GROUP BY tw.id_wisata
    {$havingSql}
    ORDER BY avg_rating DESC, tw.nama_wisata ASC
";

$sql = $koneksi->query($queryString);

// Check for query errors
if (!$sql) {
    die("Error dalam query: " . $koneksi->error . "<br>Query: " . $queryString);
}

// Collect all images for slideshow
$slideshowImages = [];
if ($sql && $sql->num_rows > 0) {
    $sql->data_seek(0);
    while ($data = $sql->fetch_assoc()) {
        $images = explode(',', $data['gambar']);
        
        // Ambil hanya gambar pertama yang tidak kosong
        $firstImage = null;
        foreach ($images as $img) {
            if (!empty(trim($img))) {
                $firstImage = trim($img);
                break;
            }
        }
        
        // Jika ada gambar yang valid, tambahkan ke slideshow
        if ($firstImage) {
            $imgPath = "uploads/" . $firstImage;
            $title = $data['nama_wisata'];
            $location = $data['alamat'] . ', ' . $data['kabupaten'];
            $rating = $data['avg_rating'] ? $data['avg_rating'] : 0;
            
            $slideshowImages[] = [
                'src' => $imgPath,
                'title' => $title,
                'location' => $location,
                'rating' => $rating
            ];
        }
    }
    // Reset pointer after building slideshow
    $sql->data_seek(0);
}

// Array warna kategori (untuk badge & marker & tombol)
$catColor = [
    "Wisata Alam" => "#50b08f",
    "Wisata Edukasi" => "#4d91ff",
    "Wisata Religi" => "#de71b6",
    "Wisata Buatan" => "#ffb74d",
    "Wisata Sejarah" => "#c96851"
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata Kalimantan Tengah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {  margin:0; background: #f6f7fa;}
        .content-header h1 {text-align: center; margin-bottom: 25px; font-size: 2rem;}
        
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

        .slide-location {
            font-size: 1rem;
            opacity: 0.9;
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
        }
        .btn-tambah-data:hover {
            background: linear-gradient(90deg,#1f89e3 10%,#23bb92 100%);
            color: #fff;
            box-shadow: 0 4px 18px rgba(31, 137, 227, 0.15);
        }
        .wisata-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .wisata-item {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .wisata-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        .wisata-image {
            position: relative;
        }
        .wisata-image img {
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
        .wisata-content {
            padding: 15px;
        }
        .wisata-title {
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
        .wisata-content p {
            margin: 5px 0;
            color: #555;
            font-size: 0.95rem;
        }
        .wisata-actions {
            margin-top: 15px;
            text-align: center;
        }
        .wisata-actions .btn {
            border-radius: 20px;
            padding: 6px 15px;
            font-weight: 600;
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
        <h1>🌴 Informasi Wisata</h1>
    </section>
    
    <!-- Photo Slideshow -->
    <div class="slideshow-container" id="slideshow">
        <?php if (!empty($slideshowImages)): ?>
            <div class="slide-counter">
                <span id="current-slide">1</span> / <span id="total-slides"><?= count($slideshowImages) ?></span>
            </div>
            
            <?php foreach ($slideshowImages as $index => $image): ?>
                <div class="slide <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>">
                    <img src="<?= htmlspecialchars($image['src']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                    <div class="slide-overlay">
                        <div class="slide-title"><?= htmlspecialchars($image['title']) ?></div>
                        <div class="slide-location">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($image['location']) ?>
                        </div>
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
        <?php else: ?>
            <div class="no-images">
                <div>
                    <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>Tidak ada gambar untuk ditampilkan</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    let currentSlideIndex = 0;
    let slideInterval;
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;

    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Show current slide
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
            slideInterval = setInterval(nextSlide, 4000); // Change slide every 4 seconds
        }
    }

    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }

    // Start auto slideshow when page loads
    document.addEventListener('DOMContentLoaded', function() {
        if (totalSlides > 0) {
            showSlide(0);
            startAutoSlide();
            
            // Pause auto slideshow on hover
            const slideshowContainer = document.getElementById('slideshow');
            slideshowContainer.addEventListener('mouseenter', () => clearInterval(slideInterval));
            slideshowContainer.addEventListener('mouseleave', startAutoSlide);
        }
    });
    </script>
    
    <?php if (isset($_SESSION['ses_level']) && $_SESSION['ses_level'] == "Administrator"): ?>
        <div class="tambah-btn-wrap" style="max-width:100%;">
            <a href="index.php?page=MyApp/tabel_wisata" class="btn-tambah-data">
                <i class="fa fa-plus"></i> Tambah Data
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Enhanced Filter Form with Rating -->
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="page" value="MyApp/data_wisata">
        
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

        <select name="kategori">
            <option value="">📍 Semua Kategori</option>
            <?php 
            $categories = [
                "Wisata Alam" => "🏞️",
                "Wisata Edukasi" => "📚", 
                "Wisata Religi" => "🕌",
                "Wisata Buatan" => "🎡",
                "Wisata Sejarah" => "🏛️"
            ];
            
            foreach ($categories as $cat => $icon): ?>
                <option value="<?= $cat ?>" <?= $kategori===$cat?'selected':'' ?>><?= $icon ?> <?= $cat ?></option>
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
    
    <div class="wisata-grid">
        <?php
        // Reset pointer only if we have valid result
        if ($sql && $sql->num_rows > 0) {
            $sql->data_seek(0); // ulang pointer
        }
        
        if ($sql && $sql->num_rows): ?>
            <?php while ($data = $sql->fetch_assoc()):
                $img = explode(',', $data['gambar'])[0];
                $warna = $catColor[$data['kategori']] ?? "#764ba2";
                $avgRating = $data['avg_rating'] ? $data['avg_rating'] : 0;
                $totalReviews = $data['total_reviews'] ? $data['total_reviews'] : 0;
            ?>
                <div class="wisata-item">
                    <div class="wisata-image">
                        <img src="uploads/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($data['nama_wisata']) ?>">
                        <?php if ($avgRating > 0): ?>
                            <div class="rating-badge">
                                <i class="fas fa-star"></i>
                                <?= number_format($avgRating, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wisata-content">
                        <span class="gallery-category" style="background:<?= $warna ?>1a;color:<?= $warna ?>;">
                            <?= htmlspecialchars($data['kategori']) ?>
                        </span>
                        <h3 class="wisata-title"><?= htmlspecialchars($data['nama_wisata']) ?></h3>
                        
                        <!-- Rating Display -->
                        <?php if ($avgRating > 0): ?>
                            <?= generateStarRating($avgRating, $totalReviews) ?>
                        <?php else: ?>
                            <div class="no-rating">Belum ada rating</div>
                        <?php endif; ?>
                        
                        <p><i class="fa fa-money-bill"></i>  
                            <?= is_numeric($data['harga_tiket']) && $data['harga_tiket'] > 0 
                                ? 'Rp' . number_format($data['harga_tiket'], 0, ',', '.') . ' per orang'
                                : htmlspecialchars($data['harga_tiket']) ?>
                        </p>
                        <p><i class="fa fa-location-dot"></i> <?= htmlspecialchars($data['alamat']) ?>, <?= htmlspecialchars($data['kabupaten']) ?></p>
                        <div class="wisata-actions">
                            <a href="index.php?page=MyApp/detail_wisata&id=<?= htmlspecialchars($data['id_wisata']) ?>"
                               class="btn btn-sm"
                               style="background:linear-gradient(90deg,<?= $warna ?>, #667eea);color:#fff;padding:0.5em 1.4em;border-radius:30px;font-weight:600;text-decoration:none;transition:.2s;">
                               <i class="fa fa-info-circle"></i> Detail 
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;width:100%;grid-column:1/-1;padding:2rem;color:#666;">
                <i class="fas fa-search" style="font-size:2rem;margin-bottom:1rem;opacity:0.5;"></i><br>
                Tidak ada data wisata yang sesuai dengan filter yang dipilih.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>