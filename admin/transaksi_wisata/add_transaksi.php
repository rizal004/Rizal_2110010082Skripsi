<?php
// inc/koneksi.php
include "inc/koneksi.php";
// Mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil ID wisata dari parameter
$id_wisata = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (!$id_wisata) {
    echo "<script>alert('ID wisata tidak valid!'); window.location.href='index.php?page=MyApp/data_wisata';</script>";
    exit;
}

// Ambil data wisata
$wisata = $koneksi->query("SELECT * FROM tb_wisata WHERE id_wisata = '$id_wisata'");
$data_wisata = $wisata ? $wisata->fetch_assoc() : null;
if (!$data_wisata) {
    echo "<script>alert('Data wisata tidak ditemukan!'); window.location.href='index.php?page=MyApp/data_wisata';</script>";
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

// Fungsi untuk mengirim notifikasi WhatsApp
function sendWhatsAppNotification($target, $message) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $target,
            'message' => $message,
            'countryCode' => '62',
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Ndsd3B32k2C2WJRWU483'
        ),
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return array(
        'response' => $response,
        'http_code' => $httpCode
    );
}

// Proses simpan transaksi
if (isset($_POST['save'])) {
    // Generate ID transaksi unik
    $id_transaksi = uniqid('WIS');
    $jumlah_tiket = intval($_POST['jumlah_tiket']);
    $status = 'pending';

    // Hitung total bayar
    $harga = floatval($data_wisata['harga_tiket']);
    $total = $harga * $jumlah_tiket;

    // Simpan ke database dengan struktur yang sama seperti file pertama
    $sql = $koneksi->query(
        "INSERT INTO tb_transaksi (id_transaksi, id_pengguna, id_wisata, jumlah_tiket, total_bayar, status) " .
        "VALUES ('" . $id_transaksi . "', " . $id_pengguna_sess . ", '" . $id_wisata . "', " . $jumlah_tiket . ", " . $total . ", '" . $status . "')"
    );

    // Proses upload bukti pembayaran jika ada
    $bukti_uploaded = false;
    if ($sql && isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['bukti_pembayaran']['tmp_name'];
        $fileName = $id_transaksi . '_' . time() . '_' . basename($_FILES['bukti_pembayaran']['name']);
        $target   = "uploads/bukti/" . $fileName;
        
        // Buat folder jika belum ada
        if (!file_exists("uploads/bukti/")) {
            mkdir("uploads/bukti/", 0777, true);
        }
        
        if (move_uploaded_file($fileTmp, $target)) {
            // Update record dengan nama file bukti
            $koneksi->query("UPDATE tb_transaksi SET bukti_pembayaran='$fileName' WHERE id_transaksi='$id_transaksi'");
            $bukti_uploaded = true;
        }
    }

    if ($sql) {
        // Kirim notifikasi WhatsApp ke admin
        $admin_phone = '085787648735';
        $tanggal = date('d/m/Y H:i:s');
        
        $message = "🎫 *TRANSAKSI BARU - TIKET WISATA*\n\n";
        $message .= "📋 *Detail Transaksi:*\n";
        $message .= "ID Transaksi: " . $id_transaksi . "\n";
        $message .= "Tanggal: " . $tanggal . "\n\n";
        $message .= "👤 *Pemesan:*\n";
        $message .= "Nama: " . $nama_pengguna_sess . "\n";
        $message .= "ID Pengguna: " . $id_pengguna_sess . "\n\n";
        $message .= "🏞️ *Wisata:*\n";
        $message .= "Nama: " . $data_wisata['nama_wisata'] . "\n";
        $message .= "Lokasi: " . $data_wisata['kecamatan'] . ", " . $data_wisata['kabupaten'] . "\n\n";
        $message .= "🎟️ *Tiket:*\n";
        $message .= "Jumlah: " . $jumlah_tiket . " tiket\n";
        $message .= "Harga per tiket: Rp " . number_format($harga, 0, ',', '.') . "\n";
        $message .= "Total: Rp " . number_format($total, 0, ',', '.') . "\n\n";
        $message .= "📄 *Status:*\n";
        $message .= "Status: " . strtoupper($status) . "\n";
        $message .= "Bukti Pembayaran: " . ($bukti_uploaded ? "✅ Sudah diupload" : "❌ Belum diupload") . "\n\n";
        $message .= "⚠️ *Silakan cek sistem untuk memverifikasi transaksi ini.*";
        
        $wa_result = sendWhatsAppNotification($admin_phone, $message);
        
        // Log hasil pengiriman WhatsApp (optional)
        error_log("WhatsApp notification sent - HTTP Code: " . $wa_result['http_code'] . " - Response: " . $wa_result['response']);
        
        echo "<script>
            alert('Transaksi wisata berhasil dibuat! Total: Rp" . number_format($total,0,',','.') . "\\n\\nNotifikasi telah dikirim ke admin.');
            window.location.href='index.php?page=MyApp/transaksi_wisata';
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
    <title>Tambah Transaksi Wisata</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
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
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
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
            border-color: #43cea2;
            box-shadow: 0 0 0 0.2rem rgba(67,206,162,0.25);
        }
        .form-control:read-only {
            background-color: #f8f9fa;
            border-color: #dee2e6;
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
            background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
            color: #0c5460;
        }
        .wisata-info {
            background: linear-gradient(135deg, #f0f8ff 0%, #e8f4fd 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #43cea2;
        }
        .wisata-info h4 {
            color: #185a9d;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .wisata-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .detail-item {
            display: flex;
            align-items: center;
        }
        .detail-item i {
            color: #43cea2;
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
        @media (max-width: 768px) {
            .box {
                margin: 10px;
            }
            .box-body {
                padding: 20px;
            }
            .wisata-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="content-header">
            <h1><i class="fa fa-ticket-alt"></i> Pesan Tiket Wisata</h1>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Form Pemesanan Tiket</h3>
                </div>
                
                <!-- Info Wisata -->
                <div class="wisata-info" style="margin: 20px;">
                    <h4><i class="fa fa-map-marker-alt"></i> Informasi Destinasi Wisata</h4>
                    <div class="wisata-details">
                        <div class="detail-item">
                            <i class="fa fa-mountain"></i>
                            <span><strong>Nama:</strong> <?= htmlspecialchars($data_wisata['nama_wisata']) ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fa fa-map-marker-alt"></i>
                            <span><strong>Lokasi:</strong> <?= htmlspecialchars($data_wisata['kecamatan'] . ', ' . $data_wisata['kabupaten']) ?></span>
                        </div>
                        <?php if(isset($data_wisata['kategori'])): ?>
                        <div class="detail-item">
                            <i class="fa fa-info-circle"></i>
                            <span><strong>Kategori:</strong> <?= htmlspecialchars($data_wisata['kategori']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if(isset($data_wisata['jam_buka'])): ?>
                        <div class="detail-item">
                            <i class="fa fa-clock"></i>
                            <span><strong>Jam Buka:</strong> <?= htmlspecialchars($data_wisata['jam_buka']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($data_wisata['deskripsi'])): ?>
                    <div style="margin-top: 15px;">
                        <div class="detail-item">
                            <i class="fa fa-file-alt"></i>
                            <span><strong>Deskripsi:</strong> <?= htmlspecialchars($data_wisata['deskripsi']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="price-display">
                        <div class="price">
                            <i class="fa fa-ticket-alt"></i> 
                            Rp <?= number_format($data_wisata['harga_tiket'], 0, ',', '.') ?> / tiket
                        </div>
                    </div>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label><i class="fa fa-user"></i> Nama Pemesan</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($nama_pengguna_sess) ?>" readonly>
                            <input type="hidden" name="id_pengguna" value="<?= $id_pengguna_sess ?>">
                            <small class="text-muted">Nama pemesan diambil dari akun yang sedang login</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-users"></i> Jumlah Tiket</label>
                            <input type="number" name="jumlah_tiket" id="jumlah_tiket" class="form-control" min="1" max="20" value="1" onchange="updateCalculation()" required>
                            <small class="text-muted">Masukkan jumlah tiket yang ingin dipesan (1-20 tiket)</small>
                        </div>

                        <div class="calculation">
                            <h5><i class="fa fa-calculator"></i> Perhitungan Biaya</h5>
                            <div id="calculation-detail">
                                <p>Harga per tiket: Rp <?= number_format($data_wisata['harga_tiket'], 0, ',', '.') ?></p>
                                <p>Jumlah tiket: <span id="ticket-display">1</span> tiket</p>
                                <hr>
                                <p><strong>Total Pembayaran: Rp <span id="total-display"><?= number_format($data_wisata['harga_tiket'], 0, ',', '.') ?></span></strong></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-upload"></i> Bukti Pembayaran</label>
                            <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload gambar bukti transfer pembayaran (JPG, PNG, max 5MB)</small>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="fa fa-info-circle"></i> Informasi Pembayaran</h5>
                            <p><strong>Total yang harus dibayar: Rp <span id="payment-total"><?= number_format($data_wisata['harga_tiket'], 0, ',', '.') ?></span></strong></p>
                            <p><strong>Silakan transfer ke salah satu rekening berikut:</strong></p>
                            <ul class="mb-0">
                                <li><i class="fa fa-wallet"></i> <strong>Dana:</strong> 0812-3456-7890 (a.n. PT Wisata Nusantara)</li>
                                <li><i class="fa fa-university"></i> <strong>Bank BRI:</strong> 1234-5678-9012-3456 (a.n. PT Wisata Nusantara)</li>
                                <li><i class="fa fa-university"></i> <strong>Bank Mandiri:</strong> 0987-6543-2109-8765 (a.n. PT Wisata Nusantara)</li>
                            </ul>
                            <p class="mt-2 mb-0"><small><em>Setelah transfer, upload bukti pembayaran dan tekan tombol "Pesan Tiket".</em></small></p>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" name="save" class="btn btn-success">
                            <i class="fa fa-ticket-alt"></i> Pesan Tiket Sekarang
                        </button>
                        <a href="?page=MyApp/data_wisata" class="btn btn-default">
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
        function updateCalculation() {
            const jumlahTiket = parseInt(document.getElementById('jumlah_tiket').value) || 1;
            const hargaPerTiket = <?= $data_wisata['harga_tiket'] ?>;
            const total = hargaPerTiket * jumlahTiket;
            
            // Update tampilan
            document.getElementById('ticket-display').textContent = jumlahTiket;
            document.getElementById('total-display').textContent = total.toLocaleString('id-ID');
            document.getElementById('payment-total').textContent = total.toLocaleString('id-ID');
        }

        // Auto update saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateCalculation();
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
            const jumlahTiket = parseInt(document.getElementById('jumlah_tiket').value);
            const buktiPembayaran = document.querySelector('input[name="bukti_pembayaran"]').files[0];
            
            if (jumlahTiket < 1 || jumlahTiket > 20) {
                e.preventDefault();
                alert('Jumlah tiket harus antara 1-20 tiket!');
                return;
            }
            
            if (!buktiPembayaran) {
                e.preventDefault();
                alert('Silakan upload bukti pembayaran!');
                return;
            }
            
            // Konfirmasi sebelum submit
            const total = <?= $data_wisata['harga_tiket'] ?> * jumlahTiket;
            const konfirmasi = confirm(`Konfirmasi Pemesanan:\n\nWisata: <?= htmlspecialchars($data_wisata['nama_wisata']) ?>\nJumlah Tiket: ${jumlahTiket}\nTotal: Rp ${total.toLocaleString('id-ID')}\n\nLanjutkan pemesanan?`);
            
            if (!konfirmasi) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>