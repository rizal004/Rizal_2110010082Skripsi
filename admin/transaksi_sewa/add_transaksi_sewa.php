<?php
// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include koneksi database
include "inc/koneksi.php";

// Ambil ID motor dari parameter
$id_motor = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_motor) {
    echo "<script>alert('ID kendaraan tidak valid!'); window.location.href='index.php?page=MyApp/data_motor';</script>";
    exit;
}

// Ambil data motor
$motor = $koneksi->query("SELECT * FROM tb_motor WHERE id_motor = '$id_motor'");
$data_motor = $motor ? $motor->fetch_assoc() : null;
if (!$data_motor) {
    echo "<script>alert('Data kendaraan tidak ditemukan!'); window.location.href='index.php?page=MyApp/data_motor';</script>";
    exit;
}

// Ambil ID pengguna dari session
$id_pengguna_sess = isset($_SESSION['ses_id']) ? intval($_SESSION['ses_id']) : 0;

// Ambil nama pengguna dari database berdasarkan ID session
$nama_pengguna_sess = '';
if ($id_pengguna_sess > 0) {
    $u = $koneksi->query("SELECT nama_pengguna FROM tb_pengguna WHERE id_pengguna = $id_pengguna_sess");
    if ($u && $u->num_rows) {
        $rowU = $u->fetch_assoc();
        $nama_pengguna_sess = $rowU['nama_pengguna'];
    }
}

