<?php
// inc/koneksi.php
include "inc/koneksi.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$ses_id    = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;
$ses_level = isset($_SESSION['ses_level']) ? strtolower($_SESSION['ses_level']) : '';

// Ambil ID transaksi dari parameter URL
$id_transaksi = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($id_transaksi)) {
    echo "<script>alert('ID Transaksi tidak valid!'); window.location='?page=MyApp/data_transaksi_sewa';</script>";
    exit;
}

// Query untuk mengambil detail transaksi
$sql_query = "SELECT t.*, m.*, p.nama_pengguna AS nama_penyewa, p.no_hp, p.alamat, p.email
              FROM tb_transaksi_sewa t
              LEFT JOIN tb_motor m ON t.id_motor = m.id_motor
              LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
              WHERE t.id_transaksi = '" . mysqli_real_escape_string($koneksi, $id_transaksi) . "'";

$result = $koneksi->query($sql_query);

if (!$result) {
    echo "<script>alert('Error: " . addslashes($koneksi->error) . "'); window.location='?page=MyApp/data_transaksi_sewa';</script>";
    exit;
}

if ($result->num_rows === 0) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='?page=MyApp/data_transaksi_sewa';</script>";
    exit;
}

$data = $result->fetch_assoc();

// Cek hak akses
if ($ses_level === 'pengguna' && intval($data['id_pengguna']) !== $ses_id) {
    echo "<script>alert('Anda tidak memiliki akses untuk melihat transaksi ini!'); window.location='?page=MyApp/data_transaksi_sewa';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Transaksi Sewa</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }
        .content-header h1 {
            color: black;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-weight: 700;
        }
        .breadcrumb {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .breadcrumb a {
            color: #fff;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            color: #f8f9fa;
        }
        .box {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 1000px;
            overflow: hidden;
        }
        .box-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            border-bottom: none;
        }
        .box-body {
            padding: 30px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        .detail-card {
            background: #f8f9ff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .detail-card h4 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #333;
            flex: 0 0 35%;
        }
        .detail-value {
            flex: 1;
            text-align: right;
            color: #666;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        .vehicle-image {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .bukti-pembayaran {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #28a745;
        }
        .text-warning {
            color: #ffc107;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-info {
            color: #17a2b8;
        }
        .text-secondary {
            color: #6c757d;
        }
        @media (max-width: 768px) {
            .box {
                margin: 10px;
            }
            .box-body {
                padding: 15px;
            }
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .detail-label {
                flex: none;
                margin-bottom: 5px;
            }
            .detail-value {
                flex: none;
                text-align: left;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <section class="content-header">
            <h1><i class="fa fa-eye"></i> Detail Transaksi Sewa</h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-home"></i> <b>Sistem Sewa</b></a></li>
                <li><a href="?page=MyApp/data_transaksi_sewa">Transaksi Sewa</a></li>
                <li class="active">Detail</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Detail Transaksi: <?= htmlspecialchars($data['id_transaksi']) ?></h3>
                    <div class="box-tools pull-right">
                        <a href="?page=MyApp/data_transaksi_sewa" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <!-- Informasi Transaksi -->
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h4><i class="fa fa-file-text"></i> Informasi Transaksi</h4>
                                <div class="detail-row">
                                    <span class="detail-label">ID Transaksi:</span>
                                    <span class="detail-value"><strong><?= htmlspecialchars($data['id_transaksi']) ?></strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tanggal Transaksi:</span>
                                    <span class="detail-value"><?= date('d-m-Y H:i', strtotime($data['tanggal'])) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <?php
                                        if ($data['status'] === 'pending') {
                                            echo '<span class="status-badge" style="background:#ffc107;color:#212529;"><i class="fa fa-clock"></i> Pending</span>';
                                        } elseif ($data['status'] === 'approved') {
                                            echo '<span class="status-badge" style="background:#28a745;color:#fff;"><i class="fa fa-check"></i> Approved</span>';
                                        } elseif ($data['status'] === 'cancelled') {
                                            echo '<span class="status-badge" style="background:#dc3545;color:#fff;"><i class="fa fa-times"></i> Dibatalkan</span>';
                                        } elseif ($data['status'] === 'selesai') {
                                            echo '<span class="status-badge" style="background:#6c757d;color:#fff;"><i class="fa fa-flag-checkered"></i> Selesai</span>';
                                        } else {
                                            echo '<span class="status-badge" style="background:#17a2b8;color:#fff;">' . htmlspecialchars($data['status']) . '</span>';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tanggal Mulai:</span>
                                    <span class="detail-value text-success"><strong><?= date('d-m-Y', strtotime($data['tanggal_mulai'])) ?></strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tanggal Selesai:</span>
                                    <span class="detail-value text-danger"><strong><?= date('d-m-Y', strtotime($data['tanggal_selesai'])) ?></strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Lama Sewa:</span>
                                    <span class="detail-value"><span class="status-badge" style="background:#007bff;color:#fff;"><?= htmlspecialchars($data['lama_sewa']) ?> hari</span></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Total Bayar:</span>
                                    <span class="detail-value text-success"><strong>Rp <?= number_format($data['total_bayar'],0,',','.') ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Penyewa -->
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h4><i class="fa fa-user"></i> Informasi Penyewa</h4>
                                <div class="detail-row">
                                    <span class="detail-label">Nama Penyewa:</span>
                                    <span class="detail-value"><strong><?= htmlspecialchars($data['nama_penyewa']) ?></strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value"><?= htmlspecialchars($data['email'] ?? '-') ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">No. HP:</span>
                                    <span class="detail-value"><?= htmlspecialchars($data['no_hp'] ?? '-') ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Alamat:</span>
                                    <span class="detail-value"><?= htmlspecialchars($data['alamat'] ?? '-') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Informasi Kendaraan -->
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h4><i class="fa fa-motorcycle"></i> Informasi Kendaraan</h4>
                                <?php if (!empty($data['gambar']) && file_exists('foto/' . $data['gambar'])): ?>
                                    <div class="text-center" style="margin-bottom: 15px;">
                                        <img src="foto/<?= htmlspecialchars($data['gambar']) ?>" alt="<?= htmlspecialchars($data['nama_motor']) ?>" class="vehicle-image">
                                    </div>
                                <?php endif; ?>
                                <div class="detail-row">
                                    <span class="detail-label">Nama Kendaraan:</span>
                                    <span class="detail-value"><strong><?= htmlspecialchars($data['nama_motor']) ?></strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Merk:</span>
                                    <span class="detail-value"><?= htmlspecialchars($data['merk']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Jenis Kendaraan:</span>
                                    <span class="detail-value"><?= htmlspecialchars($data['jenis_kendaraan']) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tahun:</span>
                                    <span class="detail-value"><?= htmlspecialchars($data['tahun'] ?? '-') ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Plat Nomor:</span>
                                    <span class="detail-value"><strong><?= htmlspecialchars($data['plat_nomor'] ?? '-') ?></strong></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Harga Sewa/Hari:</span>
                                    <span class="detail-value text-info"><strong>Rp <?= number_format($data['harga_sewa'] ?? 0,0,',','.') ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Bukti Pembayaran -->
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h4><i class="fa fa-credit-card"></i> Bukti Pembayaran</h4>
                                <?php if (!empty($data['bukti_pembayaran'])): ?>
                                    <?php if (file_exists('uploads/bukti_pembayaran/' . $data['bukti_pembayaran'])): ?>
                                        <div class="text-center">
                                            <img src="uploads/bukti_pembayaran/<?= htmlspecialchars($data['bukti_pembayaran']) ?>" 
                                                 alt="Bukti Pembayaran" 
                                                 class="bukti-pembayaran"
                                                 onclick="window.open(this.src, '_blank')">
                                            <p class="text-muted" style="margin-top: 10px;">
                                                <small>Klik gambar untuk memperbesar</small>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center text-warning">
                                            <i class="fa fa-exclamation-triangle fa-3x"></i>
                                            <p>File bukti pembayaran tidak ditemukan</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-secondary">
                                        <i class="fa fa-image fa-3x"></i>
                                        <p>Belum ada bukti pembayaran</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="detail-card">
                                <h4><i class="fa fa-cogs"></i> Aksi</h4>
                                <div class="text-center">
                                    <!-- Edit & Batal: hanya pengguna, status pending, milik sendiri -->
                                    <?php if ($ses_level === 'pengguna' && $data['status'] === 'pending' && intval($data['id_pengguna']) === $ses_id): ?>
                                        <a href="?page=MyApp/edit_transaksi_sewa&id=<?= urlencode($data['id_transaksi']) ?>" class="btn btn-warning">
                                            <i class="fa fa-edit"></i> Edit Transaksi
                                        </a>
                                        <a href="?page=MyApp/batal_transaksi_sewa&id=<?= urlencode($data['id_transaksi']) ?>" 
                                           class="btn btn-secondary" 
                                           onclick="return confirm('Batalkan transaksi sewa ini?');">
                                            <i class="fa fa-times"></i> Batalkan
                                        </a>
                                    <?php endif; ?>

                                    <!-- Approve: admin, status pending -->
                                    <?php if ($data['status'] === 'pending' && ($ses_level === 'administrator' || $ses_level === 'admin')): ?>
                                        <a href="?page=MyApp/approve_transaksi_sewa&id=<?= urlencode($data['id_transaksi']) ?>" 
                                           class="btn btn-success" 
                                           onclick="return confirm('Approve transaksi sewa ini?');">
                                            <i class="fa fa-check"></i> Approve
                                        </a>
                                    <?php endif; ?>

                                    <!-- Selesaikan Sewa: admin, status approved -->
                                    <?php if ($data['status'] === 'approved' && ($ses_level === 'administrator' || $ses_level === 'admin')): ?>
                                        <a href="?page=MyApp/selesai_transaksi_sewa&id=<?= urlencode($data['id_transaksi']) ?>" 
                                           class="btn" style="background:#6c757d;color:#fff;" 
                                           onclick="return confirm('Tandai sewa ini sebagai selesai?');">
                                            <i class="fa fa-flag-checkered"></i> Selesaikan
                                        </a>
                                    <?php endif; ?>

                                    <!-- Cetak Kontrak: approved atau selesai -->
                                    <?php if (($data['status'] === 'approved' || $data['status'] === 'selesai') && 
                                              (($ses_level === 'administrator' || $ses_level === 'admin') || 
                                               ($ses_level === 'pengguna' && intval($data['id_pengguna']) === $ses_id))): ?>
                                        <a href="?page=MyApp/cetak_kontrak_sewa&id=<?= urlencode($data['id_transaksi']) ?>" 
                                           class="btn btn-info" target="_blank">
                                            <i class="fa fa-print"></i> Cetak Kontrak
                                        </a>
                                    <?php endif; ?>

                                    <!-- Hapus: hanya admin/administrator -->
                                    <?php if ($ses_level === 'administrator' || $ses_level === 'admin'): ?>
                                        <a href="?page=MyApp/del_transaksi_sewa&id=<?= urlencode($data['id_transaksi']) ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Yakin hapus transaksi sewa ini?');">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>