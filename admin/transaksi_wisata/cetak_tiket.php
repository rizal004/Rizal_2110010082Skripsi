<?php
// Pastikan file koneksi sudah ada
if (file_exists("inc/koneksi.php")) {
    include "inc/koneksi.php";
} else {
    die("Koneksi database tidak ditemukan!");
}

// Ambil ID transaksi dari URL
if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];
    
    // Pemeriksaan error dan debugging
    if ($koneksi->connect_error) {
        die("Koneksi gagal: " . $koneksi->connect_error);
    }
    
    // Gunakan prepared statement untuk keamanan
    $stmt = $koneksi->prepare("SELECT 
        t.id_transaksi, 
        t.jumlah_tiket, 
        t.total_bayar, 
        t.tanggal,
        t.status,
        w.nama_wisata, 
        w.harga_tiket,
        p.nama_pengguna
    FROM tb_transaksi t
    JOIN tb_wisata w ON t.id_wisata = w.id_wisata
    JOIN tb_pengguna p ON t.id_pengguna = p.id_pengguna
    WHERE t.id_transaksi = ? 
    AND t.status = 'approved'");
    
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
        $qrData = $data['id_transaksi'] . '-' . $data['nama_pengguna'] . '-' . $data['jumlah_tiket'];
    } else {
        die("Transaksi tidak ditemukan atau belum diapprove!");
    }
    
    $stmt->close();
} else {
    die("ID Transaksi tidak ditemukan!");
}

