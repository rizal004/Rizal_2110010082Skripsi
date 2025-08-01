<?php
include "inc/koneksi.php";
?>
<style>
body {
    background: linear-gradient(120deg, #16a085 0%, #1abc9c 100%) !important;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.content-header {
    background: linear-gradient(90deg, #1abc9c 0%, #16a085 100%);
    padding: 18px 12px 8px 12px;
    border-radius: 0 0 20px 20px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px rgba(22,160,133,0.08);
    color: #fff;
}
.content-header h1 { color: #fff !important; text-shadow:1px 2px 8px rgba(22,160,133,0.3); }
.breadcrumb {
    background: rgba(255,255,255,0.33);
    border-radius: 10px;
    padding: 8px 18px;
}
.box.box-primary, .box.box-info {
    border-top: 4px solid #1abc9c !important;
    border-radius: 12px;
    box-shadow: 0 4px 20px 0 rgba(22,160,133,0.06);
    background: rgba(255,255,255,0.97);
}
.box-header.with-border {
    background: #a7f3d0;
    border-radius: 12px 12px 0 0;
    color: #065f46;
}
.btn-info, .btn-primary, .btn-success {
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(22,160,133,0.10);
    letter-spacing: 1px;
    transition: background 0.18s;
}
.btn-info { background: linear-gradient(90deg, #1dd1a1 0%, #10ac84 100%); border: none; color: #fff;}
.btn-primary { background: linear-gradient(90deg, #1abc9c 0%, #16a085 100%); border: none; color: #fff;}
.btn-success { background: linear-gradient(90deg, #00b894 0%, #00cec9 100%); border: none; color: #fff;}
.btn-info:hover, .btn-primary:hover, .btn-success:hover { filter: brightness(1.12); }
.table-responsive { border-radius: 16px; box-shadow: 0 4px 18px rgba(22,160,133,0.09); background: #fff;}
.table-bordered>thead>tr { background: linear-gradient(90deg, #1abc9c 10%, #16a085 100%); color: #fff; font-size: 15px;}
.table-bordered>tbody>tr:nth-child(even) { background: #f0fdfa;}
.table-bordered>tbody>tr:nth-child(odd) { background: #fff;}
.table>tbody>tr>td, .table>thead>tr>th { vertical-align: middle;}
.chart-container {
    position: relative;
    height: 400px;
    margin: 20px 0;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(22,160,133,0.09);
}
@media (max-width: 600px) {
    .content-header h1 { font-size: 1.4rem;}
    .box.box-primary, .box.box-info { border-radius: 7px; }
    .table-responsive { border-radius: 7px;}
    .chart-container { height: 300px; }
}
</style>

<!-- Tambahkan Chart.js dan Plugin DataLabels -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>

<section class="content-header">
    <h1 style="text-align:center; color:#fff !important;">Cetak Data Kritik Wisata</h1>
    <ol class="breadcrumb">
        <li>
            <a href="index.php" style="color:black;">
                <i class="fa fa-home"></i> <b>Si Wisata</b>
            </a>
        </li>
        <li>
            <a href="?page=MyApp/cetak_kritik_wisata" style="color:black;">Kritik Wisata</a>
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
                        <option value="sewa">Kritik dan Saran Sewa</option>
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
            <input type="hidden" name="page" value="MyApp/cetak_kritik_wisata">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Kategori Wisata</label>
                            <select name="kategori_wisata" class="form-control">
                                <option value="">-- Semua Kategori --</option>
                                <?php
                                $kategori_query = $koneksi->query("SELECT DISTINCT kategori FROM tb_wisata ORDER BY kategori");
                                while($kategori_data = $kategori_query->fetch_assoc()) {
                                    $selected = (isset($_GET['kategori_wisata']) && $_GET['kategori_wisata'] == $kategori_data['kategori']) ? 'selected' : '';
                                    echo "<option value='{$kategori_data['kategori']}' $selected>{$kategori_data['kategori']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">-- Pilih Tahun --</option>
                                <?php
                                $tahun_query = $koneksi->query("SELECT DISTINCT YEAR(tanggal) as tahun FROM tb_kritik_saran ORDER BY tahun DESC");
                                while($tahun_data = $tahun_query->fetch_assoc()) {
                                    $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $tahun_data['tahun']) ? 'selected' : '';
                                    echo "<option value='{$tahun_data['tahun']}' $selected>{$tahun_data['tahun']}</option>";
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
        <div class="box-header with-border" style="background:linear-gradient(90deg, #1abc9c 0%, #16a085 100%);color:#fff;">
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
                    <h2 style="color:#222;"><b>LAPORAN DATA KRITIK WISATA</b></h2>
                    <h4 style="color:#1abc9c;">Si Wisata</h4>
                    <?php
                    $bulan_array = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    if(isset($_GET['cetak_semua'])) {
                        echo "<p><b>Semua Data Kritik Wisata</b></p>";
                    } else {
                        $filter_text = [];
                        if(!empty($_GET['kategori_wisata'])) $filter_text[] = "Kategori: ".$_GET['kategori_wisata'];
                        if(!empty($_GET['tahun'])) $filter_text[] = "Tahun: ".$_GET['tahun'];
                        if(!empty($_GET['bulan'])) $filter_text[] = "Bulan: ".$bulan_array[$_GET['bulan']];
                        if(!empty($_GET['rating'])) $filter_text[] = "Rating: ".$_GET['rating']." Bintang";
                        echo "<p><b>" . implode(" | ", $filter_text) . "</b></p>";
                    }
                    ?>
                    <hr>
                </div>

                <!-- GRAFIK SECTION -->
                <div class="row chart-section" style="margin-bottom: 30px;">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h4 style="text-align: center; color: #1abc9c; margin-bottom: 20px;">Distribusi Rating</h4>
                            <canvas id="ratingChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h4 style="text-align: center; color: #1abc9c; margin-bottom: 20px;">Kritik per Kategori Wisata</h4>
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th style="text-align:center;">No</th>
                                <th>Nama Wisata</th>
                                <th>Kategori Wisata</th>
                                <th>Nama Pengguna</th>
                                <th>Rating</th>
                                <th>Komentar</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $where_conditions = [];
                            if(isset($_GET['filter'])) {
                                if(!empty($_GET['kategori_wisata'])) {
                                    $where_conditions[] = "w.kategori = '".$_GET['kategori_wisata']."'";
                                }
                                if(!empty($_GET['tahun'])) {
                                    $where_conditions[] = "YEAR(ks.tanggal) = '".$_GET['tahun']."'";
                                }
                                if(!empty($_GET['bulan'])) {
                                    $where_conditions[] = "MONTH(ks.tanggal) = '".$_GET['bulan']."'";
                                }
                                if(!empty($_GET['rating'])) {
                                    $where_conditions[] = "ks.rating = '".$_GET['rating']."'";
                                }
                            }
                            $where_clause = '';
                            if(!empty($where_conditions)) {
                                $where_clause = "WHERE " . implode(' AND ', $where_conditions);
                            }
                            
                            $sql = $koneksi->query("SELECT ks.*, 
                                                          w.nama_wisata,
                                                          w.kategori as kategori_wisata,
                                                          p.nama_pengguna 
                                                   FROM tb_kritik_saran ks 
                                                   LEFT JOIN tb_wisata w ON ks.id_wisata = w.id_wisata 
                                                   LEFT JOIN tb_pengguna p ON ks.id_pengguna = p.id_pengguna 
                                                   $where_clause 
                                                   ORDER BY ks.tanggal DESC");
                            
                            if($sql->num_rows > 0) {
                                while ($data = $sql->fetch_assoc()) {
                            ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nama_wisata'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['kategori_wisata'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['nama_pengguna'] ?? '-'); ?></td>
                                <td style="text-align: center;">
                                    <?php
                                    $rating = (int)$data['rating'];
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
                                    $komentar = htmlspecialchars($data['komentar']);
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
                    <h4 style="color:#1abc9c;"><b>Ringkasan Statistik</b></h4>
                    <div class="row">
                        <?php
                        // Get statistics
                        $stats_query = $koneksi->query("SELECT 
                                                          COUNT(*) as total_kritik,
                                                          AVG(rating) as avg_rating,
                                                          COUNT(CASE WHEN rating = 5 THEN 1 END) as rating_5,
                                                          COUNT(CASE WHEN rating = 4 THEN 1 END) as rating_4,
                                                          COUNT(CASE WHEN rating = 3 THEN 1 END) as rating_3,
                                                          COUNT(CASE WHEN rating = 2 THEN 1 END) as rating_2,
                                                          COUNT(CASE WHEN rating = 1 THEN 1 END) as rating_1
                                                        FROM tb_kritik_saran ks 
                                                        LEFT JOIN tb_wisata w ON ks.id_wisata = w.id_wisata 
                                                        $where_clause");
                        $stats = $stats_query->fetch_assoc();

                        // Get category data for chart
                        $category_query = $koneksi->query("SELECT 
                                                            w.kategori,
                                                            COUNT(*) as jumlah_kritik
                                                          FROM tb_kritik_saran ks 
                                                          LEFT JOIN tb_wisata w ON ks.id_wisata = w.id_wisata 
                                                          $where_clause
                                                          GROUP BY w.kategori
                                                          ORDER BY jumlah_kritik DESC");
                        $category_data = [];
                        $category_labels = [];
                        while($cat_data = $category_query->fetch_assoc()) {
                            $category_labels[] = $cat_data['kategori'] ?? 'Tidak Diketahui';
                            $category_data[] = $cat_data['jumlah_kritik'];
                        }
                        ?>
                        <div class="col-md-6">
                            <table class="table table-bordered" style="font-size: 11px;">
                                <tr>
                                    <td><b>Total Kritik/Saran</b></td>
                                    <td><?= $stats['total_kritik']; ?> kritik</td>
                                </tr>
                                <tr>
                                    <td><b>Rating Rata-rata</b></td>
                                    <td><?= number_format($stats['avg_rating'], 2); ?> / 5.00</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered" style="font-size: 11px;">
                                <tr><td><b>Rating 5 Bintang</b></td><td><?= $stats['rating_5']; ?> kritik</td></tr>
                                <tr><td><b>Rating 4 Bintang</b></td><td><?= $stats['rating_4']; ?> kritik</td></tr>
                                <tr><td><b>Rating 3 Bintang</b></td><td><?= $stats['rating_3']; ?> kritik</td></tr>
                                <tr><td><b>Rating 2 Bintang</b></td><td><?= $stats['rating_2']; ?> kritik</td></tr>
                                <tr><td><b>Rating 1 Bintang</b></td><td><?= $stats['rating_1']; ?> kritik</td></tr>
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
    // Register the datalabels plugin
    Chart.register(ChartDataLabels);
    
    // Rating Distribution Chart dengan Persentase
    const ratingCtx = document.getElementById('ratingChart').getContext('2d');
    const ratingData = [<?= $stats['rating_5']; ?>, <?= $stats['rating_4']; ?>, <?= $stats['rating_3']; ?>, <?= $stats['rating_2']; ?>, <?= $stats['rating_1']; ?>];
    const totalRating = ratingData.reduce((a, b) => a + b, 0);
    
    const ratingChart = new Chart(ratingCtx, {
        type: 'doughnut',
        data: {
            labels: ['5 Bintang', '4 Bintang', '3 Bintang', '2 Bintang', '1 Bintang'],
            datasets: [{
                data: ratingData,
                backgroundColor: [
                    '#1abc9c',
                    '#16a085',
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
                                        text: `${label}: ${value} (${percentage}%)`,
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
                            return `${label}: ${value} kritik (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    display: function(context) {
                        const value = context.dataset.data[context.dataIndex];
                        return value > 0; // Hanya tampilkan label jika nilai > 0
                    },
                    formatter: function(value, context) {
                        const percentage = totalRating > 0 ? ((value / totalRating) * 100).toFixed(1) : 0;
                        return `${percentage}%`;
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 11
                    }
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($category_labels); ?>,
            datasets: [{
                label: 'Jumlah Kritik',
                data: <?php echo json_encode($category_data); ?>,
                backgroundColor: [
                    '#1abc9c',
                    '#16a085',
                    '#f39c12',
                    '#e67e22',
                    '#9b59b6'
                ],
                borderColor: [
                    '#1abc9c',
                    '#16a085',
                    '#f39c12',
                    '#e67e22',
                    '#9b59b6'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                datalabels: {
                    display: function(context) {
                        const value = context.dataset.data[context.dataIndex];
                        return value > 0;
                    },
                    formatter: function(value, context) {
                        return value;
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    anchor: 'end',
                    align: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>

<style media="print">
    @page {
        size: A4 landscape;
        margin: 1cm;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.3;
        background: #fff !important;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
    }
    th {
        background-color: #f0f0f0 !important;
        font-weight: bold;
    }
    .text-center { text-align: center; }
    hr { border: 1px solid #000; }
    .btn, .box-header, .breadcrumb, .box-title { display: none !important; }
    .box, .box-info, .box.box-primary { box-shadow: none !important; border-radius:0 !important; }
    .row { display: block; }
    .col-md-6 { width: 48%; float: left; margin-right: 2%; }
    .chart-section { display: none !important; } /* Hide charts when printing */
</style>