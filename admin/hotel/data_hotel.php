<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$kabupaten = isset($_GET['kabupaten']) ? mysqli_real_escape_string($koneksi, $_GET['kabupaten']) : '';
$rating    = isset($_GET['rating'])    ? mysqli_real_escape_string($koneksi, $_GET['rating'])    : '';
$urutan    = isset($_GET['urutan'])    ? mysqli_real_escape_string($koneksi, $_GET['urutan'])    : '';

// Build WHERE conditions
$where = [];
$having = [];

if ($kabupaten !== '') $where[] = "th.kabupaten='{$kabupaten}'";
if ($rating    !== '') $having[] = "ROUND(AVG(tks.rating)) = '{$rating}'";

$whereSql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
$havingSql = count($having) ? ' HAVING ' . implode(' AND ', $having) : '';

// Build ORDER BY clause
$orderBy = "ORDER BY ";
switch ($urutan) {
    case 'harga_termurah':
        $orderBy .= "CAST(REPLACE(REPLACE(th.harga_hotel, 'Rp ', ''), '.', '') AS UNSIGNED) ASC, th.nama_hotel ASC";
        break;
    case 'harga_termahal':
        $orderBy .= "CAST(REPLACE(REPLACE(th.harga_hotel, 'Rp ', ''), '.', '') AS UNSIGNED) DESC, th.nama_hotel ASC";
        break;
    default:
        $orderBy .= "avg_rating DESC, th.nama_hotel ASC";
        break;
}

// Modified query to include rating calculation with proper error handling
$queryString = "
    SELECT th.*, 
           ROUND(AVG(tks.rating), 1) as avg_rating,
           COUNT(tks.rating) as total_reviews
    FROM tb_hotel th 
    LEFT JOIN tb_kritik_saran_hotel tks ON th.id_hotel = tks.id_hotel
    {$whereSql}
    GROUP BY th.id_hotel
    {$havingSql}
    {$orderBy}
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
                break; // Keluar dari loop setelah menemukan gambar pertama
            }
        }
        
        // Jika ada gambar yang valid, tambahkan ke slideshow
        if ($firstImage) {
            $imgPath = "uploads/" . $firstImage;
            $title = $data['nama_hotel'];
            $location = $data['alamat'] . ', ' . $data['kabupaten'];
            $contact = $data['kontak'];
            $price = $data['harga_hotel'];
            $rating = $data['avg_rating'] ? $data['avg_rating'] : 0;
            
            $slideshowImages[] = [
                'src' => $imgPath,
                'title' => $title,
                'location' => $location,
                'contact' => $contact,
                'price' => $price,
                'rating' => $rating
            ];
        }
    }
    $sql->data_seek(0);
}

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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body { margin: 0; background: #f6f7fa; }
.content-header h1 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 2rem;
    color:rgb(5, 7, 15);
    letter-spacing: 1.2px;
    font-weight: bold;
}

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
    box-shadow: 0 4px 18px rgba(220,53,69,0.13);
    overflow: hidden;
    background: #ff9a56;
    border: 2.5px solid rgb(52, 13, 205);
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

.slide-contact {
    font-size: 1rem;
    color: #ff9a56;
    margin-bottom: 5px;
    font-weight: 600;
}

.slide-price {
    font-size: 1rem;
    color: #ffa726;
    margin-bottom: 5px;
    font-weight: 600;
}

