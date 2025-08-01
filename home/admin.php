<?php
// --- QUERY PHP (diperluas dengan sewa motor dan hotel) ---
$sqlWisata   = $koneksi->query("SELECT COUNT(id_wisata) as jml FROM tb_wisata");
$wisata      = $sqlWisata->fetch_assoc()['jml'];
$sqlKuliner  = $koneksi->query("SELECT COUNT(id_kuliner) as jml FROM tb_kuliner");
$kuliner     = $sqlKuliner->fetch_assoc()['jml'];
$sqlEvent    = $koneksi->query("SELECT COUNT(id_event) as jml FROM tb_event");
$event       = $sqlEvent->fetch_assoc()['jml'];
$sqlOleh2    = $koneksi->query("SELECT COUNT(id_oleh2) as jml FROM tb_oleh2");
$oleh2       = $sqlOleh2->fetch_assoc()['jml'];
$sqlPengguna = $koneksi->query("SELECT COUNT(id_pengguna) as jml FROM tb_pengguna");
$pengguna    = $sqlPengguna->fetch_assoc()['jml'];

// Query untuk sewa motor dan hotel
$sqlMotor = $koneksi->query("SELECT COUNT(id_motor) as jml FROM tb_motor");
$Motor    = $sqlMotor->fetch_assoc()['jml'];
$sqlHotel     = $koneksi->query("SELECT COUNT(id_hotel) as jml FROM tb_hotel");
$hotel        = $sqlHotel->fetch_assoc()['jml'];