// Format tanggal Indonesia jika ada
$tanggal = isset($data['tanggal']) ? date('d F Y', strtotime($data['tanggal'])) : date('d F Y');
// Mengubah nama bulan menjadi bahasa Indonesia
$tanggal = str_replace(
    ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
    $tanggal
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Wisata - <?= $data['id_transaksi'] ?></title>
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
        
        .tiket-container {
            max-width: 650px;
            width: 100%;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            margin-bottom: 20px;
        }
        
        .ticket-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.637 0 3-1.363 3-3s-1.363-3-3-3-3 1.363-3 3 1.363 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'/%3E%3C/svg%3E");
            z-index: 1;
        }
        
        .ticket-top {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-bottom: 2px dashed rgba(255, 255, 255, 0.3);
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .ticket-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ticket-logo i {
            font-size: 28px;
            color: white;
        }
        
        .ticket-logo h2 {
            color: white;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
        }
        
        .ticket-id {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 50px;
            color: white;
            font-size: 14px;
            letter-spacing: 1px;
        }
        
        .ticket-title {
            text-align: center;
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .ticket-content {
            position: relative;
            z-index: 2;
            padding: 25px;
            display: flex;
            justify-content: space-between;
        }
        
        .ticket-details {
            flex: 1;
        }
        
        .ticket-info-item {
            margin-bottom: 20px;
        }
        
        .ticket-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .ticket-value {
            color: white;
            font-size: 18px;
            font-weight: 600;
            display: block;
        }
        
        .ticket-qr {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-left: 20px;
        }
        
        .qr-code {
            width: 150px;
            height: 150px;
            background-color: white;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-text {
            color: white;
            font-size: 12px;
            text-align: center;
        }
        
        .ticket-validation {
            text-align: center;
            background: rgba(39, 174, 96, 0.2);
            color: white;
            font-weight: 600;
            font-size: 20px;
            padding: 15px;
            letter-spacing: 1px;
            border-top: 2px dashed rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
        }
        
        .ticket-validation i {
            margin-right: 10px;
        }
        
        .print-btn {
            background: #2980b9;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .print-btn:hover {
            background: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        
        .no-print a {
            color: #2980b9;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .no-print a:hover {
            color: #3498db;
            text-decoration: underline;
        }
        
        .ticket-footer {
            display: flex;
            justify-content: space-between;
            padding: 15px 25px;
            background: rgba(0, 0, 0, 0.1);
            color: white;
            font-size: 14px;
        }
        
        .total-section {
            font-weight: 700;
            font-size: 18px;
            color: white;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .date-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 50px;
            color: white;
            font-size: 14px;
            letter-spacing: 1px;
        }
        
        /* Navigation elements that should be hidden when printing */
        .sidebar-navigation {
            display: block;
        }
        
        /* Ensure printing is always in color */
        @media print {
            body {
                background-color: white;
                padding: 0;
                /* Center the ticket when printing */
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            
            .tiket-container {
                box-shadow: none;
                /* Enforce color printing */
                
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Hide elements marked as no-print */
            .print-btn, .no-print, .sidebar-navigation {
                display: none !important;
            }
            
            /* Force the background gradients to print */
            .tiket-container {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Make sure text colors are preserved */
            .ticket-label, .ticket-value, .ticket-title, .ticket-logo h2, .ticket-id, .ticket-validation, .ticket-footer {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        @media (max-width: 768px) {
            .ticket-content {
                flex-direction: column;
            }
            
            .ticket-qr {
                margin-top: 20px;
                padding-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="tiket-container">
        <div class="ticket-background"></div>
        
        <div class="ticket-top">
            <div class="ticket-header">
                <div class="ticket-logo">
                    <i class="fas fa-umbrella-beach"></i>
                    <h2>Si Wisata</h2>
                </div>
                <span class="ticket-id">#<?= htmlspecialchars($data['id_transaksi']) ?></span>
            </div>
            
            <h1 class="ticket-title">E-Tiket Wisata</h1>
        </div>
        
        <div class="ticket-content">
            <div class="ticket-details">
                <div class="date-badge">
                    <i class="far fa-calendar-alt"></i> <?= $tanggal ?>
                </div>
                
                <div class="ticket-info-item">
                    <span class="ticket-label">Nama Pengunjung</span>
                    <span class="ticket-value"><?= htmlspecialchars($data['nama_pengguna']) ?></span>
                </div>
                
                <div class="ticket-info-item">
                    <span class="ticket-label">Destinasi Wisata</span>
                    <span class="ticket-value"><?= htmlspecialchars($data['nama_wisata']) ?></span>
                </div>
                
                <div class="ticket-info-item">
                    <span class="ticket-label">Harga Tiket</span>
                    <span class="ticket-value">Rp<?= number_format($data['harga_tiket'], 0, ',', '.') ?></span>
                </div>
                
                <div class="ticket-info-item">
                    <span class="ticket-label">Jumlah Tiket</span>
                    <span class="ticket-value"><?= htmlspecialchars($data['jumlah_tiket']) ?> Orang</span>
                </div>
                
                <div class="ticket-info-item total-section">
                    <span class="ticket-label">Total Pembayaran</span>
                    <span class="ticket-value">Rp<?= number_format($data['total_bayar'], 0, ',', '.') ?></span>
                </div>
            </div>
            
            <div class="ticket-qr">
                <div class="qr-code">
                    <!-- QR Code Placeholder - In a real app, generate this dynamically -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=<?= urlencode($qrData) ?>" alt="QR Code">
                </div>
                <div class="qr-text">Scan untuk validasi</div>
            </div>
        </div>
        
        <div class="ticket-validation">
            <i class="fas fa-check-circle"></i> Tiket Valid
        </div>
        
        <div class="ticket-footer">
            <span>Dipesan pada <?= $tanggal ?></span>
            <span>Terima kasih atas kunjungan Anda!</span>
        </div>
    </div>
    
    <button class="print-btn" onclick="printTicket()">
        <i class="fas fa-print"></i> Cetak Tiket
    </button>
    
    <p class="no-print">
        <a href="?page=MyApp/transaksi_wisata"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Transaksi</a>
    </p>
    
    <script>
        // Script untuk mengatur pencetakan dengan warna
        function printTicket() {
            var printContents = document.querySelector('.tiket-container').outerHTML;
            var originalContents = document.body.innerHTML;
            
            // Buat dialog cetak dengan konten tiket saja dan CSS khusus untuk memastikan warna
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Cetak Tiket - <?= htmlspecialchars($data['id_transaksi']) ?></title>
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
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            min-height: 100vh;
                            background-color: white;
                            padding: 20px;
                        }
                        
                        /* Override untuk memastikan pencetakan warna */
                        .tiket-container {
                            max-width: 650px;
                            width: 100%;
                            background: linear-gradient(135deg, #2980b9, #6dd5fa) !important;
                            border-radius: 15px;
                            overflow: hidden;
                            position: relative;
                            color-adjust: exact !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        
                        /* Salin semua CSS yang relevan dari atas untuk memastikan tetap konsisten */
                        .ticket-background {
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            opacity: 0.05;
                            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.637 0 3-1.363 3-3s-1.363-3-3-3-3 1.363-3 3 1.363 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'/%3E%3C/svg%3E");
                            z-index: 1;
                        }
                        
                        .ticket-top {
                            position: relative;
                            z-index: 2;
                            background: rgba(255, 255, 255, 0.1);
                            backdrop-filter: blur(10px);
                            padding: 25px;
                            border-bottom: 2px dashed rgba(255, 255, 255, 0.3);
                        }
                        
                        .ticket-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 20px;
                        }
                        
                        .ticket-logo {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        }
                        
                        .ticket-logo i {
                            font-size: 28px;
                            color: white !important;
                        }
                        
                        .ticket-logo h2 {
                            color: white !important;
                            font-size: 26px;
                            font-weight: 700;
                            letter-spacing: 1px;
                            margin: 0;
                        }
                        
                        .ticket-id {
                            background: rgba(255, 255, 255, 0.2);
                            padding: 5px 10px;
                            border-radius: 50px;
                            color: white !important;
                            font-size: 14px;
                            letter-spacing: 1px;
                        }
                        
                        .ticket-title {
                            text-align: center;
                            color: white !important;
                            font-size: 32px;
                            font-weight: 700;
                            margin-bottom: 20px;
                            text-transform: uppercase;
                            letter-spacing: 2px;
                            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                        }
                        
                        .ticket-content {
                            position: relative;
                            z-index: 2;
                            padding: 25px;
                            display: flex;
                            justify-content: space-between;
                        }
                        
                        .ticket-details {
                            flex: 1;
                        }
                        
                        .ticket-info-item {
                            margin-bottom: 20px;
                        }
                        
                        .ticket-label {
                            color: rgba(255, 255, 255, 0.8) !important;
                            font-size: 14px;
                            font-weight: 500;
                            margin-bottom: 5px;
                            display: block;
                            text-transform: uppercase;
                            letter-spacing: 1px;
                        }
                        
                        .ticket-value {
                            color: white !important;
                            font-size: 18px;
                            font-weight: 600;
                            display: block;
                        }
                        
                        .ticket-qr {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            padding-left: 20px;
                        }
                        
                        .qr-code {
                            width: 150px;
                            height: 150px;
                            background-color: white;
                            padding: 10px;
                            border-radius: 10px;
                            margin-bottom: 10px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        
                        .qr-text {
                            color: white !important;
                            font-size: 12px;
                            text-align: center;
                        }
                        
                        .ticket-validation {
                            text-align: center;
                            background: rgba(39, 174, 96, 0.2) !important;
                            color: white !important;
                            font-weight: 600;
                            font-size: 20px;
                            padding: 15px;
                            letter-spacing: 1px;
                            border-top: 2px dashed rgba(255, 255, 255, 0.3);
                            text-transform: uppercase;
                        }
                        
                        .ticket-validation i {
                            margin-right: 10px;
                        }
                        
                        .ticket-footer {
                            display: flex;
                            justify-content: space-between;
                            padding: 15px 25px;
                            background: rgba(0, 0, 0, 0.1) !important;
                            color: white !important;
                            font-size: 14px;
                        }
                        
                        .total-section {
                            font-weight: 700;
                            font-size: 18px;
                            color: white !important;
                            margin-top: 10px;
                            padding-top: 10px;
                            border-top: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        
                        .date-badge {
                            position: absolute;
                            top: 25px;
                            right: 25px;
                            background: rgba(255, 255, 255, 0.2);
                            padding: 5px 10px;
                            border-radius: 50px;
                            color: white !important;
                            font-size: 14px;
                            letter-spacing: 1px;
                        }
                        
                        @media print {
                            /* Pastikan semua warna tercetak dengan benar */
                            * {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.setTimeout(function(){ window.close(); }, 500)">
                    ${printContents}
                </body>
                </html>
            `);
            printWindow.document.close();
        }
        
        // Set kelas pada elemen yang ingin disembunyikan saat cetak
        window.onload = function() {
            // Tambahkan kelas untuk elemen yang perlu disembunyikan saat cetak
            // (ini untuk elemen yang dipanah di screenshot)
            const sidebarElements = document.querySelectorAll(".main-navigation, .sidebar, nav");
            if (sidebarElements) {
                sidebarElements.forEach(elem => {
                    elem.classList.add("sidebar-navigation");
                });
            }
        };
    </script>
</body>
</html>