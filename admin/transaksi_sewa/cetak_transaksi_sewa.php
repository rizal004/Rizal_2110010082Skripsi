<?php
// Pastikan file koneksi sudah ada
if (file_exists("inc/koneksi.php")) {
    include "inc/koneksi.php";
} else {
    die("Koneksi database tidak ditemukan!");
}

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Ambil ID transaksi dari URL
if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];
    
    // Pemeriksaan error dan debugging
    if ($koneksi->connect_error) {
        die("Koneksi gagal: " . $koneksi->connect_error);
    }
    
    // Gunakan prepared statement untuk keamanan - sesuai dengan database yang ada
    $stmt = $koneksi->prepare("SELECT 
        t.id_transaksi, 
        t.tanggal_mulai,
        t.tanggal_selesai,
        t.lama_sewa,
        t.total_bayar, 
        t.tanggal,
        t.status,
        m.nama_motor,
        m.merk,
        m.jenis_kendaraan,
        p.nama_pengguna
    FROM tb_transaksi_sewa t
    LEFT JOIN tb_motor m ON t.id_motor = m.id_motor
    LEFT JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
    WHERE t.id_transaksi = ? 
    AND (t.status = 'approved' OR t.status = 'selesai')");
    
    if (!$stmt) {
        die("Error preparing statement: " . $koneksi->error);
    }
    
    $stmt->bind_param("s", $id_transaksi);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        die("Error dalam query: " . $stmt->error);
    }
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        // Generate unique QR code data
        $qrData = $data['id_transaksi'] . '-' . $data['nama_pengguna'] . '-' . $data['lama_sewa'];
    } else {
        die("Transaksi tidak ditemukan atau belum diapprove!");
    }
    
    $stmt->close();
} else {
    die("ID Transaksi tidak ditemukan!");
}

// Format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $tahun = date('Y', strtotime($tanggal));
    $bulan_num = date('n', strtotime($tanggal));
    $hari = date('j', strtotime($tanggal));
    
    return $hari . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
}

