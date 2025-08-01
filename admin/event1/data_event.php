<?php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$provinsi  = isset($_GET['provinsi'])  ? mysqli_real_escape_string($koneksi, $_GET['provinsi'])  : '';
$kabupaten = isset($_GET['kabupaten']) ? mysqli_real_escape_string($koneksi, $_GET['kabupaten']) : '';
$kategori  = isset($_GET['kategori'])  ? mysqli_real_escape_string($koneksi, $_GET['kategori'])  : '';

$where = [];
if ($provinsi  !== '') $where[] = "provinsi='{$provinsi}'";
if ($kabupaten !== '') $where[] = "kabupaten='{$kabupaten}'";
if ($kategori  !== '') $where[] = "kategori='{$kategori}'";
$whereSql = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';

// Menambahkan ORDER BY untuk mengurutkan berdasarkan waktu upload terbaru
// Asumsikan ada kolom 'created_at' atau 'uploaded_at' atau 'id_event' (auto increment)
// Jika tidak ada kolom timestamp, gunakan id_event DESC karena biasanya auto increment
$sql = $koneksi->query("SELECT * FROM tb_event" . $whereSql . " ORDER BY id_event DESC");

// Jika ada kolom timestamp untuk waktu upload, gunakan ini:
// $sql = $koneksi->query("SELECT * FROM tb_event" . $whereSql . " ORDER BY created_at DESC");
// atau
// $sql = $koneksi->query("SELECT * FROM tb_event" . $whereSql . " ORDER BY uploaded_at DESC");