// Proses simpan transaksi
if (isset($_POST['save'])) {
    // Generate ID transaksi unik
    $id_transaksi = uniqid('SEW');
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $lama_sewa = intval($_POST['lama_sewa']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat_pengantaran = mysqli_real_escape_string($koneksi, $_POST['alamat_pengantaran']);

    $status = 'pending';
    $tanggal = date('Y-m-d H:i:s');

    // Hitung total bayar
    $harga_sewa = floatval($data_motor['harga_sewa']);
    $total = $harga_sewa * $lama_sewa;

    // Upload bukti pembayaran
    $bukti_pembayaran = '';
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['bukti_pembayaran']['tmp_name'];
        $fileName = $id_transaksi . '_' . time() . '_' . basename($_FILES['bukti_pembayaran']['name']);
        $target   = "uploads/bukti/" . $fileName;
        
        // Buat folder jika belum ada
        if (!file_exists("uploads/bukti/")) {
            mkdir("uploads/bukti/", 0777, true);
        }
        
        if (move_uploaded_file($fileTmp, $target)) {
            $bukti_pembayaran = $fileName;
        }
    }

    // Simpan ke database
    $sql = $koneksi->query(
        "INSERT INTO tb_transaksi_sewa (id_transaksi, id_pengguna, id_motor, tanggal_mulai, tanggal_selesai, lama_sewa, total_bayar, no_hp, alamat_pengantaran, status, bukti_pembayaran, tanggal) " .
        "VALUES ('" . $id_transaksi . "', " . $id_pengguna_sess . ", '" . $id_motor . "', '" . $tanggal_mulai . "', '" . $tanggal_selesai . "', " . $lama_sewa . ", " . $total . ", '" . $no_hp . "', '" . $alamat_pengantaran . "', '" . $status . "', '" . $bukti_pembayaran . "', '" . $tanggal . "')"
    );

    if ($sql) {
        echo "<script>
            alert('Transaksi sewa berhasil dibuat! Total: Rp" . number_format($total,0,',','.') . "');
            window.location.href='index.php?page=MyApp/transaksi_sewa';
        </script>";
    } else {
        echo "<script>alert('Transaksi gagal disimpan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tambah Transaksi Sewa</title>
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
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-weight: 700;
        }
        .box {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 800px;
            overflow: hidden;
        }
        .box-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
        }
        .box-header h3 {
            margin: 0;
            font-weight: 600;
        }
        .box-body {
            padding: 30px;
        }
        .box-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }
        .btn-default {
            background: #6c757d;
            border: none;
            color: white;
        }
        .btn-default:hover {
            background: #545b62;
            color: white;
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: linear-gradient(135deg,rgb(15, 194, 225) 0%, #bee5eb 100%);
            color: #0c5460;
        }
        .alert-warning {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            color: #856404;
        }
        .vehicle-info {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #667eea;
        }
        .vehicle-info h4 {
            color: #4c63d2;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .vehicle-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .detail-item {
            display: flex;
            align-items: center;
        }
        .detail-item i {
            color: #667eea;
            margin-right: 10px;
            width: 20px;
        }
        .price-display {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0fff0 100%);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 15px 0;
            border: 2px solid #28a745;
        }
        .price-display .price {
            font-size: 1.5em;
            font-weight: bold;
            color: #28a745;
        }
        .calculation {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .contact-info {
            background: linear-gradient(135deg, #fff4e6 0%, #ffe8cc 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #fd7e14;
        }
        .contact-info h5 {
            color: #d63384;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .delivery-info {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #0dcaf0;
        }
        .delivery-info h5 {
            color: #0a58ca;
            margin-bottom: 15px;
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .box {
                margin: 10px;
            }
            .box-body {
                padding: 20px;
            }
            .vehicle-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="content-header">
            <h1><i class="fa fa-calendar-plus"></i> Tambah Transaksi Sewa</h1>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Form Transaksi Sewa</h3>
                </div>
                
                <!-- Info Kendaraan -->
                <div class="vehicle-info" style="margin: 20px;">
                    <h4><i class="fa fa-car"></i> Informasi Kendaraan</h4>
                    <div class="vehicle-details">
                        <div class="detail-item">
                            <i class="fa fa-tag"></i>
                            <span><strong>Nama:</strong> <?= htmlspecialchars($data_motor['nama_motor']) ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa fa-car"></i>
                            <span><strong>Jenis:</strong> <?= htmlspecialchars($data_motor['jenis_kendaraan']) ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa fa-trademark"></i>
                            <span><strong>Merk:</strong> <?= htmlspecialchars($data_motor['merk']) ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa fa-calendar"></i>
                            <span><strong>Tahun:</strong> <?= htmlspecialchars($data_motor['tahun']) ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa fa-palette"></i>
                            <span><strong>Warna:</strong> <?= htmlspecialchars($data_motor['warna']) ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <span><strong>Lokasi:</strong> <?= htmlspecialchars($data_motor['kecamatan'] . ', ' . $data_motor['kabupaten']) ?></span>
                        </div>
                    </div>
                    <div class="price-display">
                        <div class="price">
                            <i class="fa fa-money-bill-wave"></i> 
                            Rp <?= number_format($data_motor['harga_sewa'], 0, ',', '.') ?> / hari
                        </div>
                    </div>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label><i class="fa fa-user"></i> Nama Penyewa</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nama_pengguna_sess) ?>" readonly>
                            <input type="hidden" name="id_pengguna" value="<?= $id_pengguna_sess ?>">
                        </div>

                        <!-- Informasi Kontak -->
                        <div class="contact-info">
                            <h5><i class="fa fa-phone"></i> Informasi Kontak</h5>
                            <div class="form-group">
                                <label><i class="fa fa-mobile-alt"></i> Nomor HP/WhatsApp</label>
                                <input type="tel" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" required pattern="[0-9]{10,13}">
                                <small class="text-muted">Nomor HP akan digunakan untuk konfirmasi dan komunikasi sewa</small>
                            </div>
                        </div>

                        <!-- Informasi Pengantaran -->
                        <div class="delivery-info">
                            <h5><i class="fa fa-truck"></i> Informasi Pengantaran</h5>
                            <div class="form-group">
                                <label><i class="fa fa-map-marker-alt"></i> Alamat Pengantaran</label>
                                <textarea name="alamat_pengantaran" class="form-control" rows="3" placeholder="Masukkan alamat lengkap untuk pengantaran kendaraan" required></textarea>
                                <small class="text-muted">Alamat lengkap tempat kendaraan akan diantar</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> Tanggal Mulai Sewa</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" required min="<?= date('Y-m-d') ?>" onchange="calculateDays()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar-check"></i> Tanggal Selesai Sewa</label>
                                    <input type="date" name="tanggal_selesai" class="form-control" required min="<?= date('Y-m-d') ?>" onchange="calculateDays()">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-clock"></i> Lama Sewa (Hari)</label>
                            <input type="number" name="lama_sewa" id="lama_sewa" class="form-control" min="1" value="1" required readonly>
                        </div>

                        <div class="calculation">
                            <h5><i class="fa fa-calculator"></i> Perhitungan Biaya</h5>
                            <div id="calculation-detail">
                                <p>Harga per hari: Rp <?= number_format($data_motor['harga_sewa'], 0, ',', '.') ?></p>
                                <p>Lama sewa: <span id="days-display">1</span> hari</p>
                                <hr>
                                <p><strong>Total: Rp <span id="total-display"><?= number_format($data_motor['harga_sewa'], 0, ',', '.') ?></span></strong></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-upload"></i> Bukti Pembayaran</label>
                            <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload gambar bukti transfer pembayaran</small>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fa fa-info-circle"></i> Informasi Pembayaran</h5>
                            <p><strong>Silakan transfer ke salah satu rekening berikut:</strong></p>
                            <ul class="mb-0">
                                <li><i class="fa fa-wallet"></i> <strong>Dana:</strong> 0812-3456-7890</li>
                                <li><i class="fa fa-university"></i> <strong>Bank BRI:</strong> 1234-5678-9012-3456</li>
                                <li><i class="fa fa-university"></i> <strong>Bank Mandiri:</strong> 0987-6543-2109-8765</li>
                            </ul>
                            <p class="mt-2 mb-0"><small><em>Setelah transfer, upload bukti pembayaran di form di atas.</em></small></p>
                        </div>

                        <div class="alert alert-warning">
                            <h5><i class="fa fa-exclamation-triangle"></i> Perhatian</h5>
                            <ul class="mb-0">
                                <li>Pastikan nomor HP yang dimasukkan aktif dan bisa dihubungi</li>
                                <li>Alamat pengantaran harus jelas dan mudah dijangkau</li>
                                <li>Konfirmasi akan dilakukan via WhatsApp/telepon setelah pembayaran</li>
                                <li>Kendaraan akan diantar sesuai jadwal yang disepakati</li>
                            </ul>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" name="save" class="btn btn-success">
                            <i class="fa fa-save"></i> Simpan Transaksi
                        </button>
                        <a href="?page=MyApp/detail_sewa&id=<?= $id_motor ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    
    <script>
        function calculateDays() {
            const startDate = document.querySelector('input[name="tanggal_mulai"]').value;
            const endDate = document.querySelector('input[name="tanggal_selesai"]').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const timeDiff = end.getTime() - start.getTime();
                const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                if (dayDiff > 0) {
                    document.getElementById('lama_sewa').value = dayDiff;
                    document.getElementById('days-display').textContent = dayDiff;
                    
                    const hargaPerHari = <?= $data_motor['harga_sewa'] ?>;
                    const total = hargaPerHari * dayDiff;
                    document.getElementById('total-display').textContent = total.toLocaleString('id-ID');
                } else {
                    alert('Tanggal selesai harus setelah tanggal mulai!');
                    document.querySelector('input[name="tanggal_selesai"]').value = '';
                }
            }
        }

        // Set minimum date untuk tanggal selesai berdasarkan tanggal mulai
        document.querySelector('input[name="tanggal_mulai"]').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.querySelector('input[name="tanggal_selesai"]');
            endDateInput.min = startDate;
            
            // Reset tanggal selesai jika lebih kecil dari tanggal mulai
            if (endDateInput.value && endDateInput.value < startDate) {
                endDateInput.value = '';
                document.getElementById('lama_sewa').value = 1;
                document.getElementById('days-display').textContent = 1;
                const hargaPerHari = <?= $data_motor['harga_sewa'] ?>;
                document.getElementById('total-display').textContent = hargaPerHari.toLocaleString('id-ID');
            }
        });

        // Validasi nomor HP
        document.querySelector('input[name="no_hp"]').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Hapus semua karakter non-digit
            this.value = value;
            
            // Validasi panjang
            if (value.length > 13) {
                this.value = value.substring(0, 13);
            }
        });

        // Validasi file upload
        document.querySelector('input[name="bukti_pembayaran"]').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // MB
                const fileType = file.type;
                
                if (fileSize > 5) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB');
                    this.value = '';
                    return;
                }
                
                if (!fileType.startsWith('image/')) {
                    alert('File harus berupa gambar!');
                    this.value = '';
                    return;
                }
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = document.querySelector('input[name="tanggal_mulai"]').value;
            const endDate = document.querySelector('input[name="tanggal_selesai"]').value;
            const noHp = document.querySelector('input[name="no_hp"]').value;
            const alamat = document.querySelector('textarea[name="alamat_pengantaran"]').value;
            const buktiPembayaran = document.querySelector('input[name="bukti_pembayaran"]').files[0];
            
            if (!startDate || !endDate) {
                e.preventDefault();
                alert('Silakan lengkapi tanggal sewa!');
                return;
            }
            
            if (!noHp || noHp.length < 10) {
                e.preventDefault();
                alert('Nomor HP harus diisi minimal 10 digit!');
                return;
            }
            
            if (!alamat.trim()) {
                e.preventDefault();
                alert('Alamat pengantaran harus diisi!');
                return;
            }
            
            if (!buktiPembayaran) {
                e.preventDefault();
                alert('Silakan upload bukti pembayaran!');
                return;
            }
            
            const lamaSewa = parseInt(document.getElementById('lama_sewa').value);
            if (lamaSewa < 1) {
                e.preventDefault();
                alert('Lama sewa minimal 1 hari!');
                return;
            }
        });
    </script>
</body>
</html>