function getStatPerBulan($koneksi, $table, $tanggalCol) {
    $arr = array_fill(1, 12, 0);
    $sql = $koneksi->query("SELECT MONTH($tanggalCol) AS bulan, COUNT(*) AS total FROM $table GROUP BY bulan");
    while ($row = $sql->fetch_assoc()) {
        $arr[(int)$row['bulan']] = (int)$row['total'];
    }
    return array_values($arr); // biar urut index 0..11
}
$stat_wisata     = getStatPerBulan($koneksi, 'tb_wisata', 'tanggal_upload');
$stat_kuliner    = getStatPerBulan($koneksi, 'tb_kuliner', 'tanggal_upload');
$stat_event      = getStatPerBulan($koneksi, 'tb_event', 'tanggal_upload');
$stat_oleh2      = getStatPerBulan($koneksi, 'tb_oleh2', 'tanggal_upload');
$stat_motor      = getStatPerBulan($koneksi, 'tb_motor', 'tanggal_upload');
$stat_hotel      = getStatPerBulan($koneksi, 'tb_hotel', 'tanggal_upload');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Statistik SI Wisata</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        
        .content-header {
            padding: 36px 0 0 0;
            text-align: center;
        }
        .content-header h1 {
            color: #156cb2;
            font-weight: 700;
            font-size: 2.2rem;
            letter-spacing: 1.2px;
        }
        .stat-container {
            max-width: 1200px;
            margin: 38px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }
        .stat-card {
            background: rgba(255,255,255,0.35);
            border-radius: 24px;
            box-shadow: 0 8px 28px 0 rgba(48, 119, 209, 0.12);
            padding: 36px 24px 28px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform .20s cubic-bezier(.4,2,.6,1), box-shadow .21s;
            border: 2.5px solid #d6ecff;
            cursor: pointer;
            position: relative;
            backdrop-filter: blur(5px);
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-7px) scale(1.045);
            box-shadow: 0 16px 44px 0 rgba(48, 119, 209, 0.23);
            z-index: 2;
        }
        .stat-icon {
            font-size: 2.6rem;
            margin-bottom: 15px;
        }
        .stat-wisata      { color: #2e85f7; }
        .stat-kuliner     { color: #29bb89; }
        .stat-event       { color: #fbbf24; }
        .stat-oleh2       { color: #e46ba3; }
        .stat-pengguna    { color: #155263; }
        .stat-sewa-motor  { color: #8b5cf6; }
        .stat-hotel       { color: #f59e0b; }
        .stat-title {
            font-size: 1.10rem;
            color: #1953a6;
            font-weight: 600;
            margin-bottom: 7px;
            text-align: center;
        }
        .stat-value {
            font-size: 2.4rem;
            color: #156cb2;
            font-weight: 700;
            margin-top: 0px;
            letter-spacing: 2px;
            text-shadow: 0 2px 12px #cbe5ff88;
        }
        .chart-section {
            max-width: 960px;
            margin: 54px auto 30px auto;
            background: rgba(255,255,255,0.38);
            padding: 32px 18px 38px 18px;
            border-radius: 30px;
            box-shadow: 0 10px 42px 0 rgba(48, 119, 209, 0.12);
            border: 2px solid #d6ecff;
            backdrop-filter: blur(6px);
        }
        .chart-title {
            text-align: center;
            color: #156cb2;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        @media (max-width: 1100px) {
            .stat-container { 
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 22px;
            }
            .chart-section { padding: 14px 4vw 18px 4vw; }
        }
        @media (max-width: 600px) {
            .stat-card { 
                min-width: 140px; 
                padding: 16px 7px;
            }
            .content-header h1 { font-size: 1.08rem; }
            .chart-title { font-size: 1rem; }
            .stat-container {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <section class="content-header">
        <h1>Statistik Sistem Informasi Wisata</h1>
    </section>
    <section class="content">
        <div class="stat-container">
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_wisata'">
                <i class="fa fa-map-marked-alt stat-icon stat-wisata"></i>
                <div class="stat-title">Wisata</div>
                <div class="stat-value"><?= $wisata ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_kuliner'">
                <i class="fa fa-utensils stat-icon stat-kuliner"></i>
                <div class="stat-title">Kuliner</div>
                <div class="stat-value"><?= $kuliner ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_event'">
                <i class="fa fa-calendar-alt stat-icon stat-event"></i>
                <div class="stat-title">Event</div>
                <div class="stat-value"><?= $event ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_oleh2'">
                <i class="fa fa-gift stat-icon stat-oleh2"></i>
                <div class="stat-title">Oleh-oleh</div>
                <div class="stat-value"><?= $oleh2 ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_sewa'">
                <i class="fa fa-motorcycle stat-icon stat-sewa-motor"></i>
                <div class="stat-title">Sewa Motor</div>
                <div class="stat-value"><?= $Motor ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_hotel'">
                <i class="fa fa-bed stat-icon stat-hotel"></i>
                <div class="stat-title">Hotel</div>
                <div class="stat-value"><?= $hotel ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='index.php?page=MyApp/data_pengguna'">
                <i class="fa fa-users stat-icon stat-pengguna"></i>
                <div class="stat-title">Pengguna</div>
                <div class="stat-value"><?= $pengguna ?></div>
            </div>
        </div>
        <div class="chart-section">
            <div class="chart-title">Statistik Penambahan Data per Bulan</div>
            <canvas id="chartData"></canvas>
        </div>
    </section>
    <script>
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const dataWisata    = <?= json_encode($stat_wisata); ?>;
        const dataKuliner   = <?= json_encode($stat_kuliner); ?>;
        const dataEvent     = <?= json_encode($stat_event); ?>;
        const dataOleh2     = <?= json_encode($stat_oleh2); ?>;
        const dataMotor = <?= json_encode($stat_motor); ?>;
        const dataHotel     = <?= json_encode($stat_hotel); ?>;
        
        const ctx = document.getElementById('chartData').getContext('2d');
        const chartData = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Wisata',
                        data: dataWisata,
                        backgroundColor: 'rgba(66,165,245,0.7)',
                        borderColor: '#2e85f7',
                        borderWidth: 2,
                        borderRadius: 7
                    },
                    {
                        label: 'Kuliner',
                        data: dataKuliner,
                        backgroundColor: 'rgba(41,187,137,0.7)',
                        borderColor: '#29bb89',
                        borderWidth: 2,
                        borderRadius: 7
                    },
                    {
                        label: 'Event',
                        data: dataEvent,
                        backgroundColor: 'rgba(251,191,36,0.7)',
                        borderColor: '#fbbf24',
                        borderWidth: 2,
                        borderRadius: 7
                    },
                    {
                        label: 'Oleh-oleh',
                        data: dataOleh2,
                        backgroundColor: 'rgba(228,107,163,0.7)',
                        borderColor: '#e46ba3',
                        borderWidth: 2,
                        borderRadius: 7
                    },
                    {
                        label: 'Sewa Motor',
                        data: dataMotor,
                        backgroundColor: 'rgba(139,92,246,0.7)',
                        borderColor: '#8b5cf6',
                        borderWidth: 2,
                        borderRadius: 7
                    },
                    {
                        label: 'Hotel',
                        data: dataHotel,
                        backgroundColor: 'rgba(245,158,11,0.7)',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderRadius: 7
                    }
                ]
            },
            options: {
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#156cb2', font: { family: 'Poppins', weight:'bold'} },
                        grid: { color: '#e2ecfa' }
                    },
                    x: {
                        ticks: { color: '#156cb2', font: { family: 'Poppins', weight:'bold'} },
                        grid: { color: '#e2ecfa' }
                    }
                }
            }
        });
    </script>
</body>
</html>