// Collect all images for slideshow
$slideshowImages = [];
if ($sql && $sql->num_rows) {
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
            $title = $data['nama_event'];
            $category = $data['kategori'];
            $location = $data['alamat'] . ', ' . $data['kabupaten'];
            $dateRange = date('d M Y', strtotime($data['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($data['tanggal_selesai']));
            
            // Check if event has expired - PERBAIKAN LOGIKA TANGGAL
            $today = date('Y-m-d');
            $tanggal_selesai = date('Y-m-d', strtotime($data['tanggal_selesai']));
            $isExpired = $tanggal_selesai < $today;
            
            $slideshowImages[] = [
                'src' => $imgPath,
                'title' => $title,
                'category' => $category,
                'location' => $location,
                'date' => $dateRange,
                'expired' => $isExpired
            ];
        }
    }
    $sql->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Wisata Kalimantan Tengah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        
        body { margin: 0; background: #f6f7fa; }
        .content-header h1 { 
            text-align: center; margin-bottom: 25px; font-size: 2rem;
            color: #8b4513; letter-spacing: 1.2px; font-weight: bold;
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
            border: 2.5px solid rgb(125, 126, 127);
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

        .slide.expired img {
            filter: grayscale(50%) brightness(0.7);
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
            color: #a0522d;
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

        .expired-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
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
        }
        .btn-tambah-data:hover {
            background: linear-gradient(90deg,#a0522d 10%,#8b4513 100%);
            color: #fff;
            box-shadow: 0 4px 18px rgba(160,82,45,0.13);
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
            position: relative;
        }
        .event-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(160,82,45,0.12);
            border: 1.5px solid #8b4513;
        }
        .event-item.expired {
            opacity: 0.8;
            border: 1.5px solid #dc3545;
        }
        .event-item.expired:hover {
            border: 1.5px solid #c82333;
        }
        .event-image {
            position: relative;
        }
        .event-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: filter 0.3s;
        }
        .event-item.expired .event-image img {
            filter: grayscale(30%) brightness(0.8);
        }
        .event-expired-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(220, 53, 69, 0.95);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 5;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
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
        .event-item.expired .event-title {
            color: #6c757d;
        }
        .event-content p {
            margin: 5px 0;
            color: #3e4c4b;
            font-size: 0.97rem;
        }
        .event-item.expired .event-content p {
            color: #6c757d;
        }
        .event-status {
            margin: 10px 0;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }
        .event-status.expired {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .event-status.active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        }
        .event-actions .btn:hover {
            background: linear-gradient(90deg,#a0522d,#8b4513);
        }
        .event-item.expired .event-actions .btn {
            background: linear-gradient(90deg,#6c757d,#495057);
        }
        .event-item.expired .event-actions .btn:hover {
            background: linear-gradient(90deg,#495057,#6c757d);
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

        /* Badge untuk event terbaru */
        .new-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(40, 167, 69, 0.95);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 5;
            box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <section class="content-header">
        <h1>🎉 Event & Festival</h1>
    </section>
    
    <!-- Photo Slideshow -->
    <div class="slideshow-container" id="slideshow">
        <?php if (!empty($slideshowImages)): ?>
            <div class="slide-counter">
                <span id="current-slide">1</span> / <span id="total-slides"><?= count($slideshowImages) ?></span>
            </div>
            
            <?php foreach ($slideshowImages as $index => $image): ?>
                <div class="slide <?= $index === 0 ? 'active' : '' ?> <?= $image['expired'] ? 'expired' : '' ?>" data-slide="<?= $index ?>">
                    <img src="<?= htmlspecialchars($image['src']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                    <?php if ($image['expired']): ?>
                        <div class="expired-badge">
                            <i class="fas fa-clock"></i> Event Berakhir
                        </div>
                    <?php endif; ?>
                    <div class="slide-overlay">
                        <div class="slide-title"><?= htmlspecialchars($image['title']) ?></div>
                        <div class="slide-category">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($image['category']) ?>
                        </div>
                        <div class="slide-location">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($image['location']) ?>
                        </div>
                        <div class="slide-date">
                            <i class="fas fa-calendar"></i> <?= htmlspecialchars($image['date']) ?>
                        </div>
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
            <a href="index.php?page=MyApp/tabel_event" class="btn-tambah-data">
                <i class="fa fa-plus"></i> Tambah Event
            </a>
        </div>
    <?php endif; ?>

    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="page" value="MyApp/data_event">
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
            <option value="">Kategori</option>
            <?php foreach (["Festival","Budaya","Religi","Kuliner","Olahraga"] as $cat): ?>
                <option value="<?= $cat ?>" <?= $kategori===$cat?'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fa fa-search"></i> Filter</button>
    </form>

    <div class="event-grid">
        <?php
        $sql->data_seek(0); // ulang pointer
        if ($sql && $sql->num_rows): 
            $counter = 0;
            while ($data = $sql->fetch_assoc()): 
                $counter++;
                
                // PERBAIKAN LOGIKA TANGGAL EVENT
                $today = date('Y-m-d');
                $tanggal_selesai = date('Y-m-d', strtotime($data['tanggal_selesai']));
                $tanggal_mulai = date('Y-m-d', strtotime($data['tanggal_mulai']));
                
                $isExpired = $tanggal_selesai < $today;
                $isActive = $tanggal_mulai <= $today && $tanggal_selesai >= $today;
                $isUpcoming = $tanggal_mulai > $today;
                
                // Menandai event sebagai "baru" jika dalam 3 posisi pertama (atau sesuai kriteria lain)
                $isNew = $counter <= 3;
            ?>
                <div class="event-item <?= $isExpired ? 'expired' : '' ?>">
                    <div class="event-image">
                        <?php
                        $images = explode(',', $data['gambar']);
                        $img = !empty($images[0]) ? htmlspecialchars($images[0]) : 'placeholder.png';
                        ?>
                        <img src="uploads/<?= $img ?>" alt="<?= htmlspecialchars($data['nama_event']) ?>">
                        
                        <?php if ($isExpired): ?>
                            <div class="event-expired-badge">
                                <i class="fas fa-clock"></i> Berakhir
                            </div>
                        <?php elseif ($isNew): ?>
                            <div class="new-badge">
                                <i class="fas fa-star"></i> Terbaru
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($data['nama_event']) ?></h3>
                        <p><i class="fa fa-calendar" style="color:#2272ff"></i> <?= date('d M Y', strtotime($data['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($data['tanggal_selesai'])) ?></p>
                        <p><i class="fa fa-tag" style="color:#ffa726"></i> <?= htmlspecialchars($data['kategori']) ?></p>
                        <p><i class="fa fa-location-dot" style="color:#2272ff"></i> <?= htmlspecialchars($data['alamat']) ?>, <?= htmlspecialchars($data['kabupaten']) ?></p>
                        
                        <!-- Event Status -->
                        <?php if ($isExpired): ?>
                            <div class="event-status expired">
                                <i class="fas fa-times-circle"></i> Event Sudah Berakhir
                            </div>
                        <?php elseif ($isActive): ?>
                            <div class="event-status active">
                                <i class="fas fa-check-circle"></i> Event Sedang Berlangsung
                            </div>
                        <?php elseif ($isUpcoming): ?>
                            <div class="event-status active">
                                <i class="fas fa-calendar-plus"></i> Event Akan Datang
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-actions">
                            <a href="index.php?page=MyApp/detail_event&id=<?= htmlspecialchars($data['id_event']) ?>" class="btn btn-sm">
                                <i class="fa fa-info-circle"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;width:100%;">Tidak ada event ditemukan.</p>
        <?php endif; ?>
    </div>
</body>
</html>