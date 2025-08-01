<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$provinsi  = isset($_GET['provinsi'])  ? mysqli_real_escape_string($koneksi, $_GET['provinsi'])  : '';
$kabupaten = isset($_GET['kabupaten']) ? mysqli_real_escape_string($koneksi, $_GET['kabupaten']) : '';
$rating    = isset($_GET['rating'])    ? mysqli_real_escape_string($koneksi, $_GET['rating'])    : '';

// Build WHERE conditions
$where = [];
$having = [];

if ($provinsi  !== '') $where[] = "to2.provinsi='{$provinsi}'";
if ($kabupaten !== '') $where[] = "to2.kabupaten='{$kabupaten}'";
if ($rating    !== '') $having[] = "ROUND(AVG(tks.rating)) = '{$rating}'";

$whereSql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
$havingSql = count($having) ? ' HAVING ' . implode(' AND ', $having) : '';

// Modified query to include rating calculation with proper error handling
$queryString = "
    SELECT to2.*, 
           ROUND(AVG(tks.rating), 1) as avg_rating,
           COUNT(tks.rating) as total_reviews
    FROM tb_oleh2 to2 
    LEFT JOIN tb_kritik_saran_oleh2 tks ON to2.id_oleh2 = tks.id_oleh2
    {$whereSql}
    GROUP BY to2.id_oleh2
    {$havingSql}
    ORDER BY avg_rating DESC, to2.nama_toko ASC
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
            $title = $data['nama_toko'];
            $location = $data['alamat'] . ', ' . $data['kabupaten'];
            $hours = $data['jam_operasional'];
            
            $rating = $data['avg_rating'] ? $data['avg_rating'] : 0;
            
            $slideshowImages[] = [
                'src' => $imgPath,
                'title' => $title,
                'location' => $location,
                'hours' => $hours,
                
                'rating' => $rating
            ];
        }
    }
    // Reset pointer after building slideshow
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
    color: #ff7043;
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
    box-shadow: 0 4px 18px rgba(255,112,67,0.13);
    overflow: hidden;
    background: #faf6f2;
    border: 2.5px solid rgb(240, 241, 242);
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

.slide-hours {
    font-size: 1rem;
    color: #ffb74d;
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
    background: linear-gradient(90deg,#ff7043 10%,#ffb74d 100%);
    color: #fff;
    border: none;
    padding: 8px 22px;
    border-radius: 22px;
    font-weight: 600;
    font-size: 1rem;
    letter-spacing: 1px;
    box-shadow: 0 2px 8px rgba(255,112,67,0.08);
    transition: background 0.18s, box-shadow 0.18s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-tambah-data:hover {
    background: linear-gradient(90deg,#ffb74d 10%,#ff7043 100%);
    color: #fff;
    box-shadow: 0 4px 18px rgba(255, 112, 67, 0.15);
}
.oleh2-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}
.oleh2-item {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(255,112,67,0.08);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1.5px solid #fae3d8;
}
.oleh2-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 25px rgba(255,183,77,0.12);
    border: 1.5px solid #ff7043;
}
.oleh2-image {
    position: relative;
}
.oleh2-image img {
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
.oleh2-content {
    padding: 15px;
}
.oleh2-title {
    font-size: 1.32rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #ff7043;
    letter-spacing: 0.5px;
}
.oleh2-content p {
    margin: 5px 0;
    color: #3e4c4b;
    font-size: 0.97rem;
}
.oleh2-actions {
    margin-top: 15px;
    text-align: center;
}
.oleh2-actions .btn {
    border-radius: 20px;
    padding: 6px 15px;
    font-weight: 600;
    background: linear-gradient(90deg,#ff7043,#ffb74d);
    color: #fff;
    text-decoration: none;
    border: none;
    transition: .18s;
    margin: 0 3px;
}
.oleh2-actions .btn:hover {
    background: linear-gradient(90deg,#ffb74d,#ff7043);
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
    background: linear-gradient(90deg,#ff7043 60%,#ffb74d 100%);
    color: white;
    cursor: pointer;
    transition: background 0.3s;
}
.filter-form button:hover {
    background: linear-gradient(90deg,#ffb74d 60%,#ff7043 100%);
}

/* No rating indicator */
.no-rating {
    color: #999;
    font-size: 0.9rem;
    font-style: italic;
}

</style>

<section class="content-header">
    <h1>🎁 Oleh-oleh Khas</h1>
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
                    <div class="slide-hours">
                        <i class="fas fa-clock"></i> <?= htmlspecialchars($image['hours']) ?>
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
        <a href="index.php?page=MyApp/tabel_oleh2" class="btn-tambah-data">
            <i class="fa fa-plus"></i> Tambah Data
        </a>
    </div>
<?php endif; ?>

<section class="content">
    <!-- Enhanced Filter Form with Rating -->
    <form class="filter-form" method="GET">
        <input type="hidden" name="page" value="MyApp/data_oleh2">

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

        <button type="submit"><i class="fa fa-search"></i> Filter</button>
    </form>

    <div class="oleh2-grid">
        <?php
        // Reset pointer only if we have valid result
        if ($sql && $sql->num_rows > 0) {
            $sql->data_seek(0); // ulang pointer
        }
        
        if ($sql && $sql->num_rows): ?>
            <?php while ($d = $sql->fetch_assoc()): 
                $avgRating = $d['avg_rating'] ? $d['avg_rating'] : 0;
                $totalReviews = $d['total_reviews'] ? $d['total_reviews'] : 0;
            ?>
                <div class="oleh2-item">
                    <div class="oleh2-image">
                        <?php
                        $imgs = explode(',', $d['gambar']);
                        $img = !empty($imgs[0]) ? htmlspecialchars($imgs[0]) : 'placeholder.png';
                        ?>
                        <img src="uploads/<?= $img ?>" alt="<?= htmlspecialchars($d['nama_toko']) ?>">
                        <?php if ($avgRating > 0): ?>
                            <div class="rating-badge">
                                <i class="fas fa-star"></i>
                                <?= number_format($avgRating, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="oleh2-content">
                        <h3 class="oleh2-title"><?= htmlspecialchars($d['nama_toko']) ?></h3>
                        
                        <!-- Rating Display -->
                        <?php if ($avgRating > 0): ?>
                            <?= generateStarRating($avgRating, $totalReviews) ?>
                        <?php else: ?>
                            <div class="no-rating">Belum ada rating</div>
                        <?php endif; ?>
                        
                        <p><i class="fa fa-clock" style="color:#007bff"></i> <?= htmlspecialchars($d['jam_operasional']) ?></p>
                        <p><i class="fa fa-location-dot" style="color:#007bff"></i> <?= htmlspecialchars($d['alamat']) ?>, <?= htmlspecialchars($d['kabupaten']) ?></p>
                        <div class="oleh2-actions">
                            <a href="index.php?page=MyApp/detail_oleh2&id=<?= htmlspecialchars($d['id_oleh2']) ?>" class="btn btn-sm">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;width:100%;grid-column:1/-1;padding:2rem;color:#666;">
                <i class="fas fa-search" style="font-size:2rem;margin-bottom:1rem;opacity:0.5;"></i><br>
                Tidak ada data oleh-oleh yang sesuai dengan filter yang dipilih.
            </p>
        <?php endif; ?>
    </div>
</section>