.slide-location {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 5px;
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
    background: linear-gradient(90deg, #ff9a56 20%, #ff6b35 100%);
    color: white;
    border: none;
    padding: 8px 22px;
    border-radius: 22px;
    font-weight: 600;
    font-size: 1rem;
    letter-spacing: 1px;
    box-shadow: 0 2px 8px rgba(220,53,69,0.08);
    transition: background 0.18s, box-shadow 0.18s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-tambah-data:hover {
    background: linear-gradient(90deg, #ff6b35 10%, #ff9a56 100%);
    color: white;
    box-shadow: 0 4px 18px rgba(220,53,69,0.15);
}
.hotel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}
.hotel-item {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(220,53,69,0.08);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1.5px solid #f8d7da;
}
.hotel-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 25px rgba(255,107,122,0.12);
    border: 1.5px solid #ff9a56;
}
.hotel-image {
    position: relative;
}
.hotel-image img {
    width: 100%;
    height: 200px;
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
.hotel-content {
    padding: 15px;
}
.hotel-title {
    font-size: 1.32rem;
    font-weight: bold;
    margin-bottom: 10px;
    color:rgb(8, 0, 1);
    letter-spacing: 0.5px;
}
.hotel-content p {
    margin: 5px 0;
    color: #3e4c4b;
    font-size: 0.97rem;
}
.hotel-actions {
    margin-top: 15px;
    text-align: center;
}
.hotel-actions .btn {
    border-radius: 20px;
    padding: 6px 15px;
    font-weight: 600;
    background: linear-gradient(90deg, #ff6b35 10%, #ff9a56 100%);
    color: white;
    text-decoration: none;
    border: none;
    transition: .18s;
    margin: 0 3px;
}
.hotel-actions .btn:hover {
    background: linear-gradient(90deg, #ff9a56 20%, #ff6b35 100%);
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
    background: linear-gradient(90deg, #ff6b35 10%, #ff9a56 100%);
    color: white;
    cursor: pointer;
    transition: background 0.3s;
}
.filter-form button:hover {
    background: linear-gradient(90deg, #ff9a56 20%, #ff6b35 100%);
}
.hotel-price {
    color:rgb(14, 1, 2);
    font-weight: bold;
    font-size: 1.1rem;
}
.hotel-facilities {
    color:rgb(0, 8, 2);
    font-size: 0.9rem;
    margin: 5px 0;
}

/* No rating indicator */
.no-rating {
    color: #999;
    font-size: 0.9rem;
    font-style: italic;
}

/* Filter info */
.filter-info {
    text-align: center;
    margin-bottom: 20px;
    padding: 10px;
    background: #e8f4f8;
    border-radius: 10px;
    color: #2c5aa0;
    font-size: 0.95rem;
}

.filter-info i {
    margin-right: 5px;
}

/* Sort indicator */
.sort-indicator {
    display: inline-block;
    margin-left: 5px;
    font-size: 0.8rem;
    color: #666;
}
</style>

<section class="content-header">
    <h1>🏨 Hotel & Penginapan</h1>
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
                    <div class="slide-contact">
                        <i class="fas fa-phone"></i> <?= htmlspecialchars($image['contact']) ?>
                    </div>
                    <div class="slide-price">
                        <i class="fas fa-money-bill"></i> <?= htmlspecialchars($image['price']) ?>
                    </div>
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
                <i class="fas fa-hotel" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p>Tidak ada gambar hotel untuk ditampilkan</p>
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
        <a href="index.php?page=MyApp/tabel_hotel" class="btn-tambah-data">
            <i class="fa fa-plus"></i> Tambah Data
        </a>
    </div>
<?php endif; ?>

<section class="content">
    <!-- Enhanced Filter Form with Rating and Price Sort -->
    <form class="filter-form" method="GET">
        <input type="hidden" name="page" value="MyApp/data_hotel">

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

        <select name="urutan">
            <option value="">📊 Urutkan Harga</option>
            <?php 
            $urutan_list = [
                "harga_termurah" => "💰",
                "harga_termahal" => "💎"
            ];
            
            foreach ($urutan_list as $order => $icon): ?>
                <option value="<?= $order ?>" <?= $urutan===$order?'selected':'' ?>><?= $icon ?> Harga <?= ucfirst(str_replace('harga_', '', $order)) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit"><i class="fa fa-search"></i> Filter</button>
    </form>

    <!-- Filter Info -->
    <?php if ($kabupaten || $rating || $urutan): ?>
        <div class="filter-info">
            <i class="fas fa-info-circle"></i>
            <?php 
            $filterInfo = [];
            if ($kabupaten) $filterInfo[] = "Kabupaten: <strong>{$kabupaten}</strong>";
            if ($rating) $filterInfo[] = "Rating: <strong>{$rating} bintang</strong>";
            if ($urutan) {
                $urutanText = [
                    'harga_termurah' => 'Harga Termurah',
                    'harga_termahal' => 'Harga Termahal'
                ];
                $filterInfo[] = "Urutan: <strong>{$urutanText[$urutan]}</strong>";
            }
            echo "Filter aktif: " . implode(' | ', $filterInfo);
            ?>
        </div>
    <?php endif; ?>

    <div class="hotel-grid">
        <?php
        // Reset pointer only if we have valid result
        if ($sql && $sql->num_rows > 0) {
            $sql->data_seek(0); // ulang pointer
        }
        
        if ($sql && $sql->num_rows): ?>
            <?php while ($d = $sql->fetch_assoc()): 
                $imgs = explode(',', $d['gambar']);
                $img = !empty($imgs[0]) ? htmlspecialchars($imgs[0]) : 'placeholder.png';
                $avgRating = $d['avg_rating'] ? $d['avg_rating'] : 0;
                $totalReviews = $d['total_reviews'] ? $d['total_reviews'] : 0;
            ?>
                <div class="hotel-item">
                    <div class="hotel-image">
                        <img src="uploads/<?= $img ?>" alt="<?= htmlspecialchars($d['nama_hotel']) ?>">
                        <?php if ($avgRating > 0): ?>
                            <div class="rating-badge">
                                <i class="fas fa-star"></i>
                                <?= number_format($avgRating, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="hotel-content">
                        <h3 class="hotel-title"><?= htmlspecialchars($d['nama_hotel']) ?></h3>
                        
                        <!-- Rating Display -->
                        <?php if ($avgRating > 0): ?>
                            <?= generateStarRating($avgRating, $totalReviews) ?>
                        <?php else: ?>
                            <div class="no-rating">Belum ada rating</div>
                        <?php endif; ?>
                        
                        <p class="hotel-price">
                            <i class="fa fa-money-bill" style="color:#dc3545"></i> <?= htmlspecialchars($d['harga_hotel']) ?>
                        </p>
                        <p class="hotel-facilities">
                            <i class="fa fa-star" style="color:#28a745"></i> <?= htmlspecialchars($d['fasilitas']) ?>
                        </p>
                        <p><i class="fa fa-phone" style="color:#dc3545"></i> <?= htmlspecialchars($d['kontak']) ?></p>
                        <p><i class="fa fa-location-dot" style="color:#dc3545"></i> <?= htmlspecialchars($d['alamat']) ?>, <?= htmlspecialchars($d['kabupaten']) ?></p>
                        <div class="hotel-actions">
                            <a href="index.php?page=MyApp/detail_hotel&id=<?= htmlspecialchars($d['id_hotel']) ?>" class="btn btn-sm">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;width:100%;grid-column:1/-1;padding:2rem;color:#666;">
                <i class="fas fa-search" style="font-size:2rem;margin-bottom:1rem;opacity:0.5;"></i><br>
                Tidak ada data hotel yang sesuai dengan filter yang dipilih.
            </p>
        <?php endif; ?>
    </div>
</section>