$tanggal_mulai = formatTanggalIndonesia($data['tanggal_mulai']);
$tanggal_selesai = formatTanggalIndonesia($data['tanggal_selesai']);
$tanggal_transaksi = formatTanggalIndonesia($data['tanggal']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kontrak Sewa Kendaraan - <?= $data['id_transaksi'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .kontrak-container {
            max-width: 800px;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            margin-bottom: 20px;
        }
        
        .contract-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px dashed rgba(255, 255, 255, 0.3);
        }
        
        .contract-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contract-logo i {
            font-size: 24px;
            color: white;
        }
        
        .contract-logo h2 {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }
        
        .contract-id {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 25px;
            color: white;
            font-size: 14px;
            font-weight: 600;
        }
        
        .contract-title {
            text-align: center;
            color: white;
            font-size: 28px;
            font-weight: 700;
            padding: 15px 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .contract-content {
            padding: 25px 30px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        .contract-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .contract-info-item {
            margin-bottom: 15px;
        }
        
        .contract-info-item.full-width {
            grid-column: 1 / -1;
        }
        
        .contract-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 3px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .contract-value {
            color: white;
            font-size: 16px;
            font-weight: 600;
            display: block;
            line-height: 1.2;
        }
        
        .contract-qr {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .qr-code {
            width: 120px;
            height: 120px;
            background-color: white;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-text {
            color: white;
            font-size: 11px;
            text-align: center;
            font-weight: 500;
        }
        
        .total-section {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .total-section .contract-value {
            font-size: 20px;
            font-weight: 700;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(40, 167, 69, 0.3);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .contract-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
            font-size: 12px;
        }
        
        .validation-text {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .print-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .print-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .no-print {
            text-align: center;
            margin-top: 15px;
        }
        
        .no-print a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .no-print a:hover {
            text-decoration: underline;
        }
        
        /* PRINT STYLES - MEMPERTAHANKAN TAMPILAN LAYAR */
        @media print {
            @page {
                size: A4;
                margin: 0.5in;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                
            }
            
            body {
                background: #f5f5f5 !important;
                padding: 0 !important;
                margin: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: auto !important;
                font-size: 12px !important;
            }
            
            .kontrak-container {
                max-width: 700px !important;
                width: 100% !important;
                /* MEMPERTAHANKAN GRADIENT BACKGROUND */
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
            }
            
            .contract-header {
                background: rgba(255, 255, 255, 0.1) !important;
                backdrop-filter: blur(10px) !important;
                color: white !important;
                padding: 18px 25px !important;
                border-bottom: 2px dashed rgba(255, 255, 255, 0.3) !important;
            }
            
            .contract-logo h2 {
                color: white !important;
                font-size: 20px !important;
            }
            
            .contract-logo i {
                color: white !important;
                font-size: 20px !important;
            }
            
            .contract-id {
                background: rgba(255, 255, 255, 0.2) !important;
                color: white !important;
                font-size: 12px !important;
                padding: 6px 12px !important;
                border-radius: 20px !important;
            }
            
            .contract-title {
                background: rgba(255, 255, 255, 0.05) !important;
                color: white !important;
                font-size: 24px !important;
                padding: 12px 25px !important;
                text-align: center !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
                font-weight: 700 !important;
            }
            
            .contract-content {
                background: transparent !important;
                padding: 20px 25px !important;
                display: grid !important;
                grid-template-columns: 2fr 1fr !important;
                gap: 25px !important;
                align-items: start !important;
            }
            
            .contract-details {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 12px !important;
            }
            
            .contract-info-item {
                margin-bottom: 12px !important;
            }
            
            .contract-info-item.full-width {
                grid-column: 1 / -1 !important;
            }
            
            .contract-label {
                color: rgba(255, 255, 255, 0.8) !important;
                font-size: 10px !important;
                font-weight: 500 !important;
                margin-bottom: 2px !important;
                display: block !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
            }
            
            .contract-value {
                color: white !important;
                font-size: 14px !important;
                font-weight: 600 !important;
                display: block !important;
                line-height: 1.2 !important;
            }
            
            .contract-qr {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .qr-code {
                width: 100px !important;
                height: 100px !important;
                background-color: white !important;
                padding: 6px !important;
                border-radius: 6px !important;
                margin-bottom: 6px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .qr-text {
                color: white !important;
                font-size: 9px !important;
                text-align: center !important;
                font-weight: 500 !important;
            }
            
            .total-section {
                grid-column: 1 / -1 !important;
                background: rgba(255, 255, 255, 0.1) !important;
                padding: 12px !important;
                border-radius: 6px !important;
                margin-top: 8px !important;
            }
            
            .total-section .contract-value {
                font-size: 18px !important;
                font-weight: 700 !important;
                color: white !important;
            }
            
            .total-section .contract-label {
                color: rgba(255, 255, 255, 0.8) !important;
            }
            
            .status-badge {
                display: inline-block !important;
                padding: 3px 10px !important;
                border-radius: 12px !important;
                font-size: 9px !important;
                font-weight: 600 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                background: rgba(40, 167, 69, 0.3) !important;
                color: #fff !important;
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
            }
            
            .contract-footer {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 12px 25px !important;
                background: rgba(0, 0, 0, 0.2) !important;
                color: white !important;
                font-size: 10px !important;
            }
            
            .validation-text {
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                font-weight: 600 !important;
                color: white !important;
            }
            
            .validation-text i {
                color: white !important;
            }
            
            .print-btn, .no-print {
                display: none !important;
            }
            
            /* Pastikan text di small tetap terlihat */
            small {
                color: rgba(255,255,255,0.7) !important;
                font-size: 10px !important;
                display: block !important;
                margin-top: 2px !important;
            }
            
            /* Pastikan semua elemen tidak terpotong */
            .contract-header,
            .contract-title,
            .contract-content,
            .contract-footer {
                page-break-inside: avoid !important;
            }
            
            /* Jika QR code dari API gagal load, berikan fallback */
            .qr-code img {
                width: 100% !important;
                height: 100% !important;
                object-fit: contain !important;
            }
        }
        
        @media (max-width: 768px) {
            .contract-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .contract-details {
                grid-template-columns: 1fr;
            }
            
            .contract-qr {
                order: -1;
            }
        }
    </style>
</head>
<body>
    <div class="kontrak-container">
        <div class="contract-header">
            <div class="contract-logo">
                <i class="fas fa-motorcycle"></i>
                <h2>Rental Motor</h2>
            </div>
            <span class="contract-id">#<?= htmlspecialchars($data['id_transaksi']) ?></span>
        </div>
        
        <div class="contract-title">Kontrak Sewa Kendaraan</div>
        
        <div class="contract-content">
            <div class="contract-details">
                <div class="contract-info-item">
                    <span class="contract-label">Nama Penyewa</span>
                    <span class="contract-value"><?= htmlspecialchars($data['nama_pengguna']) ?></span>
                </div>
                
                <div class="contract-info-item">
                    <span class="contract-label">Tanggal Transaksi</span>
                    <span class="contract-value"><?= $tanggal_transaksi ?></span>
                </div>
                
                <div class="contract-info-item full-width">
                    <span class="contract-label">Kendaraan</span>
                    <span class="contract-value"><?= htmlspecialchars($data['nama_motor']) ?></span>
                    <small style="color: rgba(255,255,255,0.7); font-size: 12px; display: block; margin-top: 2px;">
                        <?= htmlspecialchars($data['merk'] . ' - ' . $data['jenis_kendaraan']) ?>
                    </small>
                </div>
                
                <div class="contract-info-item">
                    <span class="contract-label">Tanggal Mulai</span>
                    <span class="contract-value"><?= $tanggal_mulai ?></span>
                </div>
                
                <div class="contract-info-item">
                    <span class="contract-label">Tanggal Selesai</span>
                    <span class="contract-value"><?= $tanggal_selesai ?></span>
                </div>
                
                <div class="contract-info-item">
                    <span class="contract-label">Lama Sewa</span>
                    <span class="contract-value"><?= htmlspecialchars($data['lama_sewa']) ?> Hari</span>
                </div>
                
                <div class="contract-info-item">
                    <span class="contract-label">Status</span>
                    <span class="status-badge">
                        <?= $data['status'] === 'approved' ? 'Disetujui' : 'Selesai' ?>
                    </span>
                </div>
                
                <div class="contract-info-item total-section">
                    <span class="contract-label">Total Biaya Sewa</span>
                    <span class="contract-value">Rp<?= number_format($data['total_bayar'], 0, ',', '.') ?></span>
                </div>
            </div>
            
            <div class="contract-qr">
                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($qrData) ?>" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="qr-text">Scan untuk verifikasi</div>
            </div>
        </div>
        
        <div class="contract-footer">
            <div class="validation-text">
                <i class="fas fa-check-circle"></i>
                <span>Kontrak Sah & Valid</span>
            </div>
            <span>Terima kasih atas kepercayaan Anda!</span>
        </div>
    </div>
    
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak Kontrak
    </button>
    
    <p class="no-print">
        <a href="?page=MyApp/transaksi_sewa"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Transaksi</a>
    </p>
    
    <script>
        // Fallback QR code jika gagal load
        window.addEventListener('load', function() {
            const qrImg = document.querySelector('.qr-code img');
            if (qrImg) {
                qrImg.onerror = function() {
                    this.parentElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:10px;color:#666;text-align:center;border:1px solid #ccc;">QR Code<br>Unavailable</div>';
                };
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.key === 'p') {
                event.preventDefault();
                window.print();
            }
        });
        
        // Function untuk print dengan setting otomatis (Chrome)
        function printWithSettings() {
            // Coba set print settings jika memungkinkan
            const printWindow = window.open('', '_blank');
            printWindow.document.write(document.documentElement.outerHTML);
            printWindow.document.close();
            
            // Tunggu load kemudian print
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
</body>
</html>