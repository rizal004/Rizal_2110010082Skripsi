<?php
include "inc/koneksi.php";
?>
<style>
body {
    background: linear-gradient(120deg, #2980b9 0%, #1f618d 100%) !important;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.content-header {
    background: linear-gradient(90deg, #2980b9 0%, #1f618d 100%);
    padding: 18px 12px 8px 12px;
    border-radius: 0 0 20px 20px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px rgba(41,128,185,0.08);
    color: #fff;
}
.content-header h1 { color: #fff !important; text-shadow:1px 2px 8px rgba(41,128,185,0.3); }
.breadcrumb {
    background: rgba(255,255,255,0.33);
    border-radius: 10px;
    padding: 8px 18px;
}
.box.box-primary, .box.box-info {
    border-top: 4px solid #2980b9 !important;
    border-radius: 12px;
    box-shadow: 0 4px 20px 0 rgba(41,128,185,0.06);
    background: rgba(255,255,255,0.97);
}
.box-header.with-border {
    background: #d6eaf8;
    border-radius: 12px 12px 0 0;
    color: #1b4f72;
}
.btn-info, .btn-primary, .btn-success {
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(41,128,185,0.10);
    letter-spacing: 1px;
    transition: background 0.18s;
}
.btn-info { background: linear-gradient(90deg, #5dade2 0%, #3498db 100%); border: none; color: #fff;}
.btn-primary { background: linear-gradient(90deg, #2980b9 0%, #1f618d 100%); border: none; color: #fff;}
.btn-success { background: linear-gradient(90deg, #16a085 0%, #138d75 100%); border: none; color: #fff;}
.btn-info:hover, .btn-primary:hover, .btn-success:hover { filter: brightness(1.12); }
.table-responsive { border-radius: 16px; box-shadow: 0 4px 18px rgba(41,128,185,0.09); background: #fff;}
.table-bordered>thead>tr { background: linear-gradient(90deg, #2980b9 10%, #1f618d 100%); color: #fff; font-size: 15px;}
.table-bordered>tbody>tr:nth-child(even) { background: #f8fafc;}
.table-bordered>tbody>tr:nth-child(odd) { background: #fff;}
.table>tbody>tr>td, .table>thead>tr>th { vertical-align: middle;}
.chart-container {
    position: relative;
    height: 400px;
    margin: 20px 0;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(41,128,185,0.09);
}
@media (max-width: 600px) {
    .content-header h1 { font-size: 1.4rem;}
    .box.box-primary, .box.box-info { border-radius: 7px; }
    .table-responsive { border-radius: 7px;}
    .chart-container { height: 300px; }
}
</style>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>

<section class="content-header">
    <h1 style="text-align:center; color:#fff !important;">Cetak Data Kritik Sewa</h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php" style="color:black;">
                <i class="fa fa-home"></i> <b>Si Wisata</b>
            </a>
        </li>
        <li>
            <a href="?page=MyApp/cetak_kritik_sewa" style="color:black;">Kritik Sewa</a>
        </li>
        <li class="active" style="color:black;">Cetak Data</li>
    </ol>
</section>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Pilih Kategori Kritik dan Saran</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Kategori Kritik dan Saran</label>
                    <select name="kategori" class="form-control" onchange="redirectToCategory(this.value)">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="wisata">Kritik dan Saran Wisata</option>
                        <option value="kuliner">Kritik dan Saran Kuliner</option>
                        <option value="oleh2">Kritik dan Saran Oleh-oleh</option>
                        <option value="event">Kritik dan Saran Event</option>
                        <option value="hotel">Kritik dan Saran Hotel</option>
                        <option value="sewa" selected>Kritik dan Saran Sewa</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Filter Data Cetak</h3>
        </div>
        <form method="GET" action="">
            <input type="hidden" name="page" value="MyApp/cetak_kritik_sewa">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $tahun_query = $koneksi->query("SELECT DISTINCT YEAR(tanggal) as tahun FROM tb_feedback_sewa ORDER BY tahun DESC");
                                if ($tahun_query) {
                                    while($tahun_data = $tahun_query->fetch_assoc()) {
                                        $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $tahun_data['tahun']) ? 'selected' : '';
                                        echo "<option value='{$tahun_data['tahun']}' $selected>{$tahun_data['tahun']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                <option value="">-- Pilih Bulan --</option>
                                <?php
                                $bulan_array = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                foreach($bulan_array as $key => $bulan) {
                                    $selected = (isset($_GET['bulan']) && $_GET['bulan'] == $key) ? 'selected' : '';
                                    echo "<option value='$key' $selected>$bulan</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Rating</label>
                            <select name="rating" class="form-control">
                                <option value="">-- Pilih Rating --</option>
                                <option value="1" <?= (isset($_GET['rating']) && $_GET['rating'] == '1') ? 'selected' : ''; ?>>⭐ (1)</option>
                                <option value="2" <?= (isset($_GET['rating']) && $_GET['rating'] == '2') ? 'selected' : ''; ?>>⭐⭐ (2)</option>
                                <option value="3" <?= (isset($_GET['rating']) && $_GET['rating'] == '3') ? 'selected' : ''; ?>>⭐⭐⭐ (3)</option>
                                <option value="4" <?= (isset($_GET['rating']) && $_GET['rating'] == '4') ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4)</option>
                                <option value="5" <?= (isset($_GET['rating']) && $_GET['rating'] == '5') ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jenis Kendaraan</label>
                            <select name="jenis_kendaraan" class="form-control">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Motor" <?= (isset($_GET['jenis_kendaraan']) && $_GET['jenis_kendaraan'] == 'Motor') ? 'selected' : ''; ?>>Motor</option>
                                <option value="Mobil" <?= (isset($_GET['jenis_kendaraan']) && $_GET['jenis_kendaraan'] == 'Mobil') ? 'selected' : ''; ?>>Mobil</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button type="submit" name="filter" class="btn btn-info">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <button type="submit" name="cetak_semua" class="btn btn-success">
                                <i class="fa fa-print"></i> Cetak Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if(isset($_GET['filter']) || isset($_GET['cetak_semua'])): ?>
    <div class="box box-info">
        <div class="box-header with-border" style="background:linear-gradient(90deg, #2980b9 0%, #1f618d 100%);color:#fff;">
            <h3 class="box-title">Hasil Filter</h3>
            <div class="box-tools pull-right">
                <button onclick="printData()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Cetak
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="printArea">
                <div class="text-center" style="margin-bottom: 20px;">
                    <h2 style="color:#222;"><b>LAPORAN DATA KRITIK SEWA</b></h2>
                    <h4 style="color:#2980b9;">Si Wisata</h4>
                    <?php
                    $bulan_array = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    if(isset($_GET['cetak_semua'])) {
                        echo "<p><b>Semua Data Kritik Sewa</b></p>";
                    } else {
                        $filter_text = [];
                        if(!empty($_GET['tahun'])) $filter_text[] = "Tahun: ".$_GET['tahun'];
                        if(!empty($_GET['bulan'])) $filter_text[] = "Bulan: ".$bulan_array[$_GET['bulan']];
                        if(!empty($_GET['rating'])) $filter_text[] = "Rating: ".$_GET['rating']." Bintang";
                        if(!empty($_GET['jenis_kendaraan'])) $filter_text[] = "Jenis Kendaraan: ".$_GET['jenis_kendaraan'];
                        echo "<p><b>" . implode(" | ", $filter_text) . "</b></p>";
                    }
                    ?>
                    <hr>
                </div>

                <?php
                // Build WHERE conditions
                $where_conditions = [];
                if(isset($_GET['filter'])) {
                    if(!empty($_GET['tahun'])) {
                        $tahun = mysqli_real_escape_string($koneksi, $_GET['tahun']);
                        $where_conditions[] = "YEAR(fs.tanggal) = '$tahun'";
                    }
                    if(!empty($_GET['bulan'])) {
                        $bulan = mysqli_real_escape_string($koneksi, $_GET['bulan']);
                        $where_conditions[] = "MONTH(fs.tanggal) = '$bulan'";
                    }
                    if(!empty($_GET['rating'])) {
                        $rating = mysqli_real_escape_string($koneksi, $_GET['rating']);
                        $where_conditions[] = "fs.rating = '$rating'";
                    }
                    if(!empty($_GET['jenis_kendaraan'])) {
                        $jenis_kendaraan = mysqli_real_escape_string($koneksi, $_GET['jenis_kendaraan']);
                        $where_conditions[] = "m.jenis_kendaraan = '$jenis_kendaraan'";
                    }
                }
                
                $where_clause = '';
                if(!empty($where_conditions)) {
                    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
                }

                // Get statistics
                $stats_sql = "SELECT 
                               COUNT(*) as total_feedback,
                               AVG(fs.rating) as avg_rating,
                               COUNT(CASE WHEN fs.rating = 5 THEN 1 END) as rating_5,
                               COUNT(CASE WHEN fs.rating = 4 THEN 1 END) as rating_4,
                               COUNT(CASE WHEN fs.rating = 3 THEN 1 END) as rating_3,
                               COUNT(CASE WHEN fs.rating = 2 THEN 1 END) as rating_2,
                               COUNT(CASE WHEN fs.rating = 1 THEN 1 END) as rating_1
                             FROM tb_feedback_sewa fs 
                             LEFT JOIN tb_motor m ON fs.id_motor = m.id_motor
                             $where_clause";
                
                $stats_query = $koneksi->query($stats_sql);
                
                if (!$stats_query) {
                    echo "<div class='alert alert-danger'>Error in statistics query: " . $koneksi->error . "</div>";
                    $stats = [
                        'total_feedback' => 0,
                        'avg_rating' => 0,
                        'rating_5' => 0,
                        'rating_4' => 0,
                        'rating_3' => 0,
                        'rating_2' => 0,
                        'rating_1' => 0
                    ];
                } else {
                    $stats = $stats_query->fetch_assoc();
                }

                // Get monthly data for chart
                $monthly_sql = "SELECT 
                                 MONTH(fs.tanggal) as bulan,
                                 COUNT(*) as jumlah_feedback
                               FROM tb_feedback_sewa fs 
                               LEFT JOIN tb_motor m ON fs.id_motor = m.id_motor
                               $where_clause
                               GROUP BY MONTH(fs.tanggal)
                               ORDER BY MONTH(fs.tanggal)";
                
                $monthly_query = $koneksi->query($monthly_sql);
                $monthly_data = [];
                
                if ($monthly_query) {
                    while($month_data = $monthly_query->fetch_assoc()) {
                        $monthly_data[$month_data['bulan']] = $month_data['jumlah_feedback'];
                    }
                }
                ?>

                <!-- GRAFIK SECTION -->
                <div class="row chart-section" style="margin-bottom: 30px;">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h4 style="text-align: center; color: #2980b9; margin-bottom: 20px;">Distribusi Rating</h4>
                            <canvas id="ratingChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h4 style="text-align: center; color: #2980b9; margin-bottom: 20px;">Tren Feedback per Bulan</h4>
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th style="text-align:center;">No</th>
                                <th>Nama Kendaraan</th>
                                <th>Jenis Kendaraan</th>
                                <th>Nama Pengguna</th>
                                <th>Rating</th>
                                <th>Komentar</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            
                            // Main query to get feedback data
                            $main_sql = "SELECT fs.*, 
                                               m.nama_motor,
                                               m.jenis_kendaraan,
                                               p.nama_pengguna 
                                        FROM tb_feedback_sewa fs 
                                        LEFT JOIN tb_motor m ON fs.id_motor = m.id_motor 
                                        LEFT JOIN tb_pengguna p ON fs.id_pengguna = p.id_pengguna 
                                        $where_clause 
                                        ORDER BY fs.tanggal DESC";
                            
                            $sql = $koneksi->query($main_sql);
                            
                            if (!$sql) {
                                echo "<tr><td colspan='7' class='text-center text-danger'>Error in main query: " . $koneksi->error . "</td></tr>";
                            } elseif ($sql->num_rows > 0) {
                                while ($data = $sql->fetch_assoc()) {
                                    $nama_motor_full = $data['nama_motor'] ?? 'Kendaraan Tidak Diketahui';
                                    $jenis_kendaraan = $data['jenis_kendaraan'] ?? 'Tidak Diketahui';
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($nama_motor_full); ?></td>
                                <td><?= htmlspecialchars($jenis_kendaraan); ?></td>
                                <td><?= htmlspecialchars($data['nama_pengguna'] ?? 'Pengguna Tidak Diketahui'); ?></td>
                                <td style="text-align: center;">
                                    <?php
                                    $rating = (int)($data['rating'] ?? 0);
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $rating) {
                                            echo "⭐";
                                        } else {
                                            echo "☆";
                                        }
                                    }
                                    echo " ($rating)";
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $komentar = htmlspecialchars($data['komentar'] ?? '');
                                    echo strlen($komentar) > 150 ? substr($komentar, 0, 150) . '...' : $komentar;
                                    ?>
                                </td>
                                <td><?= date('d-m-Y H:i', strtotime($data['tanggal'])); ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Tidak ada data yang ditemukan</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Statistics -->
                <div style="margin-top: 20px;">
                    <h4 style="color:#2980b9;"><b>Ringkasan Statistik</b></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered" style="font-size: 11px;">
                                <tr>
                                    <td><b>Total Feedback</b></td>
                                    <td><?= $stats['total_feedback']; ?> feedback</td>
                                </tr>
                                <tr>
                                    <td><b>Rating Rata-rata</b></td>
                                    <td><?= number_format($stats['avg_rating'], 2); ?> / 5.00</td>
                                </tr>
                                <tr>
                                    <td><b>Rating 5 Bintang</b></td>
                                    <td><?= $stats['rating_5']; ?> feedback</td>
                                </tr>
                                <tr>
                                    <td><b>Rating 4 Bintang</b></td>
                                    <td><?= $stats['rating_4']; ?> feedback</td>
                                </tr>
                                <tr>
                                    <td><b>Rating 3 Bintang</b></td>
                                    <td><?= $stats['rating_3']; ?> feedback</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered" style="font-size: 11px;">
                                <tr>
                                    <td><b>Rating 2 Bintang</b></td>
                                    <td><?= $stats['rating_2']; ?> feedback</td>
                                </tr>
                                <tr>
                                    <td><b>Rating 1 Bintang</b></td>
                                    <td><?= $stats['rating_1']; ?> feedback</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
function printData() {
    // Hide charts before printing
    document.querySelector('.chart-section').style.display = 'none';
    
    var printContents = document.getElementById('printArea').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}

function redirectToCategory(kategori) {
    if(kategori) {
        var pages = {
            'wisata': '?page=MyApp/cetak_kritik_wisata',
            'kuliner': '?page=MyApp/cetak_kritik_kuliner', 
            'oleh2': '?page=MyApp/cetak_kritik_oleh2',
            'event': '?page=MyApp/cetak_kritik_event',
            'hotel': '?page=MyApp/cetak_kritik_hotel',
            'sewa': '?page=MyApp/cetak_kritik_sewa'
        };
        
        if(pages[kategori]) {
            window.location.href = pages[kategori];
        }
    }
}

// Initialize Charts
<?php if(isset($_GET['filter']) || isset($_GET['cetak_semua'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded');
        return;
    }

    // Register the datalabels plugin if available
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }
    
    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingChart');
    if (ratingCtx) {
        const ratingData = [<?= $stats['rating_5']; ?>, <?= $stats['rating_4']; ?>, <?= $stats['rating_3']; ?>, <?= $stats['rating_2']; ?>, <?= $stats['rating_1']; ?>];
        const totalRating = ratingData.reduce((a, b) => a + b, 0);
        
        new Chart(ratingCtx, {
            type: 'doughnut',
            data: {
                labels: ['5 Bintang', '4 Bintang', '3 Bintang', '2 Bintang', '1 Bintang'],
                datasets: [{
                    data: ratingData,
                    backgroundColor: [
                        '#2980b9',
                        '#3498db',
                        '#f39c12',
                        '#e67e22',
                        '#e74c3c'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function(label, i) {
                                        const value = data.datasets[0].data[i];
                                        const percentage = totalRating > 0 ? ((value / totalRating) * 100).toFixed(1) : 0;
                                        return {
                                            text: label + ': ' + value + ' (' + percentage + '%)',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor,
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const percentage = totalRating > 0 ? ((value / totalRating) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' feedback (' + percentage + '%)';
                            }
                        }
                    }
                }
            },
            plugins: typeof ChartDataLabels !== 'undefined' ? [{
                id: 'datalabels',
                afterDatasetsDraw: function(chart) {
                    const ctx = chart.ctx;
                    chart.data.datasets.forEach((dataset, i) => {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((element, index) => {
                            const value = dataset.data[index];
                            if (value > 0) {
                                const percentage = totalRating > 0 ? ((value / totalRating) * 100).toFixed(1) : 0;
                                const position = element.tooltipPosition();
                                ctx.fillStyle = '#fff';
                                ctx.font = 'bold 11px Arial';
                                ctx.textAlign = 'center';
                                ctx.fillText(percentage + '%', position.x, position.y);
                            }
                        });
                    });
                }
            }] : []
        });
    }

    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Feedback',
                    data: [
                        <?php 
                        for($i = 1; $i <= 12; $i++) {
                            echo (isset($monthly_data[$i]) ? $monthly_data[$i] : 0);
                            if($i < 12) echo ',';
                        }
                        ?>
                    ],
                    borderColor: '#2980b9',
                    backgroundColor: 'rgba(41, 128, 185, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,tension: 0.4,
                    pointBackgroundColor: '#2980b9',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + ' feedback';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                }
            }
        });
    }
});
<?php endif; ?>